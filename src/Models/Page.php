<?php

namespace Grafite\Cms\Models;

use Carbon\Carbon;
use Grafite\Cms\Services\Normalizer;
use Grafite\Cms\Traits\Translatable;
use Grafite\Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;

/**
 * @property string $hero_image
 * @property bool $is_published
 * @property Carbon $published_at
 * @property string $url
 * @property string $title
 */
class Page extends CmsModel
{
    use Translatable;
    use HasFactory;

    protected static function newFactory()
    {
        return PageFactory::new();
    }

    public $table = 'pages';

    public $primaryKey = 'id';

    public static $rules = [
        'title' => 'required',
        'url' => 'required',
    ];

    protected $appends = [
        'translations',
    ];

    protected $fillable = [
        'title',
        'url',
        'entry',
        'seo_description',
        'seo_keywords',
        'is_published',
        'template',
        'published_at',
        'blocks',
        'hero_image',
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

    public function getHeroImageUrlAttribute($value): string
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

        return $blocks;
    }
}
