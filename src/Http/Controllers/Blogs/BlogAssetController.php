<?php

namespace GetCandy\Api\Http\Controllers\Blogs;

use GetCandy;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Assets\UploadRequest;
use GetCandy\Api\Http\Resources\Assets\AssetCollection;
use Illuminate\Http\Request;

class BlogAssetController extends BaseController
{
    /**
     * Gets all assets for a blog.
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function index($id, Request $request)
    {
        $blog = GetCandy::blogs()->getByHashedId($id);
        $assets = GetCandy::assets()->getAssets($blog, $request->all());

        return new AssetCollection($assets);
    }

    public function attach($blogId, Request $request)
    {
        $blog = GetCandy::blogs()->getByHashedId($blogId, true);
        $asset = GetCandy::assets()->getByHashedId($request->asset_id);

        if (! $asset || ! $blog) {
            return $this->errorNotFound();
        }

        $blog->assets()->attach($asset, [
            'primary' => ! $blog->assets()->images()->exists(),
            'assetable_type' => get_class($blog),
            'position' => $request->position ?: $blog->assets()->count() + 1,
        ]);

        return $this->respondWithNoContent();
    }

    /**
     * Uploads an asset for a blog.
     *
     * @param  int  $id
     * @param  \GetCandy\Api\Http\Requests\Assets\UploadRequest  $request
     * @return void
     */
    public function upload($id, UploadRequest $request)
    {
    }
}
