<?php

namespace GetCandy\Api\Core\GoodFor\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Traits\Assetable;
use GetCandy\Api\Core\Traits\Indexable;
use NeonDigital\Versioning\Versionable;
use Spatie\Activitylog\Traits\LogsActivity;

class GoodForProduct extends BaseModel
{
    use Assetable,
        Indexable,
        LogsActivity,
        Versionable;
    /**
     * @var string
     */
    protected $table = 'product_good_for_icons';

    protected $fillable = ['product_id', 'good_for_id'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
}
