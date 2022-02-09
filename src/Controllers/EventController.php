<?php

namespace Grafite\Cms\Controllers;

use Cms;
use Grafite\Cms\Models\Event;
use Grafite\Cms\Repositories\EventRepository;
use Grafite\Cms\Requests\EventRequest;
use Grafite\Cms\Services\ValidationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use URL;

class EventController extends GrafiteCmsController
{
    public function __construct(EventRepository $repository)
    {
        parent::construct();

        $this->repository = $repository;
    }

    /**
     * Display a listing of the Event.
     */
    public function index(): View
    {
        $result = $this->repository->paginated();

        return view('cms::modules.events.index')
            ->with('events', $result)
            ->with('pagination', $result->render());
    }

    /**
     * Search.
     */
    public function search(Request $request): View
    {
        $input = $request->all();

        $result = $this->repository->search($input);

        return view('cms::modules.events.index')
            ->with('events', $result[0]->get())
            ->with('pagination', $result[2])
            ->with('term', $result[1]);
    }

    /**
     * Show the form for creating a new Event.
     */
    public function create(): View
    {
        return view('cms::modules.events.create');
    }

    /**
     * Store a newly created Event in storage.
     *
     * @param EventRequest $request
     */
    public function store(Request $request): RedirectResponse
    {
        $validation = app(ValidationService::class)->check(Event::$rules);

        if (! $validation['errors']) {
            $event = $this->repository->store($request->all());
            Cms::notification('Event saved successfully.', 'success');
        } else {
            return $validation['redirect'];
        }

        if (! $event) {
            Cms::notification('Event could not be saved.', 'warning');
        }

        return redirect(route($this->routeBase.'.events.edit', [$event->id]));
    }

    /**
     * Show the form for editing the specified Event.
     */
    public function edit(int $id): View|RedirectResponse
    {
        $event = $this->repository->find($id);

        if (empty($event)) {
            Cms::notification('Event not found', 'warning');

            return redirect(route($this->routeBase.'.events.index'));
        }

        return view('cms::modules.events.edit')->with('event', $event);
    }

    /**
     * Update the specified Event in storage.
     */
    public function update(int $id, EventRequest $request): RedirectResponse
    {
        $event = $this->repository->find($id);

        if (empty($event)) {
            Cms::notification('Event not found', 'warning');

            return redirect(route($this->routeBase.'.events.index'));
        }

        $event = $this->repository->update($event, $request->all());
        Cms::notification('Event updated successfully.', 'success');

        if (! $event) {
            Cms::notification('Event could not be saved.', 'warning');
        }

        return redirect(URL::previous());
    }

    /**
     * Remove the specified Event from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $event = $this->repository->find($id);

        if (empty($event)) {
            Cms::notification('Event not found', 'warning');

            return redirect(route($this->routeBase.'.events.index'));
        }

        $event->delete();

        Cms::notification('Event deleted successfully.', 'success');

        return redirect(route($this->routeBase.'.events.index'));
    }

    /**
     * Page history.
     */
    public function history(int $id): View
    {
        $event = $this->repository->find($id);

        return view('cms::modules.events.history')
            ->with('event', $event);
    }
}
