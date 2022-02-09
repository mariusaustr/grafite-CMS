<?php

namespace Grafite\Cms\Services;

use Cms;
use Exception;
use Grafite\Cms\Facades\CryptoServiceFacade;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use SplFileInfo;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AssetService
{
    protected $mimeTypes;

    public function __construct()
    {
        $this->mimeTypes = require __DIR__.'/../Assets/mimes.php';
    }

    /**
     * Provide the File as a Public Asset.
     */
    public function asPublic(string $encFileName): HttpResponse
    {
        try {
            return Cache::remember($encFileName.'_asPublic', 3600, function () use ($encFileName) {
                $fileName = CryptoServiceFacade::url_decode($encFileName);
                $filePath = $this->getFilePath($fileName);

                $fileTool = new SplFileInfo($filePath);
                $ext = $fileTool->getExtension();
                $contentType = $this->getMimeType($ext);

                $headers = ['Content-Type' => $contentType];
                $fileContent = $this->getFileContent($fileName, $contentType, $ext);

                return Response::make($fileContent, 200, [
                    'Content-Type' => $contentType,
                    'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
                ]);
            });
        } catch (Exception $e) {
            return Response::make('file not found');
        }
    }

    /**
     * Provide the File as a Public Preview.
     */
    public function asPreview(string $encFileName, Filesystem $fileSystem): HttpResponse
    {
        try {
            return Cache::remember($encFileName.'_preview', 3600, function () use ($encFileName, $fileSystem) {
                $fileName = CryptoServiceFacade::url_decode($encFileName);

                if (config('cms.storage-location') === 'local' || config('cms.storage-location') === null) {
                    $filePath = storage_path('app/'.$fileName);
                    $contentType = $fileSystem->mimeType($filePath);
                    $ext = strtoupper($fileSystem->extension($filePath));
                } else {
                    $filePath = Storage::disk(config('cms.storage-location', 'local'))->url($fileName);
                    $fileTool = new SplFileInfo($filePath);
                    $ext = $fileTool->getExtension();
                    $contentType = $this->getMimeType($ext);
                }

                if (stristr($contentType, 'image')) {
                    $headers = ['Content-Type' => $contentType];
                    $fileContent = $this->getFileContent($fileName, $contentType, $ext);
                } else {
                    $fileContent = file_get_contents($this->generateImage($ext));
                }

                return Response::make($fileContent, 200, [
                    'Content-Type' => $contentType,
                    'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
                ]);
            });
        } catch (Exception $e) {
            return Response::make('file not found');
        }
    }

    /**
     * Provide file as download.
     */
    public function asDownload(string $encFileName, string $encRealFileName): HttpResponse|RedirectResponse
    {
        try {
            return Cache::remember($encFileName.'_asDownload', 3600, function () use ($encFileName, $encRealFileName) {
                $fileName = CryptoServiceFacade::url_decode($encFileName);
                $realFileName = CryptoServiceFacade::url_decode($encRealFileName);
                $filePath = $this->getFilePath($fileName);

                $fileTool = new SplFileInfo($filePath);
                $ext = $fileTool->getExtension();
                $contentType = $this->getMimeType($ext);

                $headers = ['Content-Type' => $contentType];
                $fileContent = $this->getFileContent($realFileName, $contentType, $ext);

                return Response::make($fileContent, 200, [
                    'Content-Type' => $contentType,
                    'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
                ]);
            });
        } catch (Exception $e) {
            Cms::notification('We encountered an error with that file', 'danger');

            return redirect('errors/general');
        }
    }

    /**
     * Gets an asset.
     */
    public function asset(string $encPath, ?string $contentType, Filesystem $fileSystem): HttpResponse|BinaryFileResponse
    {
        try {
            $path = CryptoServiceFacade::url_decode($encPath);

            if (Request::get('isModule') === 'true') {
                $filePath = $path;
            } else {
                if (str_contains($path, 'dist/') || str_contains($path, 'themes/')) {
                    $filePath = __DIR__.'/../Assets/'.$path;
                } else {
                    $filePath = __DIR__.'/../Assets/src/'.$path;
                }
            }

            $fileName = basename($filePath);

            if (! is_null($contentType)) {
                $contentType = CryptoServiceFacade::url_decode($contentType);
            } else {
                $contentType = $fileSystem->mimeType($fileName);
            }

            $headers = ['Content-Type' => $contentType];

            return response()->download($filePath, $fileName, $headers);
        } catch (Exception $e) {
            return Response::make('file not found');
        }
    }

    /**
     * Get the mime type.
     */
    public function getMimeType(string $extension): string
    {
        if (isset($this->mimeTypes['.'.strtolower($extension)])) {
            return $this->mimeTypes['.'.strtolower($extension)];
        }

        return 'text/plain';
    }

    /**
     * Get a file's path.
     */
    public function getFilePath(string $fileName): string
    {
        if (file_exists(storage_path('app/'.$fileName))) {
            $filePath = storage_path('app/'.$fileName);
        } else {
            $filePath = Storage::disk(config('cms.storage-location', 'local'))->url($fileName);
        }

        return $filePath;
    }

    /**
     * Get a files content.
     */
    public function getFileContent(string $fileName, string $contentType, string $ext): string
    {
        if (Storage::disk(config('cms.storage-location', 'local'))->exists($fileName)) {
            $fileContent = Storage::disk(config('cms.storage-location', 'local'))->get($fileName);
        } elseif (! is_null(config('filesystems.cloud.key'))) {
            $fileContent = Storage::disk('cloud')->get($fileName);
        } else {
            $fileContent = file_get_contents($this->generateImage('File Not Found'));
        }

        if (stristr($fileName, 'image') || stristr($contentType, 'image')) {
            if (! is_null(config('cms.preview-image-size'))) {
                $img = Image::make($fileContent);
                $img->resize(config('cms.preview-image-size', 800), null, function ($constraint) {
                    $constraint->aspectRatio();
                });

                return $img->encode($ext);
            }
        }

        return $fileContent;
    }

    /**
     * Generate an image.
     */
    public function generateImage(string $type): string
    {
        if ($type == 'File Not Found') {
            return __DIR__.'/../Assets/src/images/blank-file-not-found.jpg';
        }

        return __DIR__.'/../Assets/src/images/blank-file.jpg';
    }
}
