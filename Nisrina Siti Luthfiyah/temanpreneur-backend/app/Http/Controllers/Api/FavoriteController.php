<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FavoriteController extends Controller
{
    /**
     * Fetch semua favorit user yang login
     * GET /api/favorites
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            $favorites = Favorite::where('user_id', $user->id)
                ->with('product.business')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json(['data' => $favorites]);
        } catch (\Exception $e) {
            Log::error('Get favorites error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch favorites'], 500);
        }
    }

    /**
     * Add produk ke favorit
     * PENTING: Menyimpan ke database secara PERMANEN
     * POST /api/favorites
     */
    public function store(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            $productId = $request->input('product_id');

            // Validasi product exists
            $product = Product::find($productId);
            if (!$product) {
                return response()->json(['error' => 'Product not found'], 404);
            }

            // Check if already favorited
            $existing = Favorite::where('user_id', $user->id)
                ->where('product_id', $productId)
                ->first();

            if ($existing) {
                return response()->json([
                    'message' => 'Produk sudah dalam favorit',
                    'is_duplicate' => true
                ], 409);
            }

            // Create favorite - SIMPAN KE DATABASE
            $favorite = Favorite::create([
                'user_id' => $user->id,
                'product_id' => $productId
            ]);

            return response()->json([
                'data' => $favorite->load('product'),
                'message' => 'Ditambahkan ke favorit'
            ], 201);

        } catch (\Exception $e) {
            Log::error('Add favorite error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to add favorite'], 500);
        }
    }

    /**
     * Remove produk dari favorit
     * DELETE /api/favorites/{productId}
     */
    public function destroy(Request $request, $productId)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            $deleted = Favorite::where('user_id', $user->id)
                ->where('product_id', $productId)
                ->delete();

            if (!$deleted) {
                return response()->json(['error' => 'Favorite not found'], 404);
            }

            return response()->json([
                'message' => 'Dihapus dari favorit'
            ]);

        } catch (\Exception $e) {
            Log::error('Remove favorite error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to remove favorite'], 500);
        }
    }

    /**
     * Check apakah produk sudah di-favorite
     * GET /api/favorites/check/{productId}
     */
    public function check(Request $request, $productId)
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return response()->json(['is_favorite' => false]);
            }

            $isFavorite = Favorite::where('user_id', $user->id)
                ->where('product_id', $productId)
                ->exists();

            return response()->json([
                'is_favorite' => $isFavorite
            ]);

        } catch (\Exception $e) {
            Log::error('Check favorite error: ' . $e->getMessage());
            return response()->json(['is_favorite' => false]);
        }
    }

    /**
     * Get count favorit
     * GET /api/favorites/count
     */
    public function count(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['count' => 0]);
            }

            $count = Favorite::where('user_id', $user->id)->count();

            return response()->json(['count' => $count]);

        } catch (\Exception $e) {
            Log::error('Count favorites error: ' . $e->getMessage());
            return response()->json(['count' => 0]);
        }
    }
}
