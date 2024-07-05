<?php

namespace Grafite\Cms\Services;

use CryptoService as CryptoServiceForFiles;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image as InterventionImage;

class FileService
{
    /**
     * Generate a name from the file path.
     */
    public function getFileClass(string $file): string
    {
        $sections = explode(DIRECTORY_SEPARATOR, $file);
        $fileName = $sections[count($sections) - 1];

        $class = str_replace('.php', '', $fileName);

        return $class;
    }

    /**
     * Saves File.
     */
    public function saveClone(string $fileName, string $directory = '', array $fileTypes = []): array
    {
        $fileInfo = pathinfo($fileName);

        if (substr($directory, 0, -1) != '/') {
            $directory .= '/';
        }

        $extension = $fileInfo['extension'];
        $newFileName = md5(rand(1111, 9999).time());

        // In case we don't want that file type
        if (! empty($fileTypes)) {
            if (! in_array($extension, $fileTypes)) {
                throw new Exception('Incorrect file type', 1);
            }
        }

        Storage::disk(Config::get('cms.storage-location', 'local'))->put($directory.$newFileName.'.'.$extension, file_get_contents($fileName));

        return [
            'original' => basename($fileName),
            'name' => $directory.$newFileName.'.'.$extension,
        ];
    }

    public function delete($path): bool
    {
        if (is_file(storage_path($path))) {
            return Storage::delete($path);
        } else {
            return Storage::disk(config('cms.storage-location', 'local'))->delete($path);
        }
    }

    /**
     * Saves File.
     */
    public function saveFile(string|UploadedFile $fileName, string $directory = '', array $fileTypes = [], bool $isImage = false): array|false
    {
        if ($fileName instanceof UploadedFile) {
            $file = $fileName;
            $originalName = $file->getClientOriginalName();
        } else {
            $file = Request::file($fileName);
            $originalName = false;
        }

        if (is_null($file)) {
            return false;
        }

        if (File::size($file) > Config::get('cms.max-file-upload-size', 6291456)) {
            throw new Exception('This file is too large', 1);
        }

        if (substr($directory, 0, -1) != '/') {
            $directory .= '/';
        }

        $extension = $file->getClientOriginalExtension();
        $newFileName = md5(rand(1111, 9999).time());

        // In case we don't want that file type
        if (! empty($fileTypes)) {
            if (! in_array($extension, $fileTypes)) {
                throw new Exception('Incorrect file type', 1);
            }
        }

        $storage = Storage::disk(Config::get('cms.storage-location', 'local'));
        $storage->put($directory.$newFileName.'.'.$extension, File::get($file));

        // Resize images only
        if ($isImage) {
            $image = $storage->get($directory.$newFileName.'.'.$extension);

            $image = InterventionImage::make($image)->resize(config('cms.max-image-size', 800), null, function ($constraint) {
                $constraint->aspectRatio();
            });

            $imageResized = $image->stream();

            $storage->delete($directory.$newFileName.'.'.$extension);
            $storage->put($directory.$newFileName.'.'.$extension, $imageResized->__toString());
        }

        return [
            'original' => $originalName ?: ($file->getFilename().'.'.$extension),
            'name' => Str::finish((string) data_get($storage->getConfig(), 'root'), '/').$directory.$newFileName.'.'.$extension,
        ];
    }

    /**
     * Provide a URL for the file as a public asset.
     */
    public function fileAsPublicAsset(string $fileName): string
    {
        return '/public-asset/'.CryptoServiceForFiles::url_encode($fileName);
    }

    /**
     * Provides a URL for the file as a download.
     */
    public function fileAsDownload(string $fileName, string $realFileName): string
    {
        return '/public-download/'.CryptoServiceForFiles::url_encode($fileName).'/'.CryptoServiceForFiles::url_encode($realFileName);
    }

    /**
     * Provide a URL for the file as a public preview.
     */
    public function filePreview(string $fileName): string
    {
        return '/public-preview/'.CryptoServiceForFiles::url_encode($fileName);
    }
}
