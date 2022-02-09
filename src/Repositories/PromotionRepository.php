<?php

namespace Grafite\Cms\Repositories;

use Grafite\Cms\Models\CmsModel;
use Grafite\Cms\Models\Promotion;
use Illuminate\Support\Str;

class PromotionRepository extends CmsRepository
{
    public $table;

    public function __construct(public Promotion $model, public TranslationRepository $translationRepo)
    {
        $this->table = config('cms.db-prefix').'promotions';
    }

    /**
     * Stores Promotions into database.
     */
    public function store(array $payload): Promotion
    {
        $payload['slug'] = Str::slug($payload['slug']);

        return $this->model->create($payload);
    }

    /**
     * Updates Promotion in the database.
     */
    public function update(CmsModel $model, array $payload): Promotion|bool
    {
        $payload['slug'] = Str::slug($payload['slug']);

        if (! empty($payload['lang']) && $payload['lang'] !== config('cms.default-language', 'en')) {
            return $this->translationRepo->createOrUpdate($model->id, 'Grafite\Cms\Models\Promotion', $payload['lang'], $payload);
        } else {
            unset($payload['lang']);

            return $model->update($payload);
        }
    }
}
