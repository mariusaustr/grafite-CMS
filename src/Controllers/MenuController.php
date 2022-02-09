<?php

namespace Grafite\Cms\Controllers;

use Cms;
use Exception;
use Grafite\Cms\Models\Menu;
use Grafite\Cms\Repositories\LinkRepository;
use Grafite\Cms\Repositories\MenuRepository;
use Grafite\Cms\Requests\MenuRequest;
use Grafite\Cms\Services\CmsResponseService;
use Grafite\Cms\Services\ValidationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MenuController extends GrafiteCmsController
{
    protected $linkRepository;

    public function __construct(MenuRepository $repository, LinkRepository $linkRepository)
    {
        parent::construct();

        $this->repository = $repository;
        $this->linkRepository = $linkRepository;
    }

    /**
     * Display a listing of the Menu.
     */
    public function index(): View
    {
        $result = $this->repository->paginated();

        return view('cms::modules.menus.index')
            ->with('menus', $result)
            ->with('pagination', $result->render());
    }

    /**
     * Search.
     */
    public function search(Request $request): View
    {
        $input = $request->all();

        $result = $this->repository->search($input);

        return view('cms::modules.menus.index')
            ->with('menus', $result[0]->get())
            ->with('pagination', $result[2])
            ->with('term', $result[1]);
    }

    /**
     * Show the form for creating a new Menu.
     */
    public function create(): View
    {
        return view('cms::modules.menus.create');
    }

    /**
     * Store a newly created Menu in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $validation = app(ValidationService::class)->check(Menu::$rules);

            if (! $validation['errors']) {
                $menu = $this->repository->store($request->all());
                Cms::notification('Menu saved successfully.', 'success');

                return redirect(route($this->routeBase.'.menus.edit', [$menu->id]));
            } else {
                return $validation['redirect'];
            }
        } catch (Exception $e) {
            Cms::notification($e->getMessage() ?: 'Menu could not be saved.', 'danger');
        }

        return back();
    }

    /**
     * Show the form for editing the specified Menu.
     */
    public function edit(int $id): View|RedirectResponse
    {
        $menu = $this->repository->find($id);

        if (empty($menu)) {
            Cms::notification('Menu not found', 'warning');

            return redirect(route($this->routeBase.'.menus.index'));
        }

        $links = $this->linkRepository->getLinksByMenu($menu->id);

        return view('cms::modules.menus.edit')->with('menu', $menu)->with('links', $links);
    }

    /**
     * Update the specified Menu in storage.
     */
    public function update(int $id, MenuRequest $request): RedirectResponse
    {
        try {
            $menu = $this->repository->find($id);

            if (empty($menu)) {
                Cms::notification('Menu not found', 'warning');

                return redirect(route($this->routeBase.'.menus.index'));
            }

            $menu = $this->repository->update($menu, $request->all());
            Cms::notification('Menu updated successfully.', 'success');

            if (! $menu) {
                Cms::notification('Menu could not be updated.', 'danger');
            }
        } catch (Exception $e) {
            Cms::notification($e->getMessage() ?: 'Menu could not be updated.', 'danger');
        }

        return redirect(route($this->routeBase.'.menus.edit', [$id]));
    }

    /**
     * Remove the specified Menu from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $menu = $this->repository->find($id);

        if (empty($menu)) {
            Cms::notification('Menu not found', 'warning');

            return redirect(route($this->routeBase.'.menus.index'));
        }

        $menu->delete();

        Cms::notification('Menu deleted successfully.');

        return redirect(route($this->routeBase.'.menus.index'));
    }

    /*
    |--------------------------------------------------------------------------
    | Api
    |--------------------------------------------------------------------------
    */

    /**
     * Set the order.
     */
    public function setOrder(int $id, Request $request): JsonResponse
    {
        $menu = $this->repository->find($id);
        $result = $this->repository->setOrder($menu, $request->except('_token'));

        return app(CmsResponseService::class)->apiResponse('success', $result);
    }
}
