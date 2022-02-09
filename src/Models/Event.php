<?php

namespace Grafite\Cms\Models;

use Grafite\Cms\Services\Normalizer;
use Grafite\Cms\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;

class Event extends CmsModel
{
    use Translatable;
    use HasFactory;

    public $table = 'events';

    public $primaryKey = 'id';

    public static $rules = [
        'title' => 'required',
    ];

    protected $appends = [
        'translations',
    ];

    protected $fillable = [
        'start_date',
        'end_date',
        'title',
        'details',
        'seo_description',
        'seo_keywords',
        'is_published',
        'template',
        'published_at',
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

    public function getDetailsAttribute($value): string
    {
        return new Normalizer($value);
    }

    public function history(): Collection
    {
        return Archive::where('entity_type', get_class($this))->where('entity_id', $this->id)->get();
    }
}
