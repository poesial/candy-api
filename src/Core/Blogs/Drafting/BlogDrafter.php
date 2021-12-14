<?php

namespace GetCandy\Api\Core\Blogs\Drafting;

use DB;
use GetCandy\Api\Core\Drafting\Actions\DraftAssets;
use GetCandy\Api\Core\Drafting\Actions\DraftCategories;
use GetCandy\Api\Core\Drafting\Actions\DraftBlogAssociations;
use GetCandy\Api\Core\Drafting\Actions\DraftRoutes;
use GetCandy\Api\Core\Drafting\Actions\PublishAssets;
use GetCandy\Api\Core\Drafting\Actions\PublishChannels;
use GetCandy\Api\Core\Drafting\Actions\PublishCustomerGroups;
use GetCandy\Api\Core\Drafting\Actions\PublishBlogAssociations;
use GetCandy\Api\Core\Drafting\Actions\PublishRoutes;
use GetCandy\Api\Core\Drafting\BaseDrafter;
use GetCandy\Api\Core\Search\Events\IndexableSavedEvent;
use Illuminate\Database\Eloquent\Model;
use NeonDigital\Drafting\Interfaces\DrafterInterface;
use Versioning;

class BlogDrafter extends BaseDrafter implements DrafterInterface
{
    public function create(Model $parent)
    {
        return DB::transaction(function () use ($parent) {
            $parent = $parent->load([
                'categories',
                'routes.publishedParent',
                'routes.draft',
            ]);

            $draft = $parent->replicate();
            $draft->drafted_at = now();
            $draft->draft_parent_id = $parent->id;
            $draft->save();

            $this->callActions(array_merge([
                DraftRoutes::class,
                DraftBlogAssociations::class,
                DraftAssets::class,
                DraftCategories::class,
            ], $this->extendedDraftActions), [
                'draft' => $draft,
                'parent' => $parent,
            ]);

            // Not sure if this is something we need to worry about now as drafting has changed.
            // Potentially deprecated in a later release...
            $parent->attributes->each(function ($model) use ($draft) {
                $draft->attributes()->attach($model);
            });

            return $draft->refresh()->load([
                'categories',
                'routes.publishedParent',
                'routes.draft',
            ]);
        });
    }

    /**
     * Duplicate a blog.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $blog
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function firstOrCreate(Model $parent)
    {
        return $parent->draft ?: $this->create($parent);
    }

    public function publish(Model $draft)
    {
        return DB::transaction(function () use ($draft) {
            // Publish this blog and remove the parent.
            $parent = $draft->publishedParent->load(
                'categories',
                'routes',
            );

            // Get any current versions and assign them to this new blog.

            // Create a version of the parent before we publish these changes
            Versioning::with('blogs')->create($parent);

            // Publish any attributes etc
            $parent->attribute_data = $draft->attribute_data;
            $parent->option_data = $draft->option_data;
            $parent->blog_family_id = $draft->blog_family_id;
            $parent->layout_id = $draft->layout_id;

            $parent->save();

            $this->callActions(array_merge([
                PublishRoutes::class,
                PublishAssets::class,
                PublishBlogAssociations::class,
            ], $this->extendedPublishActions), [
                'draft' => $draft,
                'parent' => $parent,
            ]);

            // Categories
            $existingCategories = $parent->categories;

            // Sync blog categories to the parent.
            $parent->categories()->sync(
                $draft->categories->pluck('id')
            );

            // Delete the draft we had.
            $draft->forceDelete();

            IndexableSavedEvent::dispatch($parent);

            return $parent;
        });
    }
}
