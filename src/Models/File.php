<?php

namespace Grafite\Cms\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property string $location
 * @property string $name
 * @property Carbon $created_at
 */
class File extends CmsModel
{
    use HasFactory;

    public $table = 'files';

    public $primaryKey = 'id';

    public static $rules = [
        'location' => 'required',
    ];

    protected $fillable = [
        'name',
        'location',
        'user',
        'tags',
        'details',
        'mime',
        'size',
        'is_published',
        'order',
    ];

    public function __construct(array $attributes = [])
    {
        $keys = array_keys(request()->except('_method', '_token'));
        $this->fillable(array_values(array_unique(array_merge($this->fillable, $keys))));
        parent::__construct($attributes);
    }
}
