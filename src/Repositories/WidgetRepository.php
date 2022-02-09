<?php

namespace Grafite\Cms\Repositories;

use Grafite\Cms\Models\CmsModel;
use Grafite\Cms\Models\Widget;

class WidgetRepository extends CmsRepository
{
    public $table;

    public function __construct(public Widget $model, public TranslationRepository $translationRepo)
    {
        $this->table = config('cms.db-prefix').'widgets';
    }

    /**
     * Stores Widgets into database.
     */
    public function store(array $payload): Widget
    {
        $payload['name'] = htmlentities($payload['name']);

        return $this->model->create($payload);
    }

    /**
     * Updates Widget in the database.
     */
    public function update(CmsModel $widget, array $payload): Widget|bool
    {
        $payload['name'] = htmlentities($payload['name']);

        if (! empty($payload['lang']) && $payload['lang'] !== config('cms.default-language', 'en')) {
            return $this->translationRepo->createOrUpdate($widget->id, 'Grafite\Cms\Models\Widget', $payload['lang'], $payload);
        }
        unset($payload['lang']);

        return $widget->update($payload);
    }
}
