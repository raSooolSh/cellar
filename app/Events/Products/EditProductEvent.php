<?php

namespace App\Events\Products;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class EditProductEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public $product;
    /**
     * Create a new event instance.
     */
    public function __construct(Product $product)
    {
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
            new PrivateChannel('products'),
        ];
    }

    public function broadcastAS()
    {
        return 'edit';
    }
}
