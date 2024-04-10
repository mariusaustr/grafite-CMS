<?php

namespace Grafite\Cms\Models;

use Grafite\Cms\Traits\Translatable;
use Grafite\Database\Factories\FAQFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FAQ extends CmsModel
{
    use Translatable;
    use HasFactory;

    protected static function newFactory()
    {
        return FAQFactory::new();
    }

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

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        $keys = array_keys(request()->except('_method', '_token'));
        $this->fillable(array_values(array_unique(array_merge($this->fillable, $keys))));
        parent::__construct($attributes);
    }
}
