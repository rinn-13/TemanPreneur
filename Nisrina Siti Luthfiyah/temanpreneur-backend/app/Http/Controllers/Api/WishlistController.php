<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WishlistController extends Controller
{
    /**
     * Get user's wishlist items
     */
    public function index(Request $request)
    {
        try {
            $userId = auth()->id();
            
            if (!$userId) {
                return response()->json([
                    'error' => 'Unauthenticated',
                    'message' => 'Please login first',
                ], 401);
            }

            $wishlist = Wishlist::where('user_id', $userId)
                ->with('product')
                ->latest()
                ->get();

            return response()->json([
                'data' => $wishlist->map(fn($w) => [
                    'id' => $w->id,
                    'product_id' => $w->product_id,
                    'product_name' => $w->product?->name ?? 'Unknown',
                    'product_price' => $w->product?->price ?? 0,
                    'product_image' => $w->product?->image ?? null,
                    'added_at' => $w->created_at?->toIso8601String(),
                ])->toArray() ?? [],
                'count' => $wishlist->count(),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Wishlist fetch error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch wishlist',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Add product to wishlist
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|integer|exists:products,id',
            ]);

            $userId = auth()->id();
            if (!$userId) {
                return response()->json([
                    'error' => 'Unauthenticated',
                    'message' => 'Please login first',
                ], 401);
            }

            // Prevent duplicate
            $existing = Wishlist::where('user_id', $userId)
                ->where('product_id', $request->product_id)
                ->first();

            if ($existing) {
                return response()->json([
                    'error' => 'Already in wishlist',
                    'message' => 'This product is already in your wishlist',
                    'data' => $existing,
                ], 409);  // Conflict
            }

            $wishlist = Wishlist::create([
                'user_id' => $userId,
                'product_id' => $request->product_id,
            ]);

            return response()->json([
                'message' => 'Added to wishlist successfully',
                'data' => $wishlist,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Wishlist store error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to add to wishlist',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove product from wishlist
     */
    public function destroy($product_id)
    {
        try {
            $userId = auth()->id();
            if (!$userId) {
                return response()->json([
                    'error' => 'Unauthenticated',
                    'message' => 'Please login first',
                ], 401);
            }

            $deleted = Wishlist::where('user_id', $userId)
                ->where('product_id', $product_id)
                ->delete();

            if (!$deleted) {
                return response()->json([
                    'message' => 'Not found in wishlist',
                ], 404);
            }

            return response()->json([
                'message' => 'Removed from wishlist successfully',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Wishlist destroy error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to remove from wishlist',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
