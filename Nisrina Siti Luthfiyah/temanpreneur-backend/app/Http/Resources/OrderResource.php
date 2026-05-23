<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Utils\ImageUrl;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'buyer_id' => $this->buyer_id,
            'business_id' => $this->business_id,
            'order_group_id' => $this->order_group_id,
            'group' => $this->whenLoaded('orderGroup', function () {
                return [
                    'id' => $this->orderGroup->id,
                    'group_code' => $this->orderGroup->group_code,
                    'grand_total' => (float) ($this->orderGroup->grand_total ?? 0),
                ];
            }),
            'status' => strtolower($this->status ?? ''),

            'payment_method' => $this->payment_method,

            'shipping_cost' => (float) ($this->shipping_cost ?? 0),
            'total_amount' => (float) ($this->total_amount ?? 0),

            'shipping_address' => $this->shipping_address,
            'shipping_phone' => $this->shipping_phone,
            'shipping_name' => $this->shipping_name,
            'buyer_notes' => $this->buyer_notes,

            /**
             * SUBTOTAL dihitung dari items (aman kalau items belum loaded)
             */
            'subtotal' => $this->whenLoaded('items', function () {
                return (float) $this->items->sum('subtotal');
            }, 0),

            /**
             * BUYER
             */
            'buyer' => $this->whenLoaded('buyer', function () {
                return [
                    'id' => $this->buyer->id ?? null,
                    'name' => $this->buyer->name ?? null,
                    'email' => $this->buyer->email ?? null,
                    'phone' => $this->buyer->phone ?? null,
                ];
            }),

            /**
             * ITEMS + PRODUCT + BUSINESS (FULL SAFE)
             */
            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(function ($item) {
                    $product = $item->relationLoaded('product') && is_object($item->product) ? $item->product : null;
                    $category = $product && $product->relationLoaded('category') && is_object($product->category) ? $product->category : null;
                    $business = $product && $product->relationLoaded('business') && is_object($product->business) ? $product->business : null;

                    return [
                        'id' => $item->id,
                        'quantity' => $item->quantity,
                        'price' => (float) $item->price,
                        'subtotal' => (float) $item->subtotal,

                        'product' => $product ? [
                            'id' => $product->id,
                            'name' => $product->name,
                            'description' => $product->description,
                            'image' => ImageUrl::normalize($product->image),
                            'images' => $this->productImageUrls($product),

                            'category' => $category ? [
                                'id' => $category->id,
                                'name' => $category->name,
                                'slug' => $category->slug,
                            ] : null,

                            'business' => $business ? [
                                'id' => $business->id,
                                'name' => $business->name,
                                'logo' => ImageUrl::normalize($business->logo),
                                'is_premium' => (bool) ($business->is_premium ?? false),
                            ] : null,

                        ] : null,
                    ];
                });
            }, []),

            /**
             * TRACKING / HISTORY
             */
            'tracking' => $this->whenLoaded('trackings', function () {
                return $this->trackings->map(function ($track) {
                    return [
                        'id' => $track->id,
                        'status' => strtolower($track->status),
                        'created_at' => $track->created_at,
                        'updated_by' => $track->updated_by ?? null,
                    ];
                });
            }, []),

            /**
             * TIMESTAMP
             */
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * @param  \App\Models\Product  $product
     * @return array<int, string>
     */
    private function productImageUrls($product): array
    {
        $urls = [];
        if (!empty($product->images) && is_array($product->images)) {
            foreach ($product->images as $path) {
                if ($path) {
                    $urls[] = ImageUrl::normalize($path);
                }
            }
        }
        if (empty($urls) && $product->image) {
            $urls[] = ImageUrl::normalize($product->image);
        }

        return $urls;
    }
}