<?php

namespace GetCandy\Api\Core\Blogs\Services;

use GetCandy;
use GetCandy\Api\Core\Customers\Actions\FetchCustomerGroups;
use GetCandy\Api\Core\Languages\Actions\FetchDefaultLanguage;
use GetCandy\Api\Core\Blogs\Actions\FetchBlogFamily;
use GetCandy\Api\Core\Blogs\Events\BlogCreatedEvent;
use GetCandy\Api\Core\Blogs\Interfaces\BlogInterface;
use GetCandy\Api\Core\Blogs\Models\Blog;
use GetCandy\Api\Core\Blogs\Models\BlogRecommendation;
use GetCandy\Api\Core\Routes\Actions\CreateRoute;
use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Api\Core\Scopes\CustomerGroupScope;
use Illuminate\Support\Facades\Log;

class BlogService extends BaseService
{
    /**
     * @var \GetCandy\Api\Core\Blogs\Models\Blog
     */
    protected $model;

    /**
     * The blog factory instance.
     *
     * @var \GetCandy\Api\Core\Blogs\Interfaces\BlogInterface
     */
    protected $factory;

    public function __construct(BlogInterface $factory)
    {
        $this->model = new Blog();
        $this->factory = $factory;
    }

    /**
     * Returns model by a given hashed id.
     *
     * @param  string  $id
     * @param  bool  $withDrafted
     * @return \GetCandy\Api\Core\Blogs\Models\Blog
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getByHashedId($id, $withDrafted = false)
    {
        $id = $this->model->decodeId($id);
        $blog = $this->model;

        if ($withDrafted) {
            $blog = $blog->withDrafted();
        }

        return $this->factory->init($blog->findOrFail($id))->get();
    }

    public function findById($id, array $includes = [], $draft = false)
    {
        $query = Blog::with(array_merge($includes, ['draft']));

        if ($draft) {
            $query->withDrafted()->withoutGlobalScopes();
        }

        $blog = $query->find($id);

        return $blog;
    }

    /**
     * Updates a resource from the given data.
     *
     * @param  string  $hashedId
     * @param  array  $data
     * @return \GetCandy\Api\Core\Blogs\Models\Blog
     *
     * @throws \Exception
     * @throws \GetCandy\Exceptions\InvalidLanguageException
     */
    public function update($hashedId, array $data)
    {
        $blog = $this->getByHashedId($hashedId, true);

        if (! $blog) {
            abort(404);
        }

        if (! empty($data['attribute_data'])) {
            $blog->attribute_data = $data['attribute_data'];
        }

        if (! empty($data['family_id'])) {
            $family = FetchBlogFamily::run([
                'encoded_id' => $data['family_id'],
            ]);
            $family->blogs()->save($blog);
        }

        if (! empty($data['layout_id'])) {
            $layout = GetCandy::layouts()->getByHashedId($data['layout_id']);
            $blog->layout_id = $layout->id;
        }

        $blog->save();

        // event(new AttributableSavedEvent($blog));

        // event(new IndexableSavedEvent($blog));

        return $blog;
    }

    /**
     * Update a blogs layout.
     *
     * @param  string  $blogId
     * @param  string  $layoutId
     * @return \GetCandy\Api\Core\Blogs\Models\Blog
     */
    public function updateLayout($blogId, $layoutId)
    {
        $layout = GetCandy::layouts()->getByHashedId($layoutId);
        $blog = $this->getByHashedId($blogId);

        $blog->layout->associate($layout);
        $blog->save();

        return $blog;
    }

    /**
     * Creates a resource from the given data.
     *
     * @param  array  $data
     * @return \GetCandy\Api\Core\Blogs\Models\Blog
     *
     * @throws \GetCandy\Exceptions\InvalidLanguageException
     */
    public function create(array $data)
    {
        $blog = new Blog;
        $blog->attribute_data = $data;

        if (! empty($data['historical_id'])) {
            $blog->id = $data['historical_id'];
        }

        if (! empty($data['created_at'])) {
            $blog->created_at = $data['created_at'];
        }

        $blog->option_data = [];

        if (! empty($data['option_data'])) {
            $blog->option_data = $data['option_data'];
        }

        // $layout = GetCandy::layouts()->getByHashedId($data['layout_id']);
        // $blog->layout()->associate($layout);

        if (! empty($data['family_id'])) {
            $family = FetchBlogFamily::run([
                'encoded_id' => $data['family_id'],
            ]);
            if (! $family) {
                abort(422);
            }
            $family->blogs()->save($blog);
        } else {
            $blog->save();
        }

        $language = FetchDefaultLanguage::run();
        CreateRoute::run([
            'element_type' => Blog::class,
            'element_id' => $blog->encoded_id,
            'language_id' => $language->encoded_id,
            'slug' => $data['url'],
            'default' => true,
            'redirect' => false,
        ]);

        event(new BlogCreatedEvent($blog));

        return $blog;
    }

