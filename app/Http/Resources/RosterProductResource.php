<?php

namespace App\Http\Resources;

use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RosterProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request)
    {
        return [
            'id'=>$this->id,
            'user'=>User::find($this->user_id),
            'quantity'=>$this->quantity,
            'status'=>$this->status,
            'product_id'=>$this->product_id,
            'roster_id'=>$this->roster_id,
        ];
    }
}
