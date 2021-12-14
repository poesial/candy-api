<?php

namespace GetCandy\Api\Core\Blogs\Versioning;

use GetCandy\Api\Core\Blogs\Models\BlogVariant;
use Illuminate\Database\Eloquent\Model;
use NeonDigital\Versioning\Interfaces\VersionerInterface;
use NeonDigital\Versioning\Versioners\AbstractVersioner;

class BlogVariantVersioner extends AbstractVersioner implements VersionerInterface
{
    public function create(Model $variant, $relationId = null)
    {
        $version = $this->createFromObject($variant, $relationId);
    }

    public function restore($version, $parent = null)
    {
        $data = $version->model_data;
        $data['options'] = json_decode($data['options']);
        unset($data['id']);
        $variant = new BlogVariant;
        $variant->forceFill($data);
        $variant->asset_id = null;

        if ($parent) {
            $variant->blog_id = $parent->id;
        }
        $variant->drafted_at = now();
        $variant->save();
        // foreach ($version->relations as $relation) {
        //     dd($relation);
        // }
        return $variant;
    }
}
