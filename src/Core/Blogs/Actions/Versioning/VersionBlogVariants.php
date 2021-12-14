<?php

namespace GetCandy\Api\Core\Blogs\Actions\Versioning;

use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Versioning\Actions\CreateVersion;

class VersionBlogVariants extends AbstractAction
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('manage-versions');
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'version' => 'required',
            'blog' => 'required',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function handle()
    {
        // Create our base version.
        foreach ($this->blog->variants as $variant) {
            $variantVersion = (new CreateVersion)->actingAs($this->user())->run([
                'model' => $variant,
                'relation' => $this->version,
            ]);
            (new VersionBlogVariantTiers)->actingAs($this->user())->run([
                'version' => $variantVersion,
                'variant' => $variant,
            ]);
            (new VersionBlogVariantCustomerPricing)->actingAs($this->user())->run([
                'version' => $variantVersion,
                'variant' => $variant,
            ]);
        }

        return $this->version;
    }
}
