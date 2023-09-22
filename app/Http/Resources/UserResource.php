<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'phone' => $this->phone,
            'roles'=> $this->whenLoaded('roles',RoleResource::collection($this->roles)),
            'image'=>is_null($this->image) ? null : route('users.image',['path'=>$this->id.'/'.$this->image])
        ];
    }
}
