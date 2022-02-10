<?php

namespace Grafite\Cms\Controllers;

use Cms;
use Grafite\Cms\Models\Blog;
use Grafite\Cms\Repositories\BlogRepository;
use Grafite\Cms\Requests\BlogRequest;
use Grafite\Cms\Services\ValidationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BlogController extends GrafiteCmsController
{
    public function __construct(BlogRepository $repository)
    {
        parent::construct();

        $this->repository = $repository;
    }

    /**
     * Display a listing of the Blog.
     */
    public function index(): View
    {
        $blogs = $this->repository->paginated();

        return view('cms::modules.blogs.index')
            ->with('blogs', $blogs)
            ->with('pagination', $blogs->render());
    }

    /**
     * Search.
     */
    public function search(Request $request): View
    {
        $input = $request->all();

        $result = $this->repository->search($input);

        return view('cms::modules.blogs.index')
            ->with('blogs', $result[0]->get())
            ->with('pagination', $result[2])
            ->with('term', $result[1]);
    }

    /**
     * Show the form for creating a new Blog.
     */
    public function create(): View
    {
        return view('cms::modules.blogs.create');
    }

    /**
     * Store a newly created Blog in storage.
     *
     * @param BlogRequest $request
     */
    public function store(Request $request): RedirectResponse
    {
        $validation = app(ValidationService::class)->check(Blog::$rules);

        if (! $validation['errors']) {
            $blog = $this->repository->store($request->all());
            Cms::notification('Blog saved successfully.', 'success');
        } else {
            return $validation['redirect'];
        }

        if (! $blog) {
            Cms::notification('Blog could not be saved.', 'warning');
        }

        return redirect(route($this->routeBase.'.blog.edit', [$blog->id]));
    }

    /**
     * Show the form for editing the specified Blog.
     */
    public function edit(int $id): View|RedirectResponse
    {
        $blog = $this->repository->find($id);

        if (empty($blog)) {
            Cms::notification('Blog not found', 'warning');

            return redirect(route($this->routeBase.'.blog.index'));
        }

        return view('cms::modules.blogs.edit')->with('blog', $blog);
    }

    /**
     * Update the specified Blog in storage.
     */
    public function update(int $id, BlogRequest $request): RedirectResponse
    {
        $blog = $this->repository->find($id);

        if (empty($blog)) {
            Cms::notification('Blog not found', 'warning');

            return redirect(route($this->routeBase.'.blog.index'));
        }

        $validation = app(ValidationService::class)->check(Blog::$rules);

        if (! $validation['errors']) {
            $blog = $this->repository->update($blog, $request->all());

            Cms::notification('Blog updated successfully.', 'success');

            if (! $blog) {
                Cms::notification('Blog could not be saved.', 'warning');
            }
        } else {
            return $validation['redirect'];
        }

        return back();
    }

    /**
     * Remove the specified Blog from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $blog = $this->repository->find($id);

        if (empty($blog)) {
            Cms::notification('Blog not found', 'warning');

            return redirect(route($this->routeBase.'.blog.index'));
        }

        $blog->delete();

        Cms::notification('Blog deleted successfully.', 'success');

        return redirect(route($this->routeBase.'.blog.index'));
    }

    /**
     * Blog history.
     */
    public function history(int $id): View
    {
        $blog = $this->repository->find($id);

        return view('cms::modules.blogs.history')
            ->with('blog', $blog);
    }
}
