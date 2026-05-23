<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Utils\ImageUrl;

class ReviewResource extends JsonResource
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
            'order_id' => $this->order_id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            
            // Buyer info
            'buyer' => $this->whenLoaded('order.buyer', fn() => [
                'id' => $this->order->buyer->id,
                'name' => $this->order->buyer->name,
                'avatar' => $this->order->buyer->avatar ?? null,
            ]),
            
            // Product info
            'product' => $this->whenLoaded('order.items', fn() => [
                'id' => $this->order->items->first()?->product->id,
                'name' => $this->order->items->first()?->product->name,
                'image' => ImageUrl::normalize($this->order->items->first()?->product->image),
            ]),
            
            'created_at' => $this->created_at?->format('d M Y'),
            'created_at_full' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
