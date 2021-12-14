<?php

namespace GetCandy\Api\Core\Blogs\Events;

use GetCandy\Api\Core\Blogs\Models\Blog;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BlogSavedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var \GetCandy\Api\Core\Blogs\Models\Blog
     */
    protected $blog;

    /**
     * Create a new event instance.
     *
     * @param  \GetCandy\Api\Core\Blogs\Models\Blog  $blog
     * @return void
     */
    public function __construct(Blog $blog)
    {
        $this->blog = $blog;
    }

    public function blog()
    {
        return $this->blog;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\PrivateChannel
     */
    public function broadcastOn()
    {
        return new PrivateChannel('blogs');
    }
}
