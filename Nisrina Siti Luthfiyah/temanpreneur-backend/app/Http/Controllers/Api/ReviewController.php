<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Product;
use App\Models\Order;
use App\Utils\ImageUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
{
    /**
     * Get current user's reviews
     */
public function my(Request $request)
{
    try {
        $userId = auth()->id();

        if (!$userId) {
            return response()->json([
                'error' => 'Unauthenticated',
                'message' => 'Please login first',
            ], 401);
        }

        $reviews = Review::whereHas('order', fn($q) => $q->where('buyer_id', $userId))
            ->with('order.items.product.business')
            ->latest()
            ->get();

        $payload = $reviews->map(function ($review) {
            $product = optional($review->order->items->first())->product;
            $business = optional($product)->business;

            return [
                'id' => $review->id,
                'order_id' => $review->order_id,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'product_name' => $product->name ?? '-',
                'business_name' => $business->name ?? '-',
                'created_at' => $review->created_at?->format('d M Y'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $payload
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed',
            'message' => $e->getMessage()
        ], 500);
    }
}
    /**
     * List reviews for a product
     */
    public function product($productId)
    {
        try {
            $product = Product::findOrFail($productId);

            $reviews = Review::whereHas('order', fn($q) => $q->where('product_id', $productId))
                ->with(['order.buyer', 'order.product'])
                ->latest()
                ->paginate(10);

            $payload = $reviews->map(function ($review) {
                $buyer = $review->order->buyer ?? null;

                return [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'komentar' => $review->comment,
                    'waktu' => $review->created_at->diffForHumans(),
                    'buyer' => [
                        'id' => $buyer?->id,
                        'name' => $buyer?->name ?? 'Anonim',
                        'photo' => ImageUrl::normalize($buyer?->photo),
                    ],
                    'product' => [
                        'id' => $review->order->product->id ?? null,
                        'name' => $review->order->product->name ?? null,
                    ],
                ];
            });

            return response()->json([
                'data' => $payload,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'average_rating' => $product->reviews()->avg('rating') ?? 0,
                ],
                'pagination' => [
                    'total' => $reviews->total() ?? 0,
                    'per_page' => $reviews->perPage() ?? 10,
                    'current_page' => $reviews->currentPage() ?? 1,
                ],
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Product not found',
                'message' => 'The product does not exist',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Review product error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch reviews',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Create a review for a product
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'order_id' => 'required|integer|exists:orders,id',
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:1000',
            ]);

            $userId = auth()->id();
            if (!$userId) {
                return response()->json([
                    'error' => 'Unauthenticated',
                    'message' => 'Please login first',
                ], 401);
            }

            $order = Order::where('id', $request->order_id)
                ->where('buyer_id', $userId)
                ->first();

            if (!$order) {
                return response()->json([
                    'error' => 'Invalid order',
                    'message' => 'Order tidak ditemukan atau bukan milik Anda.',
                ], 403);
            }

            // Only allow review if order has been delivered/completed
            if ($order->status !== 'selesai') {
                return response()->json([
                    'error' => 'Order not delivered',
                    'message' => 'Anda hanya dapat memberikan ulasan setelah pesanan diterima (status selesai).',
                ], 422);
            }

            if ($order->review) {
                return response()->json([
                    'error' => 'Already reviewed',
                    'message' => 'Pesanan ini sudah diberikan ulasan.',
                    'data' => $order->review,
                ], 409);
            }

            $review = Review::create([
                'order_id' => $request->order_id,
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]);

            return response()->json([
                'message' => 'Review created successfully',
                'data' => $review,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Review store error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to create review',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, Review $review)
    {
        try {
            $userId = auth()->id();
            if (!$userId || $review->order->buyer_id !== $userId) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'Anda tidak memiliki akses untuk mengubah ulasan ini.',
                ], 403);
            }

            $request->validate([
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:1000',
            ]);

            $review->update([
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]);

            return response()->json([
                'message' => 'Review updated successfully',
                'data' => $review,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Review update error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to update review',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Review $review)
    {
        try {
            $userId = auth()->id();
            if (!$userId || $review->order->buyer_id !== $userId) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'Anda tidak memiliki akses untuk menghapus ulasan ini.',
                ], 403);
            }

            $review->delete();
            return response()->json(['message' => 'Review deleted successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Review delete error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to delete review',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get seller/business rating stats
     */
    public function sellerStats($businessId)
    {
        try {
            $business = \App\Models\Business::findOrFail($businessId);
            
            $stats = $business->getStats();
            
            return response()->json([
                'data' => [
                    'business_id' => $business->id,
                    'business_name' => $business->name,
                    'rating' => $stats['rating'],
                    'rating_count' => $stats['rating_count'],
                    'total_sales' => $stats['total_sales'],
                    'total_revenue' => $stats['total_revenue'],
                ],
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Business not found',
                'message' => 'Bisnis tidak ditemukan.',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Seller stats error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch seller stats',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get top sellers/businesses by rating
     */
    public function topSellers($limit = 5)
    {
        try {
            $businesses = \App\Models\Business::where('is_verified', true)
                ->where('status', 'approved')
                ->with(['user', 'products.category'])
                ->get()
                ->map(function ($business) {
                    $stats = $business->getStats();
                    $categoryNames = $business->products
                        ->pluck('category.name')
                        ->filter()
                        ->unique()
                        ->values()
                        ->take(4)
                        ->all();

                    return [
                        'id' => $business->id,
                        'name' => $business->name,
                        'description' => $business->description,
                        'user_name' => $business->user?->name,
                        'user_class' => $business->user?->class ?: $business->user?->school,
                        'logo' => ImageUrl::normalize($business->logo),
                        'theme_color' => $business->theme_color ?: '#10b981',
                        'rating' => $stats['rating'],
                        'rating_count' => $stats['rating_count'],
                        'total_sales' => $stats['total_sales'],
                        'total_revenue' => $stats['total_revenue'],
                        'is_premium' => $business->is_premium,
                        'category_labels' => $categoryNames,
                    ];
                })
                ->sort(function ($a, $b) {
                    if (($a['total_sales'] ?? 0) !== ($b['total_sales'] ?? 0)) {
                        return ($b['total_sales'] ?? 0) <=> ($a['total_sales'] ?? 0);
                    }
                    return ($b['rating'] ?? 0) <=> ($a['rating'] ?? 0);
                })
                ->values()
                ->take($limit > 0 ? (int) $limit : 5);

            return response()->json([
                'data' => $businesses,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Top sellers error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch top sellers',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function index()
    {
        $reviews = \App\Models\Review::with(['user', 'product'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }

    
}

