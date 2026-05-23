<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ProductResource;
use App\Utils\ImageUrl;

class BusinessResource extends JsonResource
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
            'user_id' => $this->user_id,
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category,
            'phone' => $this->phone,
            'address' => $this->address,
            'logo' => ImageUrl::normalize($this->logo),
            'banner' => ImageUrl::normalize($this->banner),
            'status' => $this->status, // pending, verified
            'type' => $this->is_premium ? 'premium' : 'regular', // regular, premium
            'is_verified' => (bool) $this->is_verified,
            'is_premium' => (bool) $this->is_premium,
            'rejection_reason' => $this->rejection_reason,
            'processed_at' => $this->processed_at,
            'theme_color' => $this->theme_color,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Relationships (with eager loading)
            'owner' => $this->whenLoaded('user', function () {
                if (is_object($this->user)) {
                    return [
                        'id' => $this->user->id,
                        'name' => $this->user->name,
                        'email' => $this->user->email,
                        'phone' => $this->user->phone,
                        'address' => $this->user->address,
                    ];
                }
                return null;
            }),
            'products_count' => $this->whenLoaded('products', fn() => is_array($this->products) ? count($this->products) : ($this->products ? $this->products->count() : 0)),
            'products' => $this->whenLoaded('products', fn() => ProductResource::collection($this->products)),
        ];
    }
}
