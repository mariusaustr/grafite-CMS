<?php

namespace Grafite\Cms\Models;

use Grafite\Cms\Services\Normalizer;
use Grafite\Cms\Traits\Translatable;
use Illuminate\Support\Carbon;

/**
 * @property string $details
 */
class Promotion extends CmsModel
{
    use Translatable;

    public $table = 'promotions';

    public $primaryKey = 'id';

    public static $rules = [
        'slug' => 'required',
    ];

    protected $appends = [
        'translations',
        'is_published',
    ];

    protected $fillable = [
        'slug',
        'details',
        'published_at',
        'finished_at',
    ];

    protected $dates = [
        'published_at' => 'Y-m-d H:i',
        'finished_at' => 'Y-m-d H:i',
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

    public function getPublishedAtAttribute($value): Carbon
    {
        return Carbon::parse($value)->timezone(config('app.timezone'));
    }

    public function getFinishedAtAttribute($value): Carbon
    {
        return Carbon::parse($value)->timezone(config('app.timezone'));
    }

    public function getIsPublishedAttribute(): bool
    {
        $published = Carbon::parse($this->published_at)->timezone(config('app.timezone'));
        $finished = Carbon::parse($this->finished_at)->timezone(config('app.timezone'));

        return Carbon::now(config('app.timezone'))->between($published, $finished);
    }
}
