<?php

namespace Grafite\Cms\Models;

/**
 * @property string $entity_type
 * @property int $entity_id
 * @property string $entity_data
 */
class Archive extends CmsModel
{
    public $table = 'archives';

    public $primaryKey = 'id';

    public $fillable = [
        'token',
        'entity_id',
        'entity_type',
        'entity_data',
    ];
}
