<?php

namespace Grafite\Cms\Controllers;

use Grafite\Cms\Services\AssetService;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AssetController extends GrafiteCmsController
{
    public function __construct(private AssetService $service)
    {
        parent::construct();
    }

    /**
     * Provide the File as a Public Asset.
     */
    public function asPublic(string $encFileName): Response
    {
        return $this->service->asPublic($encFileName);
    }

    /**
     * Provide the File as a Public Preview.
     */
    public function asPreview(string $encFileName, Filesystem $fileSystem): Response
    {
        return $this->service->asPreview($encFileName, $fileSystem);
    }

    /**
     * Provide file as download.
     */
    public function asDownload(string $encFileName, string $encRealFileName): Response
    {
        return $this->service->asDownload($encFileName, $encRealFileName);
    }

    /**
     * Gets an asset.
     */
    public function asset(string $encPath, string $contentType, Filesystem $fileSystem): Response|BinaryFileResponse
    {
        return $this->service->asset($encPath, $contentType, $fileSystem);
    }
}
