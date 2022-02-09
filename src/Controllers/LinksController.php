<?php

namespace Grafite\Cms\Controllers;

use Cms;
use Exception;
use Grafite\Cms\Models\Link;
use Grafite\Cms\Repositories\LinkRepository;
use Grafite\Cms\Requests\LinksRequest;
use Grafite\Cms\Services\ValidationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LinksController extends GrafiteCmsController
{
    public function __construct(LinkRepository $repository)
    {
        parent::construct();

        $this->repository = $repository;
    }

    /**
     * Display a listing of the Links.
     */
    public function index(): View
    {
        $result = $this->repository->paginated();

        return view('cms::modules.links.index')
            ->with('links', $result)
            ->with('pagination', $result->render());
    }

    /**
     * Show the form for creating a new Links.
     */
    public function create(Request $request)
    {
        $menu = $request->get('m');

        return view('cms::modules.links.create')->with('menu_id', $menu);
    }

    /**
     * Store a newly created Links in storage.
     */
    public function store(LinksRequest $request): RedirectResponse
    {
        try {
            $validation = app(ValidationService::class)->check(Link::$rules);

            if (! $validation['errors']) {
                $links = $this->repository->store($request->all());
                Cms::notification('Link saved successfully.', 'success');

                if (! $links) {
                    Cms::notification('Link could not be saved.', 'danger');
                }
            } else {
                return $validation['redirect'];
            }
        } catch (Exception $e) {
            Cms::notification($e->getMessage() ?: 'Link could not be saved.', 'danger');
        }

        return redirect(route($this->routeBase.'.menus.edit', [$request->get('menu_id')]));
    }

    /**
     * Show the form for editing the specified Links.
     */
    public function edit(int $id): View|RedirectResponse
    {
        $links = $this->repository->find($id);

        if (empty($links)) {
            Cms::notification('Link not found', 'warning');

            return redirect(route($this->routeBase.'.links.index'));
        }

        return view('cms::modules.links.edit')->with('links', $links);
    }

    /**
     * Update the specified Links in storage.
     */
    public function update(int $id, LinksRequest $request): RedirectResponse
    {
        try {
            $links = $this->repository->find($id);

            if (empty($links)) {
                Cms::notification('Link not found', 'warning');

                return redirect(route($this->routeBase.'.links.index'));
            }

            $links = $this->repository->update($links, $request->all());
            Cms::notification('Link updated successfully.', 'success');

            if (! $links) {
                Cms::notification('Link could not be updated.', 'danger');
            }
        } catch (Exception $e) {
            Cms::notification($e->getMessage() ?: 'Links could not be updated.', 'danger');
        }

        return redirect(route($this->routeBase.'.links.edit', [$id]));
    }

    /**
     * Remove the specified Links from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $link = $this->repository->find($id);
        $menu = $link->menu_id;

        if (empty($link)) {
            Cms::notification('Link not found', 'warning');

            return redirect(route($this->routeBase.'.menus.index'));
        }

        $order = json_decode($link->menu->order);
        $key = array_search($id, $order);
        unset($order[$key]);

        $link->menu->update([
            'order' => json_encode(array_values($order)),
        ]);

        $link->delete();

        Cms::notification('Link deleted successfully.', 'success');

        return redirect(route($this->routeBase.'.menus.edit', [$link->menu_id]));
    }
}
