<?php

namespace GetCandy\Api\Core\Blogs\Listeners;

use GetCandy\Api\Core\Blogs\Events\BlogCreatedEvent;
use GetCandy\Api\Core\Search\Actions\IndexObjects;

class AddToIndexListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param  \GetCandy\Api\Core\Blogs\Events\BlogCreatedEvent  $event
     * @return void
     */
    public function handle(BlogCreatedEvent $event)
    {
        $blog = $event->blog();
        if (! $blog->isDraft()) {
            IndexObjects::run([
                'documents' => $blog,
            ]);
        }
    }
}
