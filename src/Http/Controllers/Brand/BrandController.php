<?php

namespace GetCandy\Api\Http\Controllers\Brand;

use GetCandy;
use GetCandy\Api\Core\Brand\Services\BrandService;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Brand\UpdateRequest;
use GetCandy\Api\Http\Resources\Brand\BrandResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BrandController extends BaseController
{
    /**
     * @var BrandService
     */
    protected $service;

    public function __construct(BrandService $service)
    {
        $this->service = $service;
    }

    /**
     * Handles the request to show all blogs.
     *
     * @param Request $request
     * @return BrandResource
     */
    public function index()
    {
        $brand = $this->service->first();

        if (!$brand) {
            $brand = $this->service->createFirst();
        }

        return new BrandResource($brand);
    }

    /**
     * Handles the request to update a blog.
     *
     * @param  \GetCandy\Api\Http\Requests\Brand\UpdateRequest  $request
     * @return array
     */
    public function update(UpdateRequest $request)
    {
        try {
            $file = $request->get('logo');
            $logo = null;
            if (!empty($file)) {
                $logo = 'brand-logo.' . $file->getClientOriginalExtension();
                Storage::putFileAs('brand', $file, $logo);
            }

            $this->service->updateFirst($request->only(['name', 'description']) + ['logo' => $logo]);
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        } catch (HttpException $e) {
            return $this->errorUnprocessable($e->getMessage());
        }

        return response()->json(['success' => true]);
    }
}
