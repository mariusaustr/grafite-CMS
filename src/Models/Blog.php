<?php

namespace Grafite\Cms\Models;

use Grafite\Cms\Services\Normalizer;
use Grafite\Cms\Traits\Translatable;
use Grafite\Database\Factories\BlogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;

/**
 * @property string $hero_image
 */
class Blog extends CmsModel
{
    use Translatable;
    use HasFactory;

    protected static function newFactory()
    {
        return BlogFactory::new();
    }

    public $table = 'blogs';

    public $primaryKey = 'id';

    public static $rules = [
        'title' => 'required|string',
        'url' => 'required|string',
    ];

    protected $appends = [
        'translations',
    ];

    protected $fillable = [
        'title',
        'entry',
        'tags',
        'is_published',
        'seo_description',
        'seo_keywords',
        'url',
        'template',
        'published_at',
        'hero_image',
        'blocks',
    ];

    protected $dates = [
        'published_at' => 'Y-m-d H:i',
    ];

    public function __construct(array $attributes = [])
    {
        $keys = array_keys(request()->except('_method', '_token'));
        $this->fillable(array_values(array_unique(array_merge($this->fillable, $keys))));
        parent::__construct($attributes);
    }

    public function getEntryAttribute($value): string
    {
        return new Normalizer($value);
    }

    public function getHeroImageUrlAttribute(): string
    {
        return url(str_replace('public/', 'storage/', $this->hero_image));
    }

    public function history(): Collection
    {
        return Archive::where('entity_type', get_class($this))->where('entity_id', $this->id)->get();
    }

    public function getBlocksAttribute($value): array
    {
        $blocks = json_decode($value, true);

        if (is_null($blocks)) {
            $blocks = [];
        }

        return (array) $blocks;
    }
}
