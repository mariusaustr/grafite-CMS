<?php

namespace Grafite\Cms\Repositories;

use Carbon\Carbon;
use Grafite\Cms\Models\CmsModel;
use Grafite\Cms\Models\FAQ;

class FAQRepository extends CmsRepository
{
    public $table;

    public function __construct(public FAQ $model, public TranslationRepository $translationRepo)
    {
        $this->table = config('cms.db-prefix').'faqs';
    }

    /**
     * Stores FAQ into database.
     */
    public function store(array $payload): FAQ
    {
        $payload['question'] = htmlentities($payload['question']);
        $payload['is_published'] = (isset($payload['is_published'])) ? (bool) $payload['is_published'] : 0;
        $payload['published_at'] = (isset($payload['published_at']) && ! empty($payload['published_at'])) ? Carbon::parse($payload['published_at'])->format('Y-m-d H:i:s') : Carbon::now(config('app.timezone'))->format('Y-m-d H:i:s');

        return $this->model->create($payload);
    }

    /**
     * Updates FAQ into database.
     */
    public function update(CmsModel $item, array $payload): FAQ|bool
    {
        $payload['question'] = htmlentities($payload['question']);

        if (! empty($payload['lang']) && $payload['lang'] !== config('cms.default-language', 'en')) {
            return $this->translationRepo->createOrUpdate($item->id, 'Grafite\Cms\Models\FAQ', $payload['lang'], $payload);
        } else {
            $payload['is_published'] = (isset($payload['is_published'])) ? (bool) $payload['is_published'] : 0;
            $payload['published_at'] = (isset($payload['published_at']) && ! empty($payload['published_at'])) ? Carbon::parse($payload['published_at'])->format('Y-m-d H:i:s') : Carbon::now(config('app.timezone'))->format('Y-m-d H:i:s');

            unset($payload['lang']);

            return $item->update($payload);
        }
    }
}
