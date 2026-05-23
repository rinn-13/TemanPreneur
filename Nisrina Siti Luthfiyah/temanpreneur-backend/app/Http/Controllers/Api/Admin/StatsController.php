<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Business;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class StatsController extends Controller
{
    /**
     * Admin Dashboard Stats
     */
    public function index()
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['error' => 'Unauthenticated', 'message' => 'User is not authenticated'], 401);
            }

            if ($user->role !== 'admin') {
                return response()->json(['error' => 'Forbidden', 'message' => 'Admin access required'], 403);
            }

            // Business.status could be missing in older schema. Fallback to 0 if absent.
            $hasBusinessStatus = Schema::hasColumn('businesses', 'status');
            $hasOrderTotalPrice = Schema::hasColumn('orders', 'total_price');

            $totalUsers = User::count();
            $totalSellers = User::where('role', 'seller')->orWhere('role', 'seller_premium')->count();
            $totalBuyers = User::where('role', 'buyer')->count();
            $totalAdmin = User::where('role', 'admin')->count();

            $verifiedBusinesses = Business::where('is_verified', true)->count();
            $pendingBusinesses = $hasBusinessStatus ? Business::where('status', 'pending')->count() : 0;
            $approvedBusinesses = $hasBusinessStatus ? Business::where('status', 'approved')->count() : 0;
            $rejectedBusinesses = $hasBusinessStatus ? Business::where('status', 'rejected')->count() : 0;

            $totalProducts = (int) Product::count();
            $totalOrders = (int) Order::count();
            $totalRevenue = (float) ($hasOrderTotalPrice ? Order::sum('total_price') : 0);

            $recentOrders = collect();
            try {
                $recentOrders = Order::with(['buyer', 'product.business'])->latest()->limit(5)->get();
            } catch (\Exception $relationException) {
                Log::warning('Recent orders relation load failed: ' . $relationException->getMessage());
                $recentOrders = collect();
            }

            return response()->json([
                'users' => [
                    'total' => $totalUsers,
                    'sellers' => $totalSellers,
                    'buyers' => $totalBuyers,
                    'admin' => $totalAdmin,
                ],
                'businesses' => [
                    'total' => Business::count(),
                    'verified' => $verifiedBusinesses,
                    'pending' => $pendingBusinesses,
                    'approved' => $approvedBusinesses,
                    'rejected' => $rejectedBusinesses,
                ],
                'products' => ['total' => $totalProducts],
                'orders' => ['total' => $totalOrders, 'revenue' => round($totalRevenue, 2)],
                'totalUsers' => $totalUsers,
                'totalProducts' => $totalProducts,
                'totalOrders' => $totalOrders,
                'totalRevenue' => round($totalRevenue, 2),
                'verifiedBusinesses' => $verifiedBusinesses,
                'recentOrders' => $recentOrders->toArray(),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Admin stats error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return response()->json([
                'error' => 'Failed to fetch stats',
                'message' => 'Something went wrong while fetching admin stats',
            ], 500);
        }
    }

    /**
     * Seller Dashboard Stats
     */
    public function sellerStats(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['error' => 'Unauthenticated', 'message' => 'User tidak terautentikasi. Silakan login terlebih dahulu.'], 401);
            }

            $business = Business::where('user_id', auth()->id())->first();
            if (!$business) {
                return response()->json([
                    'totalProducts' => 0,
                    'pendingOrders' => 0,
                    'completedOrders' => 0,
                    'monthlyRevenue' => 0,
                    'totalRevenue' => 0,
                    'averageRating' => 0,
                    'totalReviews' => 0,
                    'businessName' => null,
                    'businessStatus' => null,
                    'isPremium' => false,
                    'message' => 'User belum memiliki bisnis. Silakan buat bisnis terlebih dahulu.',
                ], 200);
            }

            $productIds = $business->products()->pluck('id');
            $totalProducts = $business->products()->count();

            // Hitung pending orders
            $pendingOrders = 0;
            if ($productIds->isNotEmpty()) {
                $pendingOrders = Order::whereHas('items', function ($q) use ($productIds) {
                    $q->whereIn('order_items.product_id', $productIds);
                })->whereIn('status', ['diproses', 'dikemas', 'diantarkan'])->count();
            }

            // Hitung completed orders
            $completedOrders = 0;
            if ($productIds->isNotEmpty()) {
                $completedOrders = Order::whereHas('items', function ($q) use ($productIds) {
                    $q->whereIn('order_items.product_id', $productIds);
                })->where('status', 'selesai')->count();
            }

            // Hitung monthly revenue
            $monthlyRevenue = 0;
            if ($productIds->isNotEmpty()) {
                $monthlyRevenue = OrderItem::whereIn('order_items.product_id', $productIds)
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->where('orders.created_at', '>=', now()->startOfMonth())
                    ->sum('order_items.subtotal');
            }

            // Hitung total revenue
            $totalRevenue = 0;
            if ($productIds->isNotEmpty()) {
                $totalRevenue = OrderItem::whereIn('order_items.product_id', $productIds)->sum('subtotal');
            }

            $reviewsQuery = \App\Models\Review::query();
            if ($productIds->isNotEmpty()) {
                $reviewsQuery->whereHas('order.items', function ($q) use ($productIds) {
                    $q->whereIn('product_id', $productIds);
                });
            } else {
                $reviewsQuery->whereRaw('1 = 0');
            }

            $averageRating = (clone $reviewsQuery)->avg('rating') ?? 0;
            $totalReviews = (clone $reviewsQuery)->count();

            return response()->json([
                'totalProducts' => $totalProducts,
                'pendingOrders' => $pendingOrders,
                'completedOrders' => $completedOrders,
                'monthlyRevenue' => round($monthlyRevenue, 2),
                'totalRevenue' => round($totalRevenue, 2),
                'averageRating' => round($averageRating, 2),
                'totalReviews' => $totalReviews,
            'businessName' => $business->name ?? 'Usaha Saya',
                'businessStatus' => $business->status ?? 'pending',
                'isPremium' => (bool) ($business->is_premium ?? false),
            ], 200);
        } catch (\Exception $e) {
            // Log error dengan detail lengkap untuk debugging
            Log::error('Seller stats error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);

            // Return 200 dengan default values daripada 500 agar frontend tidak crash
            return response()->json([
                'totalProducts' => 0,
                'pendingOrders' => 0,
                'completedOrders' => 0,
                'monthlyRevenue' => 0.00,
                'totalRevenue' => 0.00,
                'averageRating' => 0.00,
                'totalReviews' => 0,
                'businessName' => null,
                'businessStatus' => null,
                'isPremium' => false,
                'error' => 'Failed to fetch seller stats',
                'message' => 'Terjadi kesalahan saat memuat data statistik. Silakan coba lagi nanti.',
            ], 200);
        }
    }
}
