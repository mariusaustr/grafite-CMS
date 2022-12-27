<?php

namespace Grafite\Cms\Controllers;

use Cms;
use CryptoService;
use Exception;
use FileService;
use Grafite\Cms\Models\Image;
use Grafite\Cms\Repositories\ImageRepository;
use Grafite\Cms\Requests\ImagesRequest;
use Grafite\Cms\Services\CmsResponseService;
use Grafite\Cms\Services\ValidationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class ImagesController extends GrafiteCmsController
{
    public function __construct(ImageRepository $repository)
    {
        parent::construct();

        $this->repository = $repository;
    }

    /**
     * Display a listing of the Images.
     */
    public function index(): View
    {
        $result = $this->repository->paginated();

        return view('cms::modules.images.index')
            ->with('images', $result)
            ->with('pagination', $result->render());
    }

    /**
     * Search.
     */
    public function search(Request $request): View
    {
        $input = $request->all();

        $result = $this->repository->search($input);

        return view('cms::modules.images.index')
            ->with('images', $result[0]->get())
            ->with('pagination', $result[2])
            ->with('term', $result[1]);
    }

    /**
     * Show the form for creating a new Images.
     */
    public function create(): View
    {
        return view('cms::modules.images.create');
    }

    /**
     * Store a newly created Images in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $validation = app(ValidationService::class)->check(['location' => 'required']);
            if (! $validation['errors']) {
                $imageSaved = true;
                foreach ($request->input('location') as $image) {
                    $imageSaved = $this->repository->store([
                        'location' => $image,
                        'is_published' => $request->input('is_published'),
                        'tags' => $request->input('tags'),
                    ]);
                }

                Cms::notification('Image saved successfully.', 'success');

                if (! $imageSaved) {
                    Cms::notification('Image was not saved.', 'danger');
                }
            } else {
                Cms::notification('Image could not be saved', 'danger');

                return $validation['redirect'];
            }
        } catch (Exception $e) {
            Cms::notification($e->getMessage() ?: 'Image could not be saved.', 'danger');
        }

        return redirect(route($this->routeBase.'.images.index'));
    }

    /**
     * Store a newly created Files in storage.
     */
    public function upload(Request $request): JsonResponse
    {
        $validation = app(ValidationService::class)->check([
            'location' => ['required'],
        ]);

        if (! $validation['errors']) {
            $file = $request->file('location');
            $fileSaved = app(FileService::class)->saveFile($file, 'images', [], true);
            $fileSaved['name'] = CryptoService::encrypt($fileSaved['name']);
            $fileSaved['mime'] = $file->getClientMimeType();
            $fileSaved['size'] = $file->getSize();
            $response = app(CmsResponseService::class)->apiResponse('success', $fileSaved);
        } else {
            $response = app(CmsResponseService::class)->apiErrorResponse($validation['errors'], $validation['inputs']);
        }

        return $response;
    }

    /**
     * Show the form for editing the specified Images.
     */
    public function edit(int $id): View|RedirectResponse
    {
        $images = $this->repository->find($id);

        if (empty($images)) {
            Cms::notification('Image not found', 'warning');

            return redirect(route($this->routeBase.'.images.index'));
        }

        return view('cms::modules.images.edit')->with('images', $images);
    }

    /**
     * Update the specified Images in storage.
     */
    public function update(int $id, ImagesRequest $request): RedirectResponse
    {
        try {
            $images = $this->repository->find($id);

            Cms::notification('Image updated successfully.', 'success');

            if (empty($images)) {
                Cms::notification('Image not found', 'warning');

                return redirect(route($this->routeBase.'.images.index'));
            }

            $images = $this->repository->update($images, $request->all());

            if (! $images) {
                Cms::notification('Image could not be updated', 'danger');
            }
        } catch (Exception $e) {
            Cms::notification($e->getMessage() ?: 'Image could not be saved.', 'danger');
        }

        return redirect(route($this->routeBase.'.images.edit', $id));
    }

    /**
     * Remove the specified Images from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $image = $this->repository->find($id);

        if (is_file(storage_path($image->location))) {
            Storage::delete($image->location);
        } else {
            Storage::disk(Config::get('cms.storage-location', 'local'))->delete($image->location);
        }

        if (empty($image)) {
            Cms::notification('Image not found', 'warning');

            return redirect(route($this->routeBase.'.images.index'));
        }

        $image->forgetCache();
        $image->delete();

        Cms::notification('Image deleted successfully.', 'success');

        return redirect(route($this->routeBase.'.images.index'));
    }

    /**
     * Bulk image delete.
     */
    public function bulkDelete(string $ids): RedirectResponse
    {
        $ids = explode('-', $ids);

        foreach ($ids as $id) {
            $image = $this->repository->find($id);

            if (is_file(storage_path($image->location))) {
                Storage::delete($image->location);
            } else {
                Storage::disk(Config::get('cms.storage-location', 'local'))->delete($image->location);
            }

            $image->delete();
        }

        Cms::notification('Bulk Image deletes completed successfully.', 'success');

        return redirect(route($this->routeBase.'.images.index'));
    }

    /*
    |--------------------------------------------------------------------------
    | Api
    |--------------------------------------------------------------------------
    */

    /**
     * Display the specified Images.
     */
    public function apiList(Request $request): JsonResponse
    {
        if (config('cms.api-key') != $request->header('cms')) {
            return app(CmsResponseService::class)->apiResponse('error', []);
        }

        $images = $this->repository->apiPrepared();

        return app(CmsResponseService::class)->apiResponse('success', $images);
    }

    /**
     * Store a newly created Images in storage.
     */
    public function apiStore(Request $request): JsonResponse
    {
        $image = $this->repository->apiStore($request->all());

        return app(CmsResponseService::class)->apiResponse('success', $image);
    }
}
