<?php

namespace Grafite\Cms\Models;

use Grafite\Cms\Traits\Translatable;
use Grafite\Database\Factories\LinkFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Link extends CmsModel
{
    use Translatable;
    use HasFactory;

    protected static function newFactory()
    {
        return LinkFactory::new();
    }

    public $table = 'links';

    public $primaryKey = 'id';

    public static $rules = [
        'name' => 'required',
    ];

    protected $fillable = [
        'name',
        'external',
        'page_id',
        'menu_id',
        'external_url',
    ];

    public $with = [
        'page',
    ];

    public function __construct(array $attributes = [])
    {
        $keys = array_keys(request()->except('_method', '_token'));
        $this->fillable(array_values(array_unique(array_merge($this->fillable, $keys))));
        parent::__construct($attributes);
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }
}
