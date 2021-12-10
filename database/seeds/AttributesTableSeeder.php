<?php

namespace Seeds;

use GetCandy\Api\Core\Attributes\Models\Attribute;
use GetCandy\Api\Core\Attributes\Models\AttributeGroup;
use Illuminate\Database\Seeder;

class AttributesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $group = AttributeGroup::create([
            'name' => ['en' => 'Marketing', 'sv' => 'Marknadsförande'],
            'handle' => 'marketing',
            'position' => 1,
        ]);

        $attribute = new Attribute();
        $attribute->name = ['en' => 'Name', 'sv' => 'Namn'];
        $attribute->handle = 'name';
        $attribute->position = 1;
        $attribute->group_id = $group->id;
        $attribute->required = true;
        $attribute->scopeable = 1;
        $attribute->searchable = 1;
        $attribute->save();

        $attribute = new Attribute();
        $attribute->name = ['en' => 'Short Description', 'sv' => 'Beskrivning'];
        $attribute->handle = 'short_description';
        $attribute->position = 2;
        $attribute->group_id = $group->id;
        $attribute->channeled = 1;
        $attribute->required = true;
        $attribute->type = 'richtext';
        $attribute->scopeable = 1;
        $attribute->searchable = 1;
        $attribute->save();

        $attribute = new Attribute();
        $attribute->name = ['en' => 'Description', 'sv' => 'Beskrivning'];
        $attribute->handle = 'description';
        $attribute->position = 2;
        $attribute->group_id = $group->id;
        $attribute->channeled = 1;
        $attribute->required = true;
        $attribute->type = 'richtext';
        $attribute->scopeable = 1;
        $attribute->searchable = 1;
        $attribute->save();

        $attribute = new Attribute();
        $attribute->name = ['en' => 'How to use', 'sv' => 'Hur man använder'];
        $attribute->handle = 'how_to_use';
        $attribute->position = 2;
        $attribute->group_id = $group->id;
        $attribute->channeled = 1;
        $attribute->required = false;
        $attribute->type = 'richtext';
        $attribute->scopeable = 1;
        $attribute->searchable = 1;
        $attribute->save();

        $attribute = new Attribute();
        $attribute->name = ['en' => 'Product warnings', 'sv' => 'Produktvarningar'];
        $attribute->handle = 'product_warnings';
        $attribute->position = 2;
        $attribute->group_id = $group->id;
        $attribute->channeled = 1;
        $attribute->required = false;
        $attribute->type = 'richtext';
        $attribute->scopeable = 1;
        $attribute->searchable = 1;
        $attribute->save();

        $attribute = new Attribute();
        $attribute->name = ['en' => 'Product specifications', 'sv' => 'Produktspecifikationer'];
        $attribute->handle = 'product_specifications';
        $attribute->position = 2;
        $attribute->group_id = $group->id;
        $attribute->channeled = 1;
        $attribute->required = false;
        $attribute->type = 'richtext';
        $attribute->scopeable = 1;
        $attribute->searchable = 1;
        $attribute->save();

        $attribute = new Attribute();
        $attribute->name = ['en' => 'Product ingredients', 'sv' => 'Produktens ingredienser'];
        $attribute->handle = 'product_ingredients';
        $attribute->position = 2;
        $attribute->group_id = $group->id;
        $attribute->channeled = 1;
        $attribute->required = false;
        $attribute->type = 'richtext';
        $attribute->scopeable = 1;
        $attribute->searchable = 1;
        $attribute->save();

        $attribute = new Attribute();
        $attribute->name = ['en' => 'Product description highlight (Poesial says)', 'sv' => 'Produktbeskrivning höjdpunkt (Poesial säger)'];
        $attribute->handle = 'description_highlight';
        $attribute->position = 2;
        $attribute->group_id = $group->id;
        $attribute->channeled = 1;
        $attribute->required = false;
        $attribute->type = 'richtext';
        $attribute->scopeable = 1;
        $attribute->searchable = 1;
        $attribute->save();

        // $group = AttributeGroup::create([
        //     'name' => ['en' => 'General', 'sv' => 'Allmän'],
        //     'handle' => 'general',
        //     'position' => 2
        // ]);

        $group = AttributeGroup::create([
            'name' => ['en' => 'SEO', 'sv' => 'SEO'],
            'handle' => 'seo',
            'position' => 3,
        ]);

        $attribute = new Attribute();
        $attribute->name = ['en' => 'Page Title', 'sv' => 'Titre de la page'];
        $attribute->handle = 'page_title';
        $attribute->position = 1;
        $attribute->group_id = $group->id;
        $attribute->channeled = 1;
        $attribute->required = false;
        $attribute->scopeable = 1;
        $attribute->searchable = 1;
        $attribute->save();

        $attribute = new Attribute();
        $attribute->name = ['en' => 'Meta description', 'sv' => 'Meta description'];
        $attribute->handle = 'meta_description';
        $attribute->position = 2;
        $attribute->group_id = $group->id;
        $attribute->channeled = 1;
        $attribute->required = false;
        $attribute->scopeable = 1;
        $attribute->searchable = 1;
        $attribute->type = 'textarea';
        $attribute->save();

        $attribute = new Attribute();
        $attribute->name = ['en' => 'Meta Keywords', 'sv' => 'Titre de la page'];
        $attribute->handle = 'meta_keywords';
        $attribute->position = 3;
        $attribute->group_id = $group->id;
        $attribute->channeled = 1;
        $attribute->required = false;
        $attribute->scopeable = 1;
        $attribute->searchable = 1;
        $attribute->save();
    }
}
