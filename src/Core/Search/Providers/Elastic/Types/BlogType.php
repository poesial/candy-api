<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic\Types;

use Elastica\Document;
use GetCandy\Api\Core\Blogs\Models\Blog;

class BlogType extends BaseType
{
    /**
     * @var string
     */
    protected $model = Blog::class;

    /**
     * @var string
     */
    protected $handle = 'blogs';

    /**
     * @var array
     */
    protected $mapping = [
        'id' => [
            'type' => 'text',
        ],
        'popularity' => [
            'type' => 'integer',
        ],
        'created_at'  => [
            'type' => 'date',
        ],
        'breadcrumbs' => [
            'type' => 'text',
            'analyzer' => 'standard',
            'fields' => [
                'en' => [
                    'type' => 'text',
                    'analyzer' => 'english',
                ],
            ],
        ],
        'name' => [
            'type' => 'text',
            'analyzer' => 'standard',
            'fields' => [
                'sortable' => [
                    'type' => 'keyword',
                ],
                'suggest' => [
                    'type' => 'completion',
                ],
                'en' => [
                    'type' => 'text',
                    'analyzer' => 'english',
                ],
                'trigram' => [
                    'type' => 'text',
                    'analyzer' => 'trigram',
                ],
            ],
        ],
    ];

    /**
     * Returns the Index document ready to be added.
     *
     * @param  \GetCandy\Api\Core\Blogs\Models\Blog  $blog
     * @return mixed
     */
    public function getIndexDocument(Blog $blog)
    {
        return $this->getIndexables($blog);
    }

    public function getUpdatedDocument($model, $field, $index)
    {
        $method = 'update'.camel_case($field);
        if (method_exists($this, $method)) {
            return $this->{$method}($model, $index);
        }
    }

    public function getUpdatedDocuments($models, $field, $index)
    {
        $method = 'update'.camel_case($field);
        $collection = [];
        if (method_exists($this, $method)) {
            foreach ($models as $model) {
                $collection[] = $this->{$method}($model, $index);
            }
        }

        return $collection;
    }

    protected function updateCategories($model, $index)
    {
        $document = $this->getIndexDocument($model);

        return $document;
    }

    public function getIndexDocuments($blogs)
    {
        $collection = collect();
        foreach ($blogs as $blog) {
            $indexables = $this->getIndexDocument($blog);
            foreach ($indexables as $document) {
                $collection->push($document);
            }
        }

        return $collection;
    }

    public function rankings()
    {
        return config('getcandy.search.ranking.blogs');
    }
}
