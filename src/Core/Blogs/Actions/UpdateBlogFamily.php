<?php

namespace GetCandy\Api\Core\Blogs\Actions;

use GetCandy\Api\Core\Attributes\Actions\AttachModelToAttributes;
use GetCandy\Api\Core\Foundation\Actions\DecodeId;
use GetCandy\Api\Core\Blogs\Models\BlogFamily;
use GetCandy\Api\Core\Blogs\Resources\BlogFamilyResource;
use GetCandy\Api\Core\Scaffold\AbstractAction;

class UpdateBlogFamily extends AbstractAction
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
    public function rules(): array
    {
        $blogFamilyId = DecodeId::run([
            'encoded_id' => $this->encoded_id,
            'model' => BlogFamily::class,
        ]);

        return [
            'name' => 'required|string|unique:blog_families,name,'.$blogFamilyId,
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
        $blogFamily = $this->delegateTo(FetchBlogFamily::class);
        $blogFamily->update([
            'name' => $this->name,
        ]);

        if ($this->attribute_ids) {
            AttachModelToAttributes::run([
                'model' => $blogFamily,
                'attribute_ids' => $this->attribute_ids,
            ]);
        }

        return $blogFamily;
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
