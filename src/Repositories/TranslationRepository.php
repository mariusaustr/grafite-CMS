<?php

namespace Grafite\Cms\Repositories;

use Carbon\Carbon;
use Grafite\Cms\Models\Translation;
use Illuminate\Support\Collection;

class TranslationRepository
{
    public function __construct(public Translation $model)
    {
    }

    /**
     * Create or Update an entry.
     */
    public function createOrUpdate(int $entityId, string $entityType, string $lang, array $payload): bool
    {
        $translation = $this->model->firstOrCreate([
            'entity_id' => $entityId,
            'entity_type' => $entityType,
            'language' => $lang,
        ]);

        unset($payload['_method']);
        unset($payload['_token']);

        $translation->entity_data = json_encode($payload);

        return $translation->save();
    }

    /**
     * Find by URL.
     */
    public function findByUrl(string $url, string $type): ?object
    {
        $item = $this->model->where('entity_type', $type)->where('entity_data', 'LIKE', '%"url":"'.$url.'"%')->first();

        if ($item && ($item->data->is_published == 1 || $item->data->is_published == 'on') && $item->data->published_at <= Carbon::now(config('app.timezone'))->format('Y-m-d H:i:s')) {
            return $item->data;
        }

        return null;
    }

    /**
     * Find an entity by its Id.
     */
    public function findByEntityId(int $entityId, string $entityType): ?object
    {
        $item = $this->model->where('entity_type', $entityType)->where('entity_id', $entityId)->first();

        if ($item && ($item->data->is_published == 1 || $item->data->is_published == 'on') && $item->data->published_at <= Carbon::now(config('app.timezone'))->format('Y-m-d H:i:s')) {
            return $item->data;
        }

        return null;
    }

    /**
     * Get entities by type and language.
     */
    public function getEntitiesByTypeAndLang(string $lang, string $type): Collection
    {
        $entities = collect();
        $collection = $this->model->where('entity_type', $type)->where('entity_data', 'LIKE', '%"lang":"'.$lang.'"%')->get();

        foreach ($collection as $item) {
            $instance = app($item->$type)->attributes = $item->data;
            $entities->push($instance);
        }

        return $entities;
    }
}
