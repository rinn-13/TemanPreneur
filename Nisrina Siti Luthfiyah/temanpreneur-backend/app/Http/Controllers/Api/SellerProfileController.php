<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Business;
use App\Models\Product;
use App\Models\Blog;
use Illuminate\Support\Facades\Log;

class SellerProfileController extends Controller
{
    /**
     * Get authenticated seller's complete profile
     * GET /api/seller/profile
     * 
     * Returns:
     * - User data (seller profile)
     * - Business data (store profile)
     * - Products (store products)
     * - Blogs (store blogs)
     * - Statistics
     */
    public function profileSync(Request $request)
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak terautentikasi'
                ], 401);
            }

            // Check if user has a business
            $business = $user->business;
            
            if (!$business) {
                return response()->json([
                    'success' => true,
                    'message' => 'User belum memiliki toko',
                    'data' => [
                        'profile' => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'phone' => $user->phone,
                            'photo' => $user->photo,
                            'role' => $user->role,
                            'is_verified' => $user->is_verified,
                        ],
                        'business' => null,
                        'products' => [],
                        'blogs' => [],
                        'stats' => null
                    ]
                ], 200);
            }

            // Load business with relations
            $business = $business->load('products', 'blogs');

            // Get products count, blogs count
            $products = $business->products()->where('status', 'active')->get();
            $blogs = $business->blogs()->get();

            // Calculate statistics
            $stats = [
                'total_products' => $business->products()->count(),
                'total_blogs' => $blogs->count(),
                'total_sold' => $business->products()->sum('total_sold'),
                'rating' => $business->getAverageRating(),
                'rating_count' => $business->getRatingCount(),
                'is_verified' => $business->is_verified,
                'is_premium' => $business->is_premium,
            ];

            // Sync profile data
            return response()->json([
                'success' => true,
                'message' => 'Profil seller berhasil disinkronkan',
                'data' => [
                    'profile' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'photo' => $user->photo,
                        'address' => $user->address,
                        'role' => $user->role,
                        'is_verified' => $user->is_verified,
                    ],
                    'business' => [
                        'id' => $business->id,
                        'name' => $business->name,
                        'description' => $business->description,
                        'category' => $business->category,
                        'phone' => $business->phone,
                        'address' => $business->address,
                        'logo' => $business->logo,
                        'banner' => $business->banner,
                        'is_verified' => $business->is_verified,
                        'is_premium' => $business->is_premium,
                        'status' => $business->status,
                        'theme_color' => $business->theme_color,
                        'created_at' => $business->created_at,
                    ],
                    'products' => $products->map(function ($product) {
                        return [
                            'id' => $product->id,
                            'name' => $product->name,
                            'price' => $product->price,
                            'image' => $product->image,
                            'total_sold' => $product->total_sold,
                            'status' => $product->status,
                        ];
                    })->toArray(),
                    'blogs' => $blogs->map(function ($blog) {
                        return [
                            'id' => $blog->id,
                            'title' => $blog->title,
                            'slug' => $blog->slug,
                            'image' => $blog->image,
                            'category' => $blog->category,
                            'created_at' => $blog->created_at,
                        ];
                    })->toArray(),
                    'stats' => $stats
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Seller profile sync error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mensinkronkan profil seller',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get public seller profile (store profile page)
     * GET /api/seller/public-profile/{userId}
     * 
     * Anyone can access this to view a seller's store
     */
    public function publicProfile($userId)
    {
        try {
            $user = User::find($userId);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seller tidak ditemukan'
                ], 404);
            }

            $business = $user->business;
            
            if (!$business) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seller belum memiliki toko'
                ], 404);
            }

            // Only show if business is verified and active
            if (!$business->is_verified || !in_array($business->status, ['approved', 'active'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Toko tidak aktif atau belum diverifikasi'
                ], 403);
            }

            // Load business with relations
            $products = $business->products()->where('status', 'active')->get();
            $blogs = $business->blogs()->get();

            // Calculate statistics
            $stats = [
                'total_products' => $business->products()->where('status', 'active')->count(),
                'total_blogs' => $blogs->count(),
                'total_sold' => $business->products()->sum('total_sold'),
                'rating' => $business->getAverageRating(),
                'rating_count' => $business->getRatingCount(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Profil toko berhasil dimuat',
                'data' => [
                    'seller' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'photo' => $user->photo,
                    ],
                    'business' => [
                        'id' => $business->id,
                        'name' => $business->name,
                        'description' => $business->description,
                        'category' => $business->category,
                        'phone' => $business->phone,
                        'address' => $business->address,
                        'logo' => $business->logo,
                        'banner' => $business->banner,
                        'theme_color' => $business->theme_color,
                    ],
                    'products' => $products->map(function ($product) {
                        return [
                            'id' => $product->id,
                            'name' => $product->name,
                            'price' => $product->price,
                            'image' => $product->image,
                            'total_sold' => $product->total_sold,
                        ];
                    })->toArray(),
                    'blogs' => $blogs->map(function ($blog) {
                        return [
                            'id' => $blog->id,
                            'title' => $blog->title,
                            'slug' => $blog->slug,
                            'image' => $blog->image,
                            'category' => $blog->category,
                            'created_at' => $blog->created_at,
                        ];
                    })->toArray(),
                    'stats' => $stats
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Public profile error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat profil toko',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
