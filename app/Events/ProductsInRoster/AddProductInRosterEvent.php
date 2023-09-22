<?php

namespace App\Events\ProductsInRoster;

use App\Models\Product;
use App\Models\RosterProduct;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use App\Http\Resources\ProductResource;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use App\Http\Resources\RosterProductResource;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class AddProductInRosterEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $inRoster;
    public $product;
    /**
     * Create a new event instance.
     */
    public function __construct(RosterProduct $rosterProduct,Product $product)
    {
        $this->inRoster = new RosterProductResource($rosterProduct);
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
        return 'add';
    }
}
