<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
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
            'product_id' => $this->product_id,
            'quantity' => (int) $this->quantity,
            'price' => (float) $this->price,
            'subtotal' => (float) $this->subtotal,
            
            // Product info
            'product' => $this->whenLoaded('product', fn() => new ProductResource($this->product)),
            
            'created_at' => $this->created_at,
        ];
    }
}
