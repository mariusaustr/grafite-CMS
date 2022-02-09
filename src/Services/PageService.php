<?php

namespace Grafite\Cms\Services;

use Grafite\Cms\Models\Page;
use Grafite\Cms\Repositories\PageRepository;

class PageService extends BaseService
{
    public function __construct(private PageRepository $repo)
    {
    }

    /**
     * Get pages as options.
     */
    public function getPagesAsOptions(): array
    {
        $pages = [];
        $publishedPages = $this->repo->all();

        foreach ($publishedPages as $page) {
            $pages[$page->title] = $page->id;
        }

        return $pages;
    }

    /**
     * Get templates as options.
     */
    public function getTemplatesAsOptions(): array
    {
        return $this->getTemplatesAsOptionsArray('pages');
    }

    /**
     * Get a page name by ID.
     */
    public function pageName(int $id): ?string
    {
        /** @var ?Page $page */
        $page = $this->repo->find($id);

        return $page?->title;
    }
}
