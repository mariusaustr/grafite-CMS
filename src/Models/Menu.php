<?php

namespace Grafite\Cms\Models;

use Grafite\Database\Factories\MenuFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Menu extends CmsModel
{
    use HasFactory;

    protected static function newFactory()
    {
        return MenuFactory::new();
    }

    public $table = 'menus';

    public $primaryKey = 'id';

    public static $rules = [
        'name' => 'required',
        'slug' => 'required',
    ];

    protected $fillable = [
        'name',
        'slug',
        'order',
    ];

    public function __construct(array $attributes = [])
    {
        $keys = array_keys(request()->except('_method', '_token'));
        $this->fillable(array_values(array_unique(array_merge($this->fillable, $keys))));
        parent::__construct($attributes);
    }

    public function getOrderAttribute($value): string
    {
        if (is_null($value)) {
            return '[]';
        }

        return $value;
    }
}
