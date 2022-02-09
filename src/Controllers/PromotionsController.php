<?php

namespace Grafite\Cms\Controllers;

use Cms;
use Grafite\Cms\Models\Promotion;
use Grafite\Cms\Repositories\PromotionRepository;
use Grafite\Cms\Requests\PromotionRequest;
use Grafite\Cms\Services\ValidationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PromotionsController extends GrafiteCmsController
{
    public function __construct(PromotionRepository $repository)
    {
        $this->repository = $repository;
        parent::construct();
    }

    /**
     * Display a listing of the Promotions.
     */
    public function index(): View
    {
        $result = $this->repository->paginated();

        return view('cms::modules.promotions.index')
            ->with('promotions', $result)
            ->with('pagination', $result->render());
    }

    /**
     * Search.
     */
    public function search(Request $request): View
    {
        $input = $request->all();

        $result = $this->repository->search($input);

        return view('cms::modules.promotions.index')
            ->with('promotion', $result[0]->get())
            ->with('pagination', $result[2])
            ->with('term', $result[1]);
    }

    /**
     * Show the form for creating a new Promotions.
     */
    public function create(): View
    {
        return view('cms::modules.promotions.create');
    }

    /**
     * Store a newly created Promotions in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validation = app(ValidationService::class)->check(Promotion::$rules);

        if (! $validation['errors']) {
            $promotion = $this->repository->store($request->all());
        } else {
            return $validation['redirect'];
        }

        Cms::notification('Promotions saved successfully.', 'success');

        return redirect(route($this->routeBase.'.promotions.edit', [$promotion->id]));
    }

    /**
     * Show the form for editing the specified Promotions.
     */
    public function edit(int $id): View|RedirectResponse
    {
        $promotion = $this->repository->find($id);

        if (empty($promotion)) {
            Cms::notification('Promotions not found', 'warning');

            return redirect(route($this->routeBase.'.promotions.index'));
        }

        return view('cms::modules.promotions.edit')->with('promotion', $promotion);
    }

    /**
     * Update the specified Promotions in storage.
     */
    public function update(int $id, PromotionRequest $request): RedirectResponse
    {
        $promotion = $this->repository->find($id);

        if (empty($promotion)) {
            Cms::notification('Promotions not found', 'warning');

            return redirect(route($this->routeBase.'.promotions.index'));
        }

        $promotion = $this->repository->update($promotion, $request->all());

        Cms::notification('Promotions updated successfully.', 'success');

        return redirect(url()->previous());
    }

    /**
     * Remove the specified Promotions from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $promotion = $this->repository->find($id);

        if (empty($promotion)) {
            Cms::notification('Promotions not found', 'warning');

            return redirect(route($this->routeBase.'.promotions.index'));
        }

        $promotion->delete();

        Cms::notification('Promotions deleted successfully.', 'success');

        return redirect(route($this->routeBase.'.promotions.index'));
    }
}
