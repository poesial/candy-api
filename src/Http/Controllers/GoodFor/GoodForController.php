<?php

namespace GetCandy\Api\Http\Controllers\GoodFor;

use GetCandy;
use GetCandy\Api\Core\GoodFor\Services\GoodForService;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\GoodFor\UpdateRequest;
use GetCandy\Api\Http\Requests\GoodFor\StoreRequest;
use GetCandy\Api\Http\Resources\GoodFor\GoodForCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GoodForController extends BaseController
{
    /**
     * @var GoodForService
     */
    protected $service;
    protected $serviceProduct;

    public function __construct(GoodForService $service, GetCandy\Api\Core\Products\Services\ProductService $productService)
    {
        $this->service = $service;
        $this->serviceProduct = $productService;
    }

    /**
     * Handles the request to show all blogs.
     *
     * @param Request $request
     * @return GoodForCollection
     */
    public function index()
    {
        $icons = $this->service->get();

        return new GoodForCollection($icons);
    }

    /**
     * Handles the request to show all blogs.
     *
     * @param Request $request
     * @return GoodForCollection
     */
    public function destroy($id)
    {
        $this->service->delete($id);

        return response()->json(['success' => true]);
    }

    /**
     * Handles the request to show all blogs.
     *
     * @param Request $request
     * @return GoodForCollection
     */
    public function attach($id, $productId)
    {
        $this->serviceProduct->attach($id, $productId);
        return response()->json(['success' => true]);
    }

    /**
     * Handles the request to update a blog.
     *
     * @param  \GetCandy\Api\Http\Requests\GoodFor\StoreRequest  $request
     * @return array
     */
    public function store(StoreRequest $request)
    {
        try {
            $file = $request->get('icon');
            $icon = null;
            if (!empty($file)) {
                $icon = uniqid() . '.' . $file->getClientOriginalExtension();
                Storage::putFileAs('good-for-icons', $file, $icon);
            }

            $this->service->create($request->only(['name']) + ['icon' => $icon]);
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        } catch (HttpException $e) {
            return $this->errorUnprocessable($e->getMessage());
        }

        return response()->json(['success' => true]);
    }
}
