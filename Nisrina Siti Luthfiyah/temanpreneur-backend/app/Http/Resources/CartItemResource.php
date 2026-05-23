<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $product = $this->product;
        $price = 0;
        $subtotal = 0;

        if ($product && isset($product->price)) {
            $price = (float) $product->price;
            $subtotal = (float) ($price * $this->quantity);
        }

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'product_id' => $this->product_id,
            'quantity' => (int) $this->quantity,
            
            // Product info
            'product' => $this->whenLoaded('product', fn() => $product ? new ProductResource($product) : null),
            
            // Calculate subtotal with safety checks
            'price' => $price,
            'subtotal' => $subtotal,
            
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
