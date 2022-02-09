<?php

namespace Grafite\Cms\Repositories;

use Carbon\Carbon;
use Cms;
use Grafite\Cms\Models\Blog;
use Grafite\Cms\Models\CmsModel;
use Grafite\Cms\Services\FileService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class BlogRepository extends CmsRepository
{
    public $table;

    public function __construct(public Blog $model, public TranslationRepository $translationRepo)
    {
        $this->table = config('cms.db-prefix').'blogs';
    }

    /**
     * Returns all paginated EventS.
     */
    public function published(): LengthAwarePaginator
    {
        return $this->model->where('is_published', 1)
            ->where('published_at', '<=', Carbon::now(config('app.timezone'))->format('Y-m-d H:i:s'))->orderBy('created_at', 'desc')
            ->paginate(config('cms.pagination', 24));
    }

    /**
     * Blog tags, with similar name.
     */
    public function tags(string $tag): LengthAwarePaginator
    {
        return $this->model->where('is_published', 1)
            ->where('published_at', '<=', Carbon::now(config('app.timezone'))->format('Y-m-d H:i:s'))
            ->where('tags', 'LIKE', '%'.$tag.'%')->orderBy('created_at', 'desc')
            ->paginate(config('cms.pagination', 24));
    }

    /**
     * Gets all tags of an entry.
     */
    public function allTags(): Collection
    {
        $tags = [];

        if (app()->getLocale() !== config('cms.default-language', 'en')) {
            $blogs = $this->translationRepo->getEntitiesByTypeAndLang(app()->getLocale(), 'Grafite\Cms\Models\Blog');
        } else {
            $blogs = $this->model->orderBy('published_at', 'desc')->get();
        }

        foreach ($blogs as $blog) {
            foreach (explode(',', $blog->tags) as $tag) {
                if ($tag !== '') {
                    array_push($tags, $tag);
                }
            }
        }

        return collect(array_unique($tags));
    }

    /**
     * Stores Blog into database.
     */
    public function store(array $payload): Blog
    {
        $payload = $this->parseBlocks($payload, 'blog');

        $payload['title'] = htmlentities($payload['title']);
        $payload['url'] = Cms::convertToURL($payload['url']);
        $payload['is_published'] = (isset($payload['is_published'])) ? (bool) $payload['is_published'] : 0;
        $payload['published_at'] = (isset($payload['published_at']) && ! empty($payload['published_at'])) ? Carbon::parse($payload['published_at'])->format('Y-m-d H:i:s') : Carbon::now(config('app.timezone'))->format('Y-m-d H:i:s');

        if (isset($payload['hero_image'])) {
            $file = request()->file('hero_image');
            $path = app(FileService::class)->saveFile($file, 'public/images', [], true);
            $payload['hero_image'] = $path['name'];
        }

        return $this->model->create($payload);
    }

    /**
     * Find Blog by given URL.
     */
    public function findBlogsByURL(string $url): ?Blog
    {
        $blog = null;

        $blog = $this->model->where('url', $url)->where('is_published', 1)->where('published_at', '<=', Carbon::now(config('app.timezone'))->format('Y-m-d H:i:s'))->first();

        if (! $blog) {
            $blog = $this->translationRepo->findByUrl($url, 'Grafite\Cms\Models\Blog');
        }

        return $blog;
    }

    /**
     * Find Blogs by given Tag.
     */
    public function findBlogsByTag(string $tag): Collection
    {
        return $this->model->where('tags', 'LIKE', "%$tag%")->where('is_published', 1)->get();
    }

    /**
     * Updates Blog into database.
     */
    public function update(CmsModel $blog, array $payload): Blog|bool
    {
        $payload = $this->parseBlocks($payload, 'blog');

        $payload['title'] = htmlentities($payload['title']);

        if (isset($payload['hero_image'])) {
            app(FileService::class)->delete($blog->hero_image);
            $file = request()->file('hero_image');
            $path = app(FileService::class)->saveFile($file, 'public/images', [], true);
            $payload['hero_image'] = $path['name'];
        }

        if (! empty($payload['lang']) && $payload['lang'] !== config('cms.default-language', 'en')) {
            return $this->translationRepo->createOrUpdate($blog->id, 'Grafite\Cms\Models\Blog', $payload['lang'], $payload);
        } else {
            $payload['url'] = Cms::convertToURL($payload['url']);
            $payload['is_published'] = (isset($payload['is_published'])) ? (bool) $payload['is_published'] : 0;
            $payload['published_at'] = (isset($payload['published_at']) && ! empty($payload['published_at'])) ? Carbon::parse($payload['published_at'])->format('Y-m-d H:i:s') : Carbon::now(config('app.timezone'))->format('Y-m-d H:i:s');

            unset($payload['lang']);

            return $blog->update($payload);
        }
    }
}
