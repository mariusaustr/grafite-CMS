<?php

namespace Grafite\Cms\Controllers;

use Cms;
use CryptoService;
use Exception;
use Grafite\Cms\Models\File;
use Grafite\Cms\Repositories\FileRepository;
use Grafite\Cms\Requests\FileRequest;
use Grafite\Cms\Services\CmsResponseService;
use Grafite\Cms\Services\FileService;
use Grafite\Cms\Services\ValidationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class FilesController extends GrafiteCmsController
{
    public function __construct(
        FileRepository $repository,
        private FileService $fileService,
        private ValidationService $validation,
        private CmsResponseService $responseService
    ) {
        parent::construct();
        $this->repository = $repository;
    }

    /**
     * Display a listing of the Files.
     */
    public function index(): View
    {
        $result = $this->repository->paginated();

        return view('cms::modules.files.index')
            ->with('files', $result)
            ->with('pagination', $result->render());
    }

    /**
     * Search.
     */
    public function search(Request $request): View
    {
        $input = $request->all();

        $result = $this->repository->search($input);

        return view('cms::modules.files.index')
            ->with('files', $result[0]->get())
            ->with('pagination', $result[2])
            ->with('term', $result[1]);
    }

    /**
     * Show the form for creating a new Files.
     */
    public function create(): View
    {
        return view('cms::modules.files.create');
    }

    /**
     * Store a newly created Files in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validation = $this->validation->check(File::$rules);

        if (! $validation['errors']) {
            $file = $this->repository->store($request->all());
        } else {
            return $validation['redirect'];
        }

        Cms::notification('File saved successfully.', 'success');

        return redirect(route($this->routeBase.'.files.index'));
    }

    /**
     * Store a newly created Files in storage.
     */
    public function upload(Request $request): JsonResponse
    {
        $validation = $this->validation->check([
            'location' => [],
        ]);

        if (! $validation['errors']) {
            $file = $request->file('location');
            $fileSaved = $this->fileService->saveFile($file, 'files/');
            $fileSaved['name'] = CryptoService::encrypt($fileSaved['name']);
            $fileSaved['mime'] = $file->getClientMimeType();
            $fileSaved['size'] = $file->getSize();
            $response = $this->responseService->apiResponse('success', $fileSaved);
        } else {
            $response = $this->responseService->apiErrorResponse($validation['errors'], $validation['inputs']);
        }

        return $response;
    }

    /**
     * Remove a file.
     */
    public function remove(string $id): JsonResponse
    {
        try {
            Storage::delete($id);
            $response = $this->responseService->apiResponse('success', 'success!');
        } catch (Exception $e) {
            $response = $this->responseService->apiResponse('error', $e->getMessage());
        }

        return $response;
    }

    /**
     * Show the form for editing the specified Files.
     */
    public function edit(int $id): View|RedirectResponse
    {
        $files = $this->repository->find($id);

        if (empty($files)) {
            Cms::notification('File not found', 'warning');

            return redirect(route($this->routeBase.'.files.index'));
        }

        return view('cms::modules.files.edit')->with('files', $files);
    }

    /**
     * Update the specified Files in storage.
     */
    public function update(int $id, FileRequest $request): RedirectResponse
    {
        $files = $this->repository->find($id);

        if (empty($files)) {
            Cms::notification('File not found', 'warning');

            return redirect(route($this->routeBase.'.files.index'));
        }

        $files = $this->repository->update($files, $request->all());

        Cms::notification('File updated successfully.', 'success');

        return Redirect::back();
    }

    /**
     * Remove the specified Files from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $files = $this->repository->find($id);

        if (empty($files)) {
            Cms::notification('File not found', 'warning');

            return redirect(route($this->routeBase.'.files.index'));
        }

        if (is_file(storage_path($files->location))) {
            Storage::delete($files->location);
        } else {
            Storage::disk(config('cms.storage-location', 'local'))->delete($files->location);
        }

        $files->delete();

        Cms::notification('File deleted successfully.', 'success');

        return redirect(route($this->routeBase.'.files.index'));
    }

    /**
     * Display the specified Images.
     */
    public function apiList(Request $request): JsonResponse
    {
        if (config('cms.api-key') != $request->header('cms')) {
            return $this->responseService->apiResponse('error', []);
        }

        $files = $this->repository->apiPrepared();

        return $this->responseService->apiResponse('success', $files);
    }
}
