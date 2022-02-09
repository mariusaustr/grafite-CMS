<?php

namespace Grafite\Cms\Controllers;

use Cms;
use Grafite\Cms\Models\Widget;
use Grafite\Cms\Repositories\WidgetRepository;
use Grafite\Cms\Requests\WidgetRequest;
use Grafite\Cms\Services\ValidationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WidgetsController extends GrafiteCmsController
{
    public function __construct(WidgetRepository $repository)
    {
        parent::construct();

        $this->repository = $repository;
    }

    /**
     * Display a listing of the Widgets.
     */
    public function index(): View
    {
        $result = $this->repository->paginated();

        return view('cms::modules.widgets.index')
            ->with('widgets', $result)
            ->with('pagination', $result->render());
    }

    /**
     * Search.
     */
    public function search(Request $request): View
    {
        $input = $request->all();

        $result = $this->repository->search($input);

        return view('cms::modules.widgets.index')
            ->with('widgets', $result[0]->get())
            ->with('pagination', $result[2])
            ->with('term', $result[1]);
    }

    /**
     * Show the form for creating a new Widgets.
     */
    public function create(): View
    {
        return view('cms::modules.widgets.create');
    }

    /**
     * Store a newly created Widgets in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validation = app(ValidationService::class)->check(Widget::$rules);

        if (! $validation['errors']) {
            $widgets = $this->repository->store($request->all());
        } else {
            return $validation['redirect'];
        }

        Cms::notification('Widgets saved successfully.', 'success');

        return redirect(route($this->routeBase.'.widgets.edit', [$widgets->id]));
    }

    /**
     * Show the form for editing the specified Widgets.
     */
    public function edit(int $id): View|RedirectResponse
    {
        $widget = $this->repository->find($id);

        if (empty($widget)) {
            Cms::notification('Widgets not found', 'warning');

            return redirect(route($this->routeBase.'.widgets.index'));
        }

        return view('cms::modules.widgets.edit')->with('widget', $widget);
    }

    /**
     * Update the specified Widgets in storage.
     */
    public function update(int $id, WidgetRequest $request): RedirectResponse
    {
        $widgets = $this->repository->find($id);

        if (empty($widgets)) {
            Cms::notification('Widgets not found', 'warning');

            return redirect(route($this->routeBase.'.widgets.index'));
        }

        $widgets = $this->repository->update($widgets, $request->all());

        Cms::notification('Widgets updated successfully.', 'success');

        return redirect(url()->previous());
    }

    /**
     * Remove the specified Widgets from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $widgets = $this->repository->find($id);

        if (empty($widgets)) {
            Cms::notification('Widgets not found', 'warning');

            return redirect(route($this->routeBase.'.widgets.index'));
        }

        $widgets->delete();

        Cms::notification('Widgets deleted successfully.', 'success');

        return redirect(route($this->routeBase.'.widgets.index'));
    }
}
