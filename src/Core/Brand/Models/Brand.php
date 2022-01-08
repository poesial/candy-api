<?php

namespace GetCandy\Api\Core\Brand\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Traits\Assetable;
use GetCandy\Api\Core\Traits\Indexable;
use NeonDigital\Versioning\Versionable;
use Spatie\Activitylog\Traits\LogsActivity;

class Brand extends BaseModel
{
    use Assetable,
        Indexable,
        LogsActivity,
        Versionable;

    /**
     * @var string
     */
    protected $settings = 'brand';

    /**
     * @var string
     */
    protected $table = 'brand';

    protected $fillable = ['logo'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
}
