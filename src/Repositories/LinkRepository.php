<?php

namespace Grafite\Cms\Repositories;

use Exception;
use Grafite\Cms\Models\CmsModel;
use Grafite\Cms\Models\Link;
use Illuminate\Support\Collection;

class LinkRepository extends CmsRepository
{
    public $table;

    public function __construct(public Link $model, public TranslationRepository $translationRepo)
    {
        $this->table = config('cms.db-prefix').'links';
    }

    /**
     * Stores Links into database.
     */
    public function store(array $payload): Link
    {
        $payload['external'] = (bool) ($payload['external'] ?? false);

        if ($payload['external'] != 0 && empty($payload['external_url'])) {
            throw new Exception("Your link was missing a URL", 1);
        }

        if (! isset($payload['page_id'])) {
            $payload['page_id'] = 0;
        }

        if ($payload['page_id'] == 0 && $payload['external'] == 0) {
            throw new Exception("Your link was not connected to anything, and could not be made", 1);
        }

        $link = $this->model->create($payload);

        $order = json_decode($link->menu->order);
        array_push($order, $link->id);
        $link->menu->update([
            'order' => json_encode($order),
        ]);

        return $link;
    }

    /**
     * Find Links by menu id.
     */
    public function getLinksByMenu(int $id): Collection
    {
        return $this->model->where('menu_id', $id)->get();
    }

    /**
     * Updates Links into database.
     */
    public function update(CmsModel $link, array $payload): Link|bool
    {
        $payload['external'] = (bool) ($payload['external'] ?? false);

        if (! empty($payload['lang']) && $payload['lang'] !== config('cms.default-language', 'en')) {
            return $this->translationRepo->createOrUpdate($link->id, 'Grafite\Cms\Models\Link', $payload['lang'], $payload);
        }

        unset($payload['lang']);

        return $link->update($payload);
    }
}
