<?php

namespace GetCandy\Api\Core\Contents\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Traits\Assetable;
use GetCandy\Api\Core\Traits\Indexable;
use NeonDigital\Versioning\Versionable;
use Spatie\Activitylog\Traits\LogsActivity;

class Content extends BaseModel
{
    use Assetable,
        Indexable,
        LogsActivity,
        Versionable;

    /**
     * @var string
     */
    protected $settings = 'contents';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
}
