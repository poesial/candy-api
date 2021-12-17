<?php

namespace GetCandy\Api\Providers;

use Drafting;
use GetCandy\Api\Core\Blogs\Drafting\BlogDrafter;
use GetCandy\Api\Core\Blogs\Factories\BlogFactory;
use GetCandy\Api\Core\Blogs\Factories\BlogVariantFactory;
use GetCandy\Api\Core\Blogs\Interfaces\BlogInterface;
use GetCandy\Api\Core\Blogs\Interfaces\BlogVariantInterface;
use GetCandy\Api\Core\Blogs\Models\Blog;
use GetCandy\Api\Core\Blogs\Observers\BlogObserver;
use GetCandy\Api\Core\Blogs\Services\BlogAssociationService;
use GetCandy\Api\Core\Blogs\Services\BlogCategoryService;
use GetCandy\Api\Core\Blogs\Services\BlogCollectionService;
use GetCandy\Api\Core\Blogs\Services\BlogProductService;
use GetCandy\Api\Core\Blogs\Services\BlogService;
use GetCandy\Api\Core\Blogs\Services\BlogVariantService;
use GetCandy\Api\Core\Blogs\Versioning\BlogVariantVersioner;
use GetCandy\Api\Core\Blogs\Versioning\BlogVersioner;
use Illuminate\Support\ServiceProvider;
use Versioning;

class BlogServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Drafting::extend('blogs', function ($app) {
            return $app->make(BlogDrafter::class);
        });

        Versioning::extend('blogs', function ($app) {
            return $app->make(BlogVersioner::class);
        });
        Versioning::extend('blog_variants', function ($app) {
            return $app->make(BlogVariantVersioner::class);
        });

        Blog::observe(BlogObserver::class);
    }

    public function register()
    {
        $this->app->bind(BlogVariantInterface::class, function ($app) {
            return $app->make(BlogVariantFactory::class);
        });

        $this->app->bind(BlogInterface::class, function ($app) {
            return $app->make(BlogFactory::class);
        });

        $this->app->bind('getcandy.blog_variants', function ($app) {
            return $app->make(BlogVariantService::class);
        });

        $this->app->bind('getcandy.blogs', function ($app) {
            return $app->make(BlogService::class);
        });

        $this->app->bind('getcandy.blog_associations', function ($app) {
            return $app->make(BlogAssociationService::class);
        });

        $this->app->bind('getcandy.blog_collections', function ($app) {
            return $app->make(BlogCollectionService::class);
        });

        $this->app->bind('getcandy.blog_categories', function ($app) {
            return $app->make(BlogCategoryService::class);
        });

        $this->app->bind('getcandy.blog_products', function ($app) {
            return $app->make(BlogProductService::class);
        });
    }
}
