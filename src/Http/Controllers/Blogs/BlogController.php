<?php

namespace GetCandy\Api\Http\Controllers\Blogs;

use Drafting;
use GetCandy;
use GetCandy\Api\Core\Baskets\Interfaces\BasketCriteriaInterface;
use GetCandy\Api\Core\Blogs\Factories\BlogDuplicateFactory;
use GetCandy\Api\Core\Blogs\Models\Blog;
use GetCandy\Api\Core\Blogs\BlogCriteria;
use GetCandy\Api\Core\Blogs\Services\BlogService;
use GetCandy\Api\Exceptions\InvalidLanguageException;
use GetCandy\Api\Exceptions\MinimumRecordRequiredException;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Blogs\CreateRequest;
use GetCandy\Api\Http\Requests\Blogs\DeleteRequest;
use GetCandy\Api\Http\Requests\Blogs\DuplicateRequest;
use GetCandy\Api\Http\Requests\Blogs\UpdateRequest;
use GetCandy\Api\Http\Resources\Blogs\BlogCollection;
use GetCandy\Api\Http\Resources\Blogs\BlogRecommendationCollection;
use GetCandy\Api\Http\Resources\Blogs\BlogResource;
use Hashids;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BlogController extends BaseController
{
    /**
     * @var \GetCandy\Api\Core\Blogs\Services\BlogService
     */
    protected $service;

    public function __construct(BlogService $service)
    {
        $this->service = $service;
    }

    /**
     * Handles the request to show all blogs.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \GetCandy\Api\Core\Blogs\BlogCriteria  $criteria
     * @return \GetCandy\Api\Http\Resources\Blogs\BlogCollection
     */
    public function index(Request $request, BlogCriteria $criteria)
    {
        $paginate = true;

        if ($request->exists('paginated') && ! $request->paginated) {
            $paginate = false;
        }

        $blogs = $criteria
            ->include($this->parseIncludes($request->include))
            ->ids($request->ids)
            ->limit($request->get('limit', 50))
            ->paginated($paginate)
            ->get();

        return new BlogCollection($blogs);
    }

    /**
     * Handles the request to show a blog based on hashed ID.
     *
     * @param  string  $idOrSku
     * @param  \Illuminate\Http\Request  $request
     * @return array|\GetCandy\Api\Http\Resources\Blogs\BlogResource
     */
    public function show($idOrSku, Request $request)
    {
        $id = Hashids::connection('blog')->decode($idOrSku);

        $includes = $this->parseIncludes($request->include);

        if (empty($id[0])) {
            $blog = $this->service->findBySku($idOrSku, $includes, $request->draft);
        } else {
            $blog = $this->service->findById($id[0], $includes, $request->draft);
        }

        if (! $blog) {
            return $this->errorNotFound();
        }
        $resource = new BlogResource($blog);

        return $resource->only($request->fields);
    }

    public function createDraft($id, Request $request)
    {
        $id = Hashids::connection('blog')->decode($id);

        if (empty($id[0])) {
            return $this->errorNotFound();
        }

        $blog = $this->service->findById($id[0], [], false);
        $draft = Drafting::with('blogs')->firstOrCreate($blog);

        return new BlogResource($draft);
    }

    public function publishDraft($id, Request $request)
    {
        $id = Hashids::connection('blog')->decode($id);
        if (empty($id[0])) {
            return $this->errorNotFound();
        }
        $blog = $this->service->findById($id[0], [], true);

        if (! $blog) {
            return $this->errorNotFound();
        }

        $blog = Drafting::with('blogs')->publish($blog);

        return new BlogResource($blog->load($this->parseIncludes($request->include)));
    }

    public function recommended(Request $request, BlogCriteria $blogCriteria, BasketCriteriaInterface $baskets)
    {
        $request->validate([
            'basket_id' => 'required|hashid_is_valid:baskets',
        ]);

        $basket = $baskets->id($request->basket_id)->first();

        $blogs = $basket->lines->map(function ($line) {
            return $line->variant->blog_id;
        })->toArray();

        $blogs = GetCandy::blogs()->getRecommendations($blogs);

        return new BlogRecommendationCollection($blogs);
    }

    /**
     * Handles the request to create a new blog.
     *
     * @param  \GetCandy\Api\Http\Requests\Blogs\CreateRequest  $request
     * @return array
     */
    public function store(CreateRequest $request)
    {
        try {
            $blog = GetCandy::blogs()->create($request->all());
        } catch (InvalidLanguageException $e) {
            return $this->errorUnprocessable($e->getMessage());
        }

        return new BlogResource($blog);
    }

    /**
     * Handles the request to update a blog.
     *
     * @param  string  $id
     * @param  \GetCandy\Api\Http\Requests\Blogs\UpdateRequest  $request
     * @return array|\Illuminate\Http\Response
     */
    public function update($id, UpdateRequest $request)
    {
        try {
            $blog = GetCandy::blogs()->update($id, $request->all());
        } catch (MinimumRecordRequiredException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        } catch (HttpException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (InvalidLanguageException $e) {
            return $this->errorUnprocessable($e->getMessage());
        }

        return new BlogResource($blog);
    }

    public function duplicate($blog, DuplicateRequest $request, BlogDuplicateFactory $factory)
    {
        try {
            $blog = Blog::with([
                'variants',
                'routes',
                'assets',
                'customerGroups',
                'channels',
            ])->findOrFail((new Blog)->decodeId($blog));
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }
        $result = $factory->init($blog)->duplicate(collect($request->all()));

        return new BlogResource($result);
    }

    /**
     * Handles the request to delete a blog.
     *
     * @param  string  $id
     * @param  \GetCandy\Api\Http\Requests\Blogs\DeleteRequest  $request
     * @return array|\Illuminate\Http\Response
     */
    public function destroy($id, DeleteRequest $request)
    {
        try {
            $id = Hashids::connection('blog')->decode($id);
            if (empty($id[0])) {
                return $this->errorNotFound();
            }
            $result = $this->service->delete($id[0], true);
        } catch (MinimumRecordRequiredException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithNoContent();
    }
}
