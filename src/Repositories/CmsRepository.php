<?php

namespace Grafite\Cms\Repositories;

use Carbon\Carbon;
use Grafite\Cms\Models\CmsModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class CmsRepository
{
    public $table;

    public function __construct(public TranslationRepository $translationRepo)
    {
    }

    /**
     * Returns all Widgets.
     */
    public function all(): array
    {
        return $this->model->orderBy('created_at', 'desc')->get()->all();
    }

    /**
     * Returns all paginated items.
     */
    public function paginated(): LengthAwarePaginator
    {
        $model = $this->model;

        if (isset(request()->dir) && isset(request()->field)) {
            $model = $model->orderBy(request()->field, request()->dir);
        } else {
            $model = $model->orderBy('created_at', 'desc');
        }

        return $model->paginate(config('cms.pagination', 25));
    }

    /**
     * Returns all published items.
     */
    public function published(): LengthAwarePaginator
    {
        return $this->model->where('is_published', 1)
            ->where('published_at', '<=', Carbon::now(config('app.timezone'))->format('Y-m-d H:i:s'))
            ->orderBy('created_at', 'desc')
            ->paginate(config('cms.pagination', 24));
    }

    /**
     * Returns all public items.
     */
    public function arePublic(): Collection
    {
        if (Schema::hasColumn($this->model->getTable(), 'is_published')) {
            $query = $this->model->where('is_published', 1);

            if (Schema::hasColumn($this->model->getTable(), 'published_at')) {
                $query->where('published_at', '<=', Carbon::now(config('app.timezone'))->format('Y-m-d H:i:s'));
            }

            return $query->orderBy('created_at', 'desc')->get();
        }

        return $this->model->orderBy('created_at', 'desc')->get();
    }

    /**
     * Search the columns of a given table.
     */
    public function search(array $payload): array
    {
        $query = $this->model->orderBy('created_at', 'desc');
        $query->where('id', 'LIKE', '%'.$payload['term'].'%');

        $columns = Schema::getColumnListing($this->table);

        foreach ($columns as $attribute) {
            $query->orWhere($attribute, 'LIKE', '%'.$payload['term'].'%');
        }

        return [$query, $payload['term'], $query->paginate(25)->render()];
    }

    /**
     * Stores Widgets into database.
     */
    public function store(array $payload): CmsModel
    {
        return $this->model->create($payload);
    }

    /**
     * Find Widgets by given id.
     */
    public function find(mixed $id): ?CmsModel
    {
        return $this->model->find($id);
    }

    /**
     * Find items by slug.
     */
    public function getBySlug(string $slug): ?CmsModel
    {
        return $this->model->where('slug', $slug)->first();
    }

    /**
     * Find items by url.
     */
    public function getByUrl(string $url): ?CmsModel
    {
        return $this->model->where('url', $url)->first();
    }

    /**
     * Updates items into database.
     */
    public function update(CmsModel $model, array $payload): CmsModel|bool
    {
        return $model->update($payload);
    }

    /**
     * Convert block payloads into json.
     */
    public function parseBlocks(array $payload, string $module): array
    {
        $blockCollection = [];

        foreach ($payload as $key => $value) {
            if (stristr($key, 'block_')) {
                $blockName = str_replace('block_', '', $key);
                $blockCollection[$blockName] = $value;
                unset($payload[$key]);
            }
        }

        $blockCollection = $this->parseTemplate($payload, $blockCollection, $module);

        if (empty($blockCollection)) {
            $payload['blocks'] = "{}";
        } else {
            $payload['blocks'] = json_encode($blockCollection);
        }

        return $payload;
    }

    /**
     * Parse the template for blocks.
     */
    public function parseTemplate(array $payload, array $currentBlocks, string $module): array
    {
        if (isset($payload['template'])) {
            $content = file_get_contents(base_path('resources/themes/'.config('cms.frontend-theme').'/'.$module.'/'.$payload['template'].'.blade.php'));

            preg_match_all('/->block\((.*)\)/', $content, $pageMethodMatches);
            preg_match_all('/\@block\((.*)\)/', $content, $bladeMatches);

            $matches = array_unique(array_merge($pageMethodMatches[1], $bladeMatches[1]));

            foreach ($matches as $match) {
                $match = str_replace('"', "", $match);
                $match = str_replace("'", "", $match);
                if (! isset($currentBlocks[$match])) {
                    $currentBlocks[$match] = '';
                }
            }
        }

        return $currentBlocks;
    }
}
