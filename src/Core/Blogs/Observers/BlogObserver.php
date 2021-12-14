<?php

namespace GetCandy\Api\Core\Blogs\Observers;

use GetCandy\Api\Core\Assets\Services\AssetService;
use GetCandy\Api\Core\Blogs\Models\Blog;
use GetCandy\Api\Core\Search\SearchManager;

class BlogObserver
{
    /**
     * @var \GetCandy\Api\Core\Assets\Services\AssetService
     */
    protected $assets;

    protected $search;

    public function __construct(AssetService $assets, SearchManager $search)
    {
        $this->assets = $assets;
        $this->search = $search;
    }

    /**
     * Handle the User "deleted" event.
     *
     * @param  \GetCandy\Api\Core\Blogs\Models\Blog  $blog
     * @return void
     */
    public function deleted(Blog $blog)
    {
        if ($blog->isForceDeleting()) {
            $blog->assets()->wherePivot('assetable_type', '=', get_class($blog))->detach();
            $blog->categories()->detach();
            $blog->routes()->forceDelete();
            $driver = $this->search->with(config('getcandy.search.driver'));
            $driver->delete($blog);
        }
    }
}
