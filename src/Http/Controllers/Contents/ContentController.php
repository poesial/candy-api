<?php

namespace GetCandy\Api\Http\Controllers\Contents;

use GetCandy;
use GetCandy\Api\Core\Contents\Services\ContentService;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Contents\UpdateRequest;
use GetCandy\Api\Http\Resources\Contents\ContentResource;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ContentController extends BaseController
{
    /**
     * @var ContentService
     */
    protected $service;

    public function __construct(ContentService $service)
    {
        $this->service = $service;
    }

    /**
     * Handles the request to show all blogs.
     *
     * @param Request $request
     * @return ContentResource
     */
    public function index($id)
    {
        $content = $this->service->findBySlug($id);

        if (!$content) {
            $content = $this->service->createBySlug($id);
        }

        return new ContentResource($content);
    }

    /**
     * Handles the request to update a blog.
     *
     * @param  string  $id
     * @param  \GetCandy\Api\Http\Requests\Contents\UpdateRequest  $request
     * @return array
     */
    public function update($id, UpdateRequest $request)
    {
        try {
            $this->service->update($id, $request->all());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        } catch (HttpException $e) {
            return $this->errorUnprocessable($e->getMessage());
        }

        return response()->json(['success' => true]);
    }
}
