<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'name'=>$this->name,
            'barcode'=>$this->barcode,
            'carton_contains'=>$this->carton_contains,
            'quantity'=>$this->quantity,
            'image'=>$this->image == 'default.jpg' ? route('products.image',['path'=>$this->image]) : route('products.image',['path'=>$this->name.'/'.$this->image]),
            'store'=>$this->whenLoaded('store'),
            'category'=>$this->whenLoaded('category'),
            'rosters'=>$this->whenLoaded('rosters'),
            'inRoster'=>$this->inRoster ? new RosterProductResource($this->inRoster) : null,
        ];
    }
}
