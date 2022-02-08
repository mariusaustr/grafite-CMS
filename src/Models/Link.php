<?php

namespace Grafite\Cms\Models;

use Grafite\Cms\Traits\Translatable;

class Link extends CmsModel
{
    use Translatable;

    public $table = 'links';

    public $primaryKey = 'id';

    protected $guarded = [];

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

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}
