<?php

namespace Grafite\Cms\Repositories;

use Cms;
use CryptoService;
use Grafite\Cms\Models\CmsModel;
use Grafite\Cms\Models\Image;
use Grafite\Cms\Services\FileService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class ImageRepository extends CmsRepository
{
    public $table;

    public function __construct(public Image $model)
    {
        $this->table = config('cms.db-prefix').'images';
    }

    public function published(): LengthAwarePaginator
    {
        return $this->model->where('is_published', 1)
            ->orderBy('created_at', 'desc')
            ->paginate(Config::get('cms.pagination', 24));
    }

    /**
     * Returns all Images for the API.
     */
    public function apiPrepared(): Collection
    {
        return $this->model->orderBy('created_at', 'desc')->where('is_published', 1)->get();
    }

    /**
     * Returns all Images for the API.
     */
    public function getImagesByTag($tag = null): Builder
    {
        $images = $this->model->orderBy('created_at', 'desc')->where('is_published', 1);

        if (! is_null($tag)) {
            $images->where('tags', 'LIKE', '%'.$tag.'%');
        }

        return $images;
    }

    /**
     * Returns all Images tags.
     */
    public function allTags(): array
    {
        $tags = [];
        $images = $this->model->orderBy('created_at', 'desc')->where('is_published', 1)->get();

        foreach ($images as $image) {
            foreach (explode(',', $image->tags) as $tag) {
                if ($tag > '') {
                    array_push($tags, $tag);
                }
            }
        }

        return array_unique($tags);
    }

    /**
     * Stores Images into database.
     */
    public function apiStore(array $input): Image|false
    {
        $savedFile = app(FileService::class)->saveClone($input['location'], 'public/images');

        if (! $savedFile) {
            return false;
        }

        $input['is_published'] = 1;
        $input['location'] = $savedFile['name'];
        $input['storage_location'] = config('cms.storage-location');
        $input['original_name'] = $savedFile['original'];

        $image = $this->model->create($input);
        $image->setCaches();

        return $image;
    }

    /**
     * Stores Images into database.
     */
    public function store(array $input): Image
    {
        $savedFile = $input['location'];

        // @todo - can be deleted?
        // if (! $savedFile) {
        //     Cms::notification('Image could not be saved.', 'danger');

        //     return false;
        // }

        if (! isset($input['is_published'])) {
            $input['is_published'] = 0;
        } else {
            $input['is_published'] = 1;
        }

        $input['location'] = CryptoService::decrypt($savedFile['name']);
        $input['storage_location'] = config('cms.storage-location');
        $input['original_name'] = $savedFile['original'];

        $image = $this->model->create($input);
        $image->setCaches();

        return $image;
    }

    /**
     * Updates Images.
     */
    public function update(CmsModel $model, array $input): Image|bool
    {
        if (isset($input['location']) && ! empty($input['location'])) {
            $savedFile = app(FileService::class)->saveFile($input['location'], 'public/images', [], true);

            if (! $savedFile) {
                Cms::notification('Image could not be updated.', 'danger');

                return false;
            }

            $input['location'] = $savedFile['name'];
            $input['original_name'] = $savedFile['original'];
        } else {
            $input['location'] = $model->location;
        }

        if (! isset($input['is_published'])) {
            $input['is_published'] = 0;
        } else {
            $input['is_published'] = 1;
        }

        $model->forgetCache();

        $model->update($input);

        $model->setCaches();

        return $model;
    }
}
