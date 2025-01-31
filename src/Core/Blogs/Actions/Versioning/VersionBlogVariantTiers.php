<?php

namespace GetCandy\Api\Core\Blogs\Actions\Versioning;

use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Versioning\Actions\CreateVersion;

class VersionBlogVariantTiers extends AbstractAction
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
            'variant' => 'required',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function handle()
    {
        foreach ($this->variant->tiers as $tier) {
            (new CreateVersion)->actingAs($this->user())->run([
                'model' => $tier,
                'relation' => $this->version,
            ]);
        }

        return $this->version;
    }
}
