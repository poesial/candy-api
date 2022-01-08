<?php

namespace GetCandy\Api\Core\GoodFor\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Traits\Assetable;
use GetCandy\Api\Core\Traits\Indexable;
use NeonDigital\Versioning\Versionable;
use Spatie\Activitylog\Traits\LogsActivity;

class GoodFor extends BaseModel
{
    use Assetable,
        Indexable,
        LogsActivity,
        Versionable;

    /**
     * @var string
     */
    protected $settings = 'good_for';

    /**
     * @var string
     */
    protected $table = 'good_for_icons';

    protected $fillable = ['name', 'icon'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
}
