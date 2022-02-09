<?php

namespace Grafite\Cms\Repositories;

use Auth;
use CryptoService;
use Grafite\Cms\Models\CmsModel;
use Grafite\Cms\Models\File;
use Grafite\Cms\Services\FileService;

class FileRepository extends CmsRepository
{
    public $table;

    public function __construct(public File $model)
    {
        $this->table = config('cms.db-prefix').'files';
    }

    /**
     * Stores Files into database.
     */
    public function store(array $payload): File
    {
        $result = false;

        foreach ($payload['location'] as $file) {
            $filePayload = $payload;
            $filePayload['name'] = $file['original'];
            $filePayload['location'] = CryptoService::decrypt($file['name']);
            $filePayload['mime'] = $file['mime'];
            $filePayload['size'] = $file['size'];
            $filePayload['order'] = 0;
            $filePayload['user'] = (isset($payload['user'])) ? $payload['user'] : Auth::id();
            $filePayload['is_published'] = (isset($payload['is_published'])) ? (bool) $payload['is_published'] : 0;
            $result = $this->model->create($filePayload);
        }

        return $result;
    }

    /**
     * Updates Files into database.
     */
    public function update(CmsModel $files, array $payload): File|bool
    {
        if (isset($payload['location'])) {
            $savedFile = app(FileService::class)->saveFile($payload['location'], 'files/');
            $_file = $payload['location'];

            $filePayload = $payload;
            $filePayload['name'] = $savedFile['original'];
            $filePayload['location'] = $savedFile['name'];
            $filePayload['mime'] = $_file->getClientMimeType();
            $filePayload['size'] = $_file->getClientSize();
        } else {
            $filePayload = $payload;
        }

        $filePayload['is_published'] = (isset($payload['is_published'])) ? (bool) $payload['is_published'] : 0;

        return $files->update($filePayload);
    }

    /**
     * Files output for API calls.
     */
    public function apiPrepared(): array
    {
        $files = File::orderBy('created_at', 'desc')->where('is_published', 1)->get();
        $allFiles = [];

        foreach ($files as $file) {
            array_push($allFiles, [
                'file_identifier' => CryptoService::url_encode($file->name).'/'.CryptoService::url_encode($file->location),
                'file_name' => $file->name,
                'file_date' => $file->created_at->format('F jS, Y'),
            ]);
        }

        return $allFiles;
    }
}
