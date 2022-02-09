<?php

namespace Grafite\Cms\Models;

use Closure;
use Exception;
use Grafite\Cms\Services\AssetService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as InterventionImage;

/**
 * @property string $location
 * @property string $tags
 */
class Image extends CmsModel
{
    use HasFactory;

    public $table = 'images';

    public $primaryKey = 'id';

    protected $appends = [
        'url',
        'js_url',
    ];

    public static $rules = [
        'location' => 'mimes:jpeg,jpg,bmp,png,gif',
    ];

    protected $fillable = [
        'location',
        'name',
        'original_name',
        'storage_location',
        'alt_tag',
        'title_tag',
        'is_published',
        'tags',
        'entity_id',
        'entity_type',
    ];

    public function __construct(array $attributes = [])
    {
        $keys = array_keys(request()->except('_method', '_token'));
        $this->fillable(array_values(array_unique(array_merge($this->fillable, $keys))));
        parent::__construct($attributes);
    }

    /**
     * Get the images url location.
     */
    public function getUrlAttribute(): string
    {
        if ($this->isLocalFile()) {
            return url(str_replace('public/', 'storage/', $this->location));
        } elseif ($this->fileExists()) {
            return $this->getS3Image();
        }

        return $this->lostImage();
    }

    /**
     * Get an S3 image.
     */
    public function getS3Image(): string
    {
        $url = Storage::disk(Config::get('cms.storage-location', 'local'))->url($this->location);

        if (! is_null(config('cms.cloudfront'))) {
            $url = str_replace(config('filesystems.disks.s3.bucket').'.s3.'.config('filesystems.disks.s3.region').'.amazonaws.com', config('cms.cloudfront'), $url);
        }

        return $url;
    }

    /**
     * Get the images url location.
     */
    public function getJsUrlAttribute(): string
    {
        return $this->url;
    }

    /**
     * Set Image Caches.
     */
    public function setCaches(): bool
    {
        if ($this->url && $this->js_url) {
            return true;
        }

        return false;
    }

    /**
     * Simple caching tool.
     */
    public function remember(string $attribute, Closure $closure): mixed
    {
        $key = $attribute.'_'.$this->location;

        if (! Cache::has($key)) {
            $result = $closure();
            Cache::forever($key, $result);
        }

        return Cache::get($key);
    }

    /**
     * Forget the current Image caches.
     */
    public function forgetCache(): void
    {
        foreach (['url', 'js_url'] as $attribute) {
            $key = $attribute.'_'.$this->location;
            Cache::forget($key);
        }
    }

    /**
     * Check the location of the file.
     */
    private function isLocalFile(): bool
    {
        try {
            if (file_exists(storage_path('app/'.$this->location))) {
                return true;
            }
        } catch (Exception $e) {
            Log::debug('Could not find the image');

            return false;
        }

        return false;
    }

    /**
     * Check if file exists.
     */
    public function fileExists(): bool
    {
        return Storage::disk(Config::get('cms.storage-location', 'local'))->exists($this->location);
    }

    /**
     * Staged image if none are found.
     */
    public function lostImage(): string
    {
        $imagePath = app(AssetService::class)->generateImage('File Not Found');

        $image = InterventionImage::make($imagePath)->resize(config('cms.preview-image-size', 800), null, function ($constraint) {
            $constraint->aspectRatio();
        });

        return (string) $image->encode('data-url');
    }
}
