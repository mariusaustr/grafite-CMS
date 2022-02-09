<?php

namespace Grafite\Cms\Models;

use Carbon\Carbon;

/**
 * @property Carbon $created_at
 */
class Analytics extends CmsModel
{
    public $table = 'analytics';

    public $primaryKey = 'id';

    public $fillable = [
        'token',
        'data',
    ];
}
