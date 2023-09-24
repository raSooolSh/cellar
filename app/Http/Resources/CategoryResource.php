<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            'image'=>$this->image == 'default.jpg' ? route('categories.image',['path'=>$this->image]) : route('categories.image',['path'=>$this->name. '/'.$this->image]),
        ];
    }
}
