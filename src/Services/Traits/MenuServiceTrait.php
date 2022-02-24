<?php

namespace Grafite\Cms\Services\Traits;

use Carbon\Carbon;
use Grafite\Cms\Models\Menu;
use Grafite\Cms\Models\Page;
use Grafite\Cms\Models\Translation;
use Grafite\Cms\Repositories\LinkRepository;
use Grafite\Cms\Repositories\MenuRepository;
use Grafite\Cms\Repositories\PageRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;

trait MenuServiceTrait
{
    /**
     * Cms package Menus.
     */
    public function packageMenus(): void
    {
        $packageViews = Config::get('cms.package-menus', []);

        foreach ($packageViews as $view) {
            include $view;
        }
    }

    /**
     * Get a view.
     */
    public function menu(string $slug, string $view = null, string $class = ''): string
    {
        $pageRepository = app(PageRepository::class);
        /** @var ?Menu $menu */
        $menu = app(MenuRepository::class)->getBySlug($slug);

        if (! $menu) {
            return '';
        }

        $links = app(LinkRepository::class)->getLinksByMenu($menu->id);
        $order = json_decode($menu->order);
        // Sort the links by the order from the menu
        $links = $this->sortByKeys($links, $order);

        $response = '';
        $processedLinks = [];
        foreach ($links as $key => $link) {
            if ($link->external) {
                if (config('app.locale') != config('cms.default-language', $this->config('cms.default-language'))) {
                    $processedLinks[] = '<a class="'.$class.'" href="'.$link->external_url.'">'.$link->translation(config('app.locale'))->name.'</a>';
                } else {
                    $processedLinks[] = '<a class="'.$class.'" href="'.$link->external_url.'">'.$link->name.'</a>';
                }
            } else {
                /** @var ?Page $page */
                $page = $pageRepository->find($link->page_id);
                // if the page is published
                if ($page && $page->is_published && $page->published_at <= Carbon::now(config('app.timezone'))) {
                    if (config('app.locale') === config('cms.default-language', $this->config('cms.default-language'))) {
                        $processedLinks[] = '<a class="'.$class.'" href="'.url('page/'.$page->url)."\">$link->name</a>";
                    } elseif (config('app.locale') != config('cms.default-language', $this->config('cms.default-language'))) {
                        // if the page has a translation
                        if ($page->translation(config('app.locale')) instanceof Translation) {
                            $processedLinks[] = '<a class="'.$class.'" href="'.url('page/'.$page->translation(config('app.locale'))->data->url).'">'.$link->translation(config('app.locale'))->name.'</a>';
                        }
                    }
                } else {
                    unset($links[$key]);
                }
            }
        }
        if (! is_null($view)) {
            $response = view($view, ['links' => $links, 'processed_links' => $processedLinks]);
        }

        if (Gate::allows('cms', Auth::user()) && config('cms.frontend-module-settings.menus.edit-button')) {
            if (is_null($view)) {
                $response = implode(',', $processedLinks);
            }
            $response .= '<a href="'.url(config('cms.backend-route-prefix', 'cms').'/menus/'.$menu->id.'/edit').'" class="btn btn-sm ml-2 btn-outline-secondary"><span class="fa fa-edit"></span> Edit</a>';
        }

        return $response;
    }

    /**
     * Sort by an existing set of keys.
     */
    public function sortByKeys(Collection $links, array $keys = null): Collection
    {
        if (! is_null($keys)) {
            $links = $links->keyBy('id');

            $sortedLinks = [];
            foreach ($keys as $key) {
                $sortedLinks[] = $links[$key];
            }

            return collect($sortedLinks);
        }

        return $links;
    }
}
