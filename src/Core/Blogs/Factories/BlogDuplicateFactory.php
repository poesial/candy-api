<?php

namespace GetCandy\Api\Core\Blogs\Factories;

use DB;
use GetCandy\Api\Core\Blogs\Interfaces\BlogInterface;
use GetCandy\Api\Core\Blogs\Models\Blog;
use GetCandy\Api\Core\Search\Events\IndexableSavedEvent;
use Illuminate\Support\Collection;
use League\Flysystem\FileNotFoundException;
use Storage;

class BlogDuplicateFactory implements BlogInterface
{
    /**
     * @var \GetCandy\Api\Core\Blogs\Models\Blog
     */
    protected $blog;

    public function init(Blog $blog)
    {
        $this->blog = $blog;

        return $this;
    }

    /**
     * Duplicate a blog.
     *
     * @param  \Illuminate\Support\Collection  $data
     * @return \GetCandy\Api\Core\Blogs\Models\Blog
     */
    public function duplicate(Collection $data)
    {
        $blog = DB::transaction(function () use ($data) {
            $newBlog = $this->blog->replicate();

            $currentVariants = $newBlog->variants;
            $currentRoutes = $newBlog->routes;
            $newBlog->save();

            // Zero them out so we can add them back in.
            $newBlog->variants()->delete();
            $newBlog->routes()->delete();

            $newBlog->refresh();

            $this->processVariants($newBlog, $currentVariants, $data);
            $this->processRoutes($newBlog, $currentRoutes, $data);
            $this->processAssets($newBlog);
            $this->processCategories($newBlog);
            $this->processChannels($newBlog);
            $this->processCustomerGroups($newBlog);

            return $newBlog->load([
                'variants',
                'channels',
                'routes',
                'customerGroups',
            ]);
        });

        event(new IndexableSavedEvent($blog));

        return $blog;
    }

    /**
     * Process the assets for a duplicated blog.
     *
     * @param  \GetCandy\Api\Core\Blogs\Models\Blog  $newBlog
     * @return void
     */
    protected function processAssets($newBlog)
    {
        $currentAssets = $this->blog->assets;
        $assets = collect();

        $currentAssets->each(function ($a) use ($newBlog) {
            $newAsset = $a->replicate();

            // Move the file to it's new location
            $newAsset->assetable_id = $newBlog->id;

            $newFilename = uniqid().'_'.$newAsset->filename;

            try {
                Storage::disk($newAsset->source->disk)->copy(
                    "{$newAsset->location}/{$newAsset->filename}",
                    "{$newAsset->location}/{$newFilename}"
                );
                $newAsset->filename = $newFilename;
            } catch (FileNotFoundException $e) {
                $newAsset->save();

                return;
            }

            $newAsset->save();

            foreach ($a->transforms as $transform) {
                $newTransform = $transform->replicate();
                $newTransform->asset_id = $newAsset->id;
                $newFilename = uniqid().'_'.$newTransform->filename;

                try {
                    Storage::disk($newAsset->source->disk)->copy(
                        "{$newTransform->location}/{$newTransform->filename}",
                        "{$newTransform->location}/{$newFilename}"
                    );
                } catch (FileNotFoundException $e) {
                    continue;
                }

                $newTransform->filename = $newFilename;
                $newTransform->save();
            }
        });
    }

    /**
     * Process the duplicated blog categories.
     *
     * @param  \GetCandy\Api\Core\Blogs\Models\Blog  $newBlog
     * @return void
     */
    protected function processCategories($newBlog)
    {
        $currentCategories = $this->blog->categories;
        foreach ($currentCategories as $category) {
            $newBlog->categories()->attach($category);
        }
    }

    /**
     * Process the customer groups for the duplicated blog.
     *
     * @param  \GetCandy\Api\Core\Blogs\Models\Blog  $newBlog
     * @return void
     */
    protected function processCustomerGroups($newBlog)
    {
        // Need to associate all the channels the current blog has
        // but make sure they are not active to start with.
        $groups = $this->blog->customerGroups;

        $newGroups = collect();

        foreach ($groups as $group) {
            $newGroups->put($group->id, [
                'visible' => $group->pivot->visible,
                'purchasable' => $group->pivot->purchasable,
            ]);
        }
        $newBlog->customerGroups()->sync($newGroups->toArray());
    }

    /**
     * Process channels for a duplicated blog.
     *
     * @param  \GetCandy\Api\Core\Blogs\Models\Blog  $newBlog
     * @return void
     */
    protected function processChannels($newBlog)
    {
        // Need to associate all the channels the current blog has
        // but make sure they are not active to start with.
        $channels = $this->blog->channels;

        $newChannels = collect();

        foreach ($channels as $channel) {
            $newChannels->put($channel->id, [
                'published_at' => null,
            ]);
        }

        $newBlog->channels()->sync($newChannels->toArray());
    }

    /**
     * Process the variants for duplication.
     *
     * @param  \GetCandy\Api\Core\Blogs\Models\Blog  $newBlog
     * @param  \Illuminate\Support\Collection  $currentVariants
     * @param  \Illuminate\Support\Collection  $data
     * @return void
     */
    protected function processVariants($newBlog, $currentVariants, $data)
    {
        foreach ($data['skus'] as $sku) {
            // Get the existing variant with this SKU.
            $variant = $this->getVariantToCopy($currentVariants, $sku['current']);
            if (! $variant) {
                continue;
            }
            $variant->blog_id = $newBlog->id;
            $variant->sku = $sku['new'];
            $variant->save();
        }
    }

    /**
     * Process the routes for duplication.
     *
     * @param  \GetCandy\Api\Core\Blogs\Models\Blog  $newBlog
     * @param  \Illuminate\Support\Collection  $currentRoutes
     * @param  \Illuminate\Support\Collection  $data
     * @return void
     */
    protected function processRoutes($newBlog, $currentRoutes, $data)
    {
        foreach ($data['routes'] as $route) {
            $routeToCopy = $currentRoutes->first(function ($r) use ($route) {
                return $r->slug == $route['current'];
            });

            if (! $route) {
                continue;
            }
            $newRoute = $routeToCopy->replicate();
            $newRoute->slug = $route['new'];
            $newRoute->element_id = $newBlog->id;
            $newRoute->save();
        }
    }

    /**
     * Get the variant to copy.
     *
     * @param  \Illuminate\Support\Collection  $variants
     * @param  string  $sku
     * @return \GetCandy\Api\Core\Blogs\Models\BlogVariant
     */
    protected function getVariantToCopy($variants, $sku)
    {
        $variant = $variants->first(function ($v) use ($sku) {
            return $v->sku == $sku;
        });
        if (! $variant) {
            return;
        }

        return $variant->load([
            'tiers',
            'customerPricing',
        ])->replicate();
    }
}
