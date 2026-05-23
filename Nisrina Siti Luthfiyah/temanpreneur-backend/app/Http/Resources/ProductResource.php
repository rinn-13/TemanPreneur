<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Utils\ImageUrl;

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
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => (int) $this->price,
            'stock' => (int) $this->stock,
            'category_id' => $this->category_id,
            'category_slug' => (function () {
                $attr = $this->resource->getAttribute('category');
                $rel = $this->resource->relationLoaded('category') ? $this->resource->getRelation('category') : null;
                if ($attr && is_string($attr) && trim($attr) !== '') return $attr;
                if ($rel && is_object($rel) && !empty($rel->slug)) return $rel->slug;
                return null;
            })(),
            'image' => ImageUrl::normalize($this->image),
            'images' => $this->galleryUrls(),
            'total_sold' => $this->total_sold ?? 0,
            'rating' => round($this->reviews_avg_rating ?? 0, 1),
            'reviews_count' => $this->whenLoaded('reviews', fn() => is_array($this->reviews) ? count($this->reviews) : ($this->reviews ? $this->reviews->count() : 0)),
            
            // Business/Seller Info
            'business' => $this->whenLoaded('business', function () {
                if (is_object($this->business)) {
                    return new \App\Http\Resources\BusinessResource($this->business);
                }
                return null;
            }),
            'seller' => $this->whenLoaded('business', function () {
                if (is_object($this->business) && is_object($this->business->user ?? null)) {
                    return [
                        'id' => $this->business->user->id,
                        'name' => $this->business->user->name,
                        'business_name' => $this->business->name,
                        'business_id' => $this->business->id,
                        'is_premium' => (bool) $this->business->is_premium,
                        'verified' => (bool) $this->business->is_verified,
                    ];
                }
                return null;
            }),

            // Category
            'category' => (function () {
                // Prefer relation object when loaded, otherwise fall back to stored category string
                if ($this->resource->relationLoaded('category')) {
                    $rel = $this->resource->getRelation('category');
                    if (is_object($rel)) {
                        return [
                            'id' => $rel->id,
                            'name' => $rel->name,
                            'slug' => $rel->slug,
                        ];
                    }
                }
                $attr = $this->resource->getAttribute('category');
                return $attr && is_string($attr) ? (string) $attr : null;
            })(),

            // Reviews (if loaded)
            'reviews' => $this->whenLoaded('reviews', function () {
                if (empty($this->reviews)) {
                    return [];
                }
                return $this->reviews->map(function ($review) {
                    $order = $review->order ?? null;
                    $buyer = $order ? $order->buyer : null;
                    return [
                        'id' => $review->id,
                        'reviewer' => $buyer?->name ?? 'Anonim',
                        'rating' => $review->rating ?? 0,
                        'comment' => $review->comment ?? '',
                        'created_at' => $review->created_at ? $review->created_at->diffForHumans() : 'Tidak diketahui',
                    ];
                })->toArray();
            }),

            'status' => $this->status ?? 'active',

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * @return array<int, string|null>
     */
    protected function galleryUrls(): array
    {
        $urls = [];
        if (!empty($this->images) && is_array($this->images)) {
            foreach ($this->images as $path) {
                if ($path) {
                    $urls[] = ImageUrl::normalize($path);
                }
            }
        }
        if (empty($urls) && $this->image) {
            $urls[] = ImageUrl::normalize($this->image);
        }

        return $urls;
    }
}
