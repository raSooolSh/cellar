<?php

namespace App\Events\ProductsInRoster;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class DeleteProductInRosterEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $id;
    public $product;
    /**
     * Create a new event instance.
     */
    public function __construct($id,Product $product)
    {
        $this->id = $id;
        $this->product = new ProductResource($product->load(['store','category']));
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('products-in-roster'),
        ];
    }
    
    public function broadcastAs(){
        return 'delete';
    }
}
