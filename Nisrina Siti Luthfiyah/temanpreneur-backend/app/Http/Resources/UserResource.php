<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Utils\ImageUrl;

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
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'role' => $this->role,
            'roles' => $this->roles,
            'class' => $this->class,
            'status' => $this->status,
            'is_verified' => $this->is_verified,

            'photo' => ImageUrl::normalize($this->photo),
            'avatar_url' => ImageUrl::normalize($this->photo),

            'business' => $this->whenLoaded('business', function () {
                if (!$this->business) return null;

                return new \App\Http\Resources\BusinessResource($this->business);
            }),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
