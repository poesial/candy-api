<?php

namespace GetCandy\Api\Core\Blogs\Versioning;

use Auth;
use Drafting;
use GetCandy\Api\Core\Assets\Models\Asset;
use GetCandy\Api\Core\Channels\Models\Channel;
use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use GetCandy\Api\Core\Blogs\Actions\Versioning\VersionBlogAssociations;
use GetCandy\Api\Core\Blogs\Actions\Versioning\VersionBlogVariants;
use GetCandy\Api\Core\Blogs\Models\Blog;
use GetCandy\Api\Core\Blogs\Models\BlogVariant;
use GetCandy\Api\Core\Routes\Models\Route;
use GetCandy\Api\Core\Versioning\Actions\CreateVersion;
use GetCandy\Api\Core\Versioning\Actions\RestoreAssets;
use GetCandy\Api\Core\Versioning\Actions\RestoreChannels;
use GetCandy\Api\Core\Versioning\Actions\RestoreCustomerGroups;
use GetCandy\Api\Core\Versioning\Actions\RestoreBlogVariants;
use GetCandy\Api\Core\Versioning\Actions\RestoreRoutes;
use GetCandy\Api\Core\Versioning\Actions\VersionAssets;
use GetCandy\Api\Core\Versioning\Actions\VersionCategories;
use GetCandy\Api\Core\Versioning\Actions\VersionChannels;
use GetCandy\Api\Core\Versioning\Actions\VersionCollections;
use GetCandy\Api\Core\Versioning\Actions\VersionCustomerGroups;
use GetCandy\Api\Core\Versioning\Actions\VersionRoutes;
use GetCandy\Api\Core\Versioning\BaseVersioner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class BlogVersioner extends BaseVersioner
{
    public function create(Model $model, Model $originator = null)
    {
        $userId = Auth::user() ? Auth::user()->id : null;

        $version = CreateVersion::run([
            'originator' => $originator,
            'model' => $model,
        ]);

        $this->callActions([
            VersionCategories::class,
            VersionBlogAssociations::class,
            VersionRoutes::class,
            VersionAssets::class,
        ], [
            'model' => $model,
            'version' => $version,
        ]);

        return $version;
    }

    public function restore($version)
    {
        $blog = $version->versionable;

        // Do we already have a draft??
        $draft = $blog->draft;

        // This is the new draft so...remove it.
        if ($draft) {
            $draft->forceDelete();
        }

        // Create a new draft for the blog
        $draft = Drafting::with('blogs')->firstOrCreate($blog->refresh());
        $draft->save();

        $attributes = collect($version->model_data)->except(['id', 'drafted_at', 'draft_parent_id']);
        $draft->update($attributes->toArray());

        // Group our relations by versionable type so we can send them all
        // through in bulk to a single action. Makes it easier so we don't have
        // to worry about continuously overriding ourselves.
        $groupedRelations = $version->relations->groupBy('versionable_type')
            ->each(function ($versions, $type) use ($draft) {
                $action = null;
                switch ($type) {
                    case Channel::class:
                        $action = RestoreChannels::class;
                        break;
                    case BlogVariant::class:
                        $action = RestoreBlogVariants::class;
                        break;
                    case CustomerGroup::class:
                        $action = RestoreCustomerGroups::class;
                        break;
                    case Route::class:
                        $action = RestoreRoutes::class;
                        break;
                    case Asset::class:
                        $action = RestoreAssets::class;
                        break;
                }
                if (! $action) {
                    Log::error("Unable to restore for {$type}");

                    return;
                }
                (new $action)->run([
                    'versions' => $versions,
                    'draft' => $draft,
                ]);
            });

        return $draft;
    }
}
