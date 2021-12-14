<?php

namespace GetCandy\Api\Core\Blogs\Actions;

use GetCandy\Api\Core\Attributes\Actions\AttachModelToAttributes;
use GetCandy\Api\Core\Attributes\Models\Attribute;
use GetCandy\Api\Core\Blogs\Models\BlogFamily;
use GetCandy\Api\Core\Blogs\Resources\BlogFamilyResource;
use GetCandy\Api\Core\Scaffold\AbstractAction;

class CreateBlogFamily extends AbstractAction
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('manage-blog-families');
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|unique:blog_families,name',
            'attribute_ids' => 'array',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Blogs\Models\BlogFamily
     */
    public function handle()
    {
        $blogFamily = BlogFamily::create($this->validated());

        if (is_array($this->attribute_ids)) {
            $attributes = Attribute::system()->get()->map(function ($attribute) {
                return $attribute->encoded_id;
            })->merge($this->attribute_ids ?? []);

            AttachModelToAttributes::run([
                'model' => $blogFamily,
                'attribute_ids' => $attributes->toArray(),
            ]);
        }

        return $blogFamily->load($this->resolveEagerRelations());
    }

    /**
     * Returns the response from the action.
     *
     * @param   \GetCandy\Api\Core\Blogs\Models\BlogFamily  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \GetCandy\Api\Core\Blogs\Resources\BlogFamilyResource
     */
    public function response($result, $request)
    {
        return new BlogFamilyResource($result);
    }
}