    protected function getPriceMapping($price)
    {
        $customerGroups = FetchCustomerGroups::run([
            'paginate' => false,
        ]);

        return $customerGroups->map(function ($group) use ($price) {
            return [
                $group->handle => [
                    'price' => $price,
                    'compare_at' => 0,
                    'tax' => 0,
                ],
            ];
        })->toArray();
    }

    /**
     * Creates a blog variant.
     *
     * @param  \GetCandy\Api\Core\Blogs\Models\Blog  $blog
     * @param  array  $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createVariant(Blog $blog, array $data = [])
    {
        $data['attribute_data'] = $blog->attribute_data;

        return $blog->variants()->create($data);
    }

    /**
     * @param  string  $id
     * @return bool
     */
    public function delete($id)
    {
        $blog = Blog::withDrafted()->find($id);

        if ($blog->isDraft()) {
            return $blog->forceDelete();
        }

        return $blog->delete();
    }

    /**
     * Gets paginated data for the record.
     *
     * @param  string|null  $channel
     * @param  int  $length
     * @param  int|null  $page
     * @param  array  $ids
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getPaginatedData($channel = null, $length = 50, $page = null, $ids = [])
    {
        $results = $this->model->channel($channel);

        if (! empty($ids)) {
            $realIds = $this->getDecodedIds($ids);
            $results->whereIn('id', $realIds);
        }

        return $results->paginate($length, ['*'], 'page', $page);
    }

    /**
     * Gets the attributes from a given blogs id.
     *
     * @param  string  $id
     * @return array
     */
    public function getAttributes($id)
    {
        $id = $this->getDecodedId($id);
        $attributes = [];

        if (! $id) {
            return [];
        }

        $blog = $this->model->with([
            'attributes',
            'family',
            'family.attributes',
        ])->withDrafted()->find($id);

        foreach ($blog->family->attributes as $attribute) {
            $attributes[$attribute->handle] = $attribute;
        }

        // Direct attributes override family ones
        foreach ($blog->attributes as $attribute) {
            $attributes[$attribute->handle] = $attribute;
        }

        return $attributes;
    }

    public function getSearchedIds($ids = [], array $includes = [])
    {
        $parsedIds = [];
        foreach ($ids as $hash) {
            $id = $this->model->decodeId($hash);
            if (! $id) {
                $parsedIds[] = $hash;
            } else {
                $parsedIds[] = $id;
            }
        }

        $placeholders = implode(',', array_fill(0, count($parsedIds), '?')); // string for the query

        $query = $this->model->with($includes)->whereIn('blogs.id', $parsedIds);

        if (count($parsedIds)) {
            $query = $query->orderByRaw("field(blogs.id,{$placeholders})", $parsedIds);
        }

        return $query->get();
    }

    /**
     * Gets recommended blogs based on an array of blogs.
     *
     * @param  array|\Illuminate\Database\Eloquent\Collection  $blogs
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecommendations($blogs = [], $limit = 6)
    {
        return BlogRecommendation::whereIn('blog_id', $blogs)
            ->with(
                'blog.routes',
                'blog.categories.assets.transforms',
                'blog.variants.tiers',
                'blog.assets.transforms',
                'blog.firstVariant'
            )
            ->whereHas('blog')
            ->select(
                'related_blog_id',
                \DB::RAW('SUM(count) as count')
            )
            ->groupBy('related_blog_id')
            ->orderBy('count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Gets the attributes from a given blogs id.
     *
     * @param  \GetCandy\Api\Core\Blogs\Models\Blog  $blog
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCategories(Blog $blog)
    {
        $blog = $this->model
            ->with(['categories', 'routes'])
            ->find($blog->id);

        return $blog->categories;
    }

    /**
     * Updates the collections for a blog.
     *
     * @param  string  $id
     * @param  array  $data
     * @return \GetCandy\Api\Core\Blogs\Models\Blog
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function updateCollections($id, array $data)
    {
        $ids = [];

        $blog = $this->getByHashedId($id);

        foreach ($data['collections'] as $attribute) {
            $ids[] = GetCandy::collections()->getDecodedId($attribute);
        }

        $blog->collections()->sync($ids);

        return $blog;
    }

    /**
     * Get blogs by a stock threshold.
     *
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByStockThreshold($limit = 15)
    {
        return $this->model
            ->with('variants')
            ->withoutGlobalScope(CustomerGroupScope::class)
            ->with(['variants' => function ($q) use ($limit) {
                return $q->where('stock', '<=', $limit);
            }])->whereHas('variants', function ($q2) use ($limit) {
                return $q2->where('stock', '<=', $limit);
            })->get();
    }
}
