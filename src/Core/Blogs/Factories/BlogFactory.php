<?php

namespace GetCandy\Api\Core\Blogs\Factories;

use GetCandy\Api\Core\Blogs\Interfaces\BlogInterface;
use GetCandy\Api\Core\Blogs\Interfaces\BlogVariantInterface;
use GetCandy\Api\Core\Blogs\Models\Blog;
use Illuminate\Support\Collection;

class BlogFactory implements BlogInterface
{
    /**
     * @var \GetCandy\Api\Core\Blogs\Models\Blog
     */
    protected $blog;

    /**
     * @var \GetCandy\Api\Core\Blogs\Interfaces\BlogVariantInterface
     */
    protected $variantFactory;

    public function __construct(BlogVariantInterface $variantFactory)
    {
        $this->variantFactory = $variantFactory;
    }

    public function init($blog)
    {
        $this->blog = $blog;

        return $this;
    }

    /**
     * Get the processed blog.
     *
     * @return \GetCandy\Api\Core\Blogs\Models\Blog
     */
    public function get()
    {
        return $this->blog;
    }

    /**
     * Process a collection of blogs.
     *
     * @param  \Illuminate\Support\Collection  $blogs
     * @return \Illuminate\Support\Collection
     */
    public function collection(Collection $blogs)
    {
        foreach ($blogs as $blog) {
            $this->init($blog)->get();
        }

        return $blogs;
    }
}
