<?php

namespace Grafite\Cms\Models;

use Grafite\Cms\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property string $content
 */
class Widget extends CmsModel
{
    use Translatable;
    use HasFactory;

    public $table = 'widgets';

    public $primaryKey = 'id';

    public static $rules = [
        'name' => 'required',
        'slug' => 'required',
    ];

    protected $appends = [
        'translations',
    ];

    protected $fillable = [
        'name',
        'slug',
        'content',
    ];

    public function __construct(array $attributes = [])
    {
        $keys = array_keys(request()->except('_method', '_token'));
        $this->fillable(array_values(array_unique(array_merge($this->fillable, $keys))));
        parent::__construct($attributes);
    }
}
