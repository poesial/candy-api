<?php

namespace GetCandy\Api\Core\Search\Indexables;

class BlogIndexable extends AbstractIndexable
{
    public function getMapping()
    {
        return array_merge(parent::getMapping(), [
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
            'title' => [
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
        ]);
    }
}
