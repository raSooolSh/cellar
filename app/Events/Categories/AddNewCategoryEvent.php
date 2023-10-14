<?php

namespace App\Events\Categories;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AddNewCategoryEvent implements ShouldBroadcast,ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $category;
    /**
     * Create a new event instance.
     */
    public function __construct(Category $category)
    {
        $this->category = new CategoryResource($category);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('categories'),
        ];
    }

    public function broadcastAs(){
        return 'add';
    }
}
