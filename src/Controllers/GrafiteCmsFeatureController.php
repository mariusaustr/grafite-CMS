<?php

namespace Grafite\Cms\Controllers;

use Cms;
use Grafite\Cms\Models\Archive;
use Grafite\Cms\Services\FileService;
use Illuminate\Contracts\View\View as ViewView;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

class GrafiteCmsFeatureController extends GrafiteCmsController
{
    public function sendHome(): RedirectResponse
    {
        return redirect('/');
    }

    /**
     * Rollback to a previous version of an entity, a specific moment.
     */
    public function revert(int $id): RedirectResponse
    {
        $archive = Archive::find($id);

        $model = app($archive->entity_type);
        $modelInstance = $model->find($archive->entity_id);

        $archiveData = (array) json_decode($archive->entity_data);

        $modelInstance->fill($archiveData);
        $modelInstance->save();

        Cms::notification('Reversion was successful', 'success');

        return redirect(URL::previous());
    }

    /**
     * Rollback to a previous version of an entity.
     */
    public function rollback(string $entity, int $id): RedirectResponse
    {
        $modelString = str_replace('_', '\\', $entity);

        if (! class_exists($modelString)) {
            Cms::notification('Could not rollback Model not found', 'warning');

            return redirect(URL::previous());
        }

        $model = app($modelString);
        $modelInstance = $model->find($id);

        $archive = Archive::where('entity_id', $id)->where('entity_type', $modelString)->limit(1)->offset(1)->orderBy('id', 'desc')->first();

        if (! $archive) {
            Cms::notification('Could not rollback', 'warning');

            return redirect(URL::previous());
        }

        $archiveData = (array) json_decode($archive->entity_data);

        $modelInstance->fill($archiveData);
        $modelInstance->save();

        Cms::notification('Rollback was successful', 'success');

        return redirect(URL::previous());
    }

    /**
     * Preview content.
     */
    public function preview(string $entity, int $id): ViewView
    {
        $modelString = 'Grafite\Cms\Models\\'.ucfirst($entity);

        if (! class_exists($modelString)) {
            $modelString = 'Grafite\Cms\Models\\'.ucfirst($entity).'s';
        }

        $model = new $modelString();
        $modelInstance = $model->find($id);

        $data = [
            $entity => $modelInstance,
        ];

        if (config('app.locale') != config('cms.default-language', Cms::config('cms.default-language'))) {
            if ($modelInstance->translation(config('app.locale'))) {
                $data = [
                    $entity => $modelInstance->translation(config('app.locale'))->data,
                ];
            }
        }

        $view = 'cms-frontend::'.$entity.'.show';

        if (! View::exists($view)) {
            $view = 'cms-frontend::'.$entity.'s.show';
        }

        if ($entity === 'page') {
            $view = 'cms-frontend::pages.'.$modelInstance->template;
        }

        if ($entity === 'blog') {
            $view = 'cms-frontend::blog.'.$modelInstance->template;
        }

        return view($view, $data);
    }

    /**
     * Set the default lanugage for the session.
     */
    public function setLanguage(Request $request, string $lang): RedirectResponse
    {
        return back()->withCookie('language', $lang);
    }

    /**
     * Delete the hero image.
     */
    public function deleteHero(string $entity, int $id): RedirectResponse
    {
        $entity = app('Grafite\Cms\Models\\'.ucfirst($entity))->find($id);

        if ($entity->hero_image && app(FileService::class)->delete($entity->hero_image)) {
            $entity->update([
                'hero_image' => null,
            ]);
            Cms::notification('Hero image deleted.', 'success');

            return back();
        }

        Cms::notification('Hero image could not be deleted', 'error');

        return back();
    }
}
