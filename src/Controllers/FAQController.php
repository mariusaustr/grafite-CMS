<?php

namespace Grafite\Cms\Controllers;

use Cms;
use Grafite\Cms\Models\FAQ;
use Grafite\Cms\Repositories\FAQRepository;
use Grafite\Cms\Requests\FAQRequest;
use Grafite\Cms\Services\ValidationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use URL;

class FAQController extends GrafiteCmsController
{
    public function __construct(FAQRepository $repository)
    {
        parent::construct();

        $this->repository = $repository;
    }

    /**
     * Display a listing of the FAQ.
     */
    public function index(): View
    {
        $result = $this->repository->paginated();

        return view('cms::modules.faqs.index')
            ->with('faqs', $result)
            ->with('pagination', $result->render());
    }

    /**
     * Search.
     */
    public function search(Request $request): View
    {
        $input = $request->all();

        $result = $this->repository->search($input);

        return view('cms::modules.faqs.index')
            ->with('faqs', $result[0]->get())
            ->with('pagination', $result[2])
            ->with('term', $result[1]);
    }

    /**
     * Show the form for creating a new FAQ.
     */
    public function create(): View
    {
        return view('cms::modules.faqs.create');
    }

    /**
     * Store a newly created FAQ in storage.
     *
     * @param FAQRequest $request
     */
    public function store(Request $request): RedirectResponse
    {
        $validation = app(ValidationService::class)->check(FAQ::$rules);

        if (! $validation['errors']) {
            $faq = $this->repository->store($request->all());
            Cms::notification('FAQ saved successfully.', 'success');
        } else {
            return $validation['redirect'];
        }

        if (! $faq) {
            Cms::notification('FAQ could not be saved.', 'warning');
        }

        return redirect(route($this->routeBase.'.faqs.edit', [$faq->id]));
    }

    /**
     * Show the form for editing the specified FAQ.
     */
    public function edit(int $id): View|RedirectResponse
    {
        $faq = $this->repository->find($id);

        if (empty($faq)) {
            Cms::notification('FAQ not found', 'warning');

            return redirect(route($this->routeBase.'.faqs.index'));
        }

        return view('cms::modules.faqs.edit')->with('faq', $faq);
    }

    /**
     * Update the specified FAQ in storage.
     *
     * @param int        $id
     * @param FAQRequest $request
     */
    public function update(int $id, FAQRequest $request)
    {
        $faq = $this->repository->find($id);

        if (empty($faq)) {
            Cms::notification('FAQ not found', 'warning');

            return redirect(route($this->routeBase.'.faqs.index'));
        }

        $validation = app(ValidationService::class)->check(FAQ::$rules);

        if (! $validation['errors']) {
            $faq = $this->repository->update($faq, $request->all());
            Cms::notification('FAQ updated successfully.', 'success');

            if (! $faq) {
                Cms::notification('FAQ could not be saved.', 'warning');
            }
        } else {
            return $validation['redirect'];
        }

        return redirect(URL::previous());
    }

    /**
     * Remove the specified FAQ from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $faq = $this->repository->find($id);

        if (empty($faq)) {
            Cms::notification('FAQ not found', 'warning');

            return redirect(route($this->routeBase.'.faqs.index'));
        }

        $faq->delete();

        Cms::notification('FAQ deleted successfully.', 'success');

        return redirect(route($this->routeBase.'.faqs.index'));
    }
}
