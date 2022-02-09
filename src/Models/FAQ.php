<?php

namespace Grafite\Cms\Models;

use Grafite\Cms\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FAQ extends CmsModel
{
    use Translatable;
    use HasFactory;

    public $table = 'faqs';

    public $primaryKey = 'id';

    public static $rules = [
        'question' => 'required',
        'answer' => 'required',
    ];

    protected $appends = [
        'translations',
    ];

    protected $fillable = [
        'question',
        'answer',
        'is_published',
        'published_at',
    ];

    protected $dates = [
        'published_at',
    ];

    public function __construct(array $attributes = [])
    {
        $keys = array_keys(request()->except('_method', '_token'));
        $this->fillable(array_values(array_unique(array_merge($this->fillable, $keys))));
        parent::__construct($attributes);
    }
}
