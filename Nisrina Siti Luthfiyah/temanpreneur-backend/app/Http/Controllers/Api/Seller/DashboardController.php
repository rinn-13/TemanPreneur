<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Http\Resources\BusinessResource;
use App\Models\Business;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * GET /api/seller/dashboard
     * Main dashboard stats for seller
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $business = $user->business;

            if (!$business) {
                return response()->json([
                    'success' => false,
                    'message' => 'Belum ada toko terdaftar',
                ], 404);
            }

            if ($business->status !== 'approved' || !$business->is_verified) {
                return response()->json([
                    'success' => false,
                    'message' => 'Toko belum disetujui oleh admin',
                ], 403);
            }

            // Get all products for this business
            $productIds = Product::where('business_id', $business->id)->pluck('id');

            // Consider orders as "paid" when status moved past 'diproses'
            $paidStatuses = ['dikemas', 'diantarkan', 'selesai'];

            // Stats calculation (only count order_items that belong to paid orders)
            $totalProducts = Product::where('business_id', $business->id)->count();
            $totalSold = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereIn('order_items.product_id', $productIds)
                ->whereIn('orders.status', $paidStatuses)
                ->sum('order_items.quantity');

            $totalRevenue = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereIn('order_items.product_id', $productIds)
                ->whereIn('orders.status', $paidStatuses)
                ->sum('order_items.subtotal');

            $totalOrders = Order::whereIn('id', 
                OrderItem::whereIn('product_id', $productIds)->pluck('order_id')
            )->whereIn('status', $paidStatuses)->count();

            // Orders this month (only paid orders)
            $ordersThisMonth = Order::whereIn('id', 
                OrderItem::whereIn('product_id', $productIds)->pluck('order_id')
            )->whereIn('status', $paidStatuses)
             ->whereMonth('created_at', now()->month)
             ->whereYear('created_at', now()->year)
             ->count();

            // Revenue this month (order_items created this month and paid)
            $revenueThisMonth = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereIn('order_items.product_id', $productIds)
                ->whereIn('orders.status', $paidStatuses)
                ->whereMonth('order_items.created_at', now()->month)
                ->whereYear('order_items.created_at', now()->year)
                ->sum('order_items.subtotal');

            // Recent orders (last 10) - only paid orders
            $recentOrders = Order::whereIn('id', 
                OrderItem::whereIn('product_id', $productIds)->pluck('order_id')
            )->whereIn('status', $paidStatuses)
             ->latest()
             ->limit(10)
             ->with(['buyer', 'items.product'])
             ->get();

            // Revenue by category (paid orders only)
            $revenueByCategory = DB::table('products')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->join('order_items', 'products.id', '=', 'order_items.product_id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('products.business_id', $business->id)
                ->whereIn('orders.status', $paidStatuses)
                ->select(
                        DB::raw('IFNULL(categories.name, "Lainnya") as category'),
                        DB::raw('SUM(order_items.subtotal) as total')
                    )
                    ->groupBy(DB::raw('categories.name'))
                ->get();

            // Top products (by sold quantity) for this business considering paid orders only
            $topProducts = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->where('products.business_id', $business->id)
                ->whereIn('orders.status', $paidStatuses)
                ->select('products.id', 'products.name', 'products.price', DB::raw('SUM(order_items.quantity) as sold'), DB::raw('SUM(order_items.subtotal) as revenue'))
                ->groupBy('products.id', 'products.name', 'products.price')
                ->orderByDesc('sold')
                ->limit(5)
                ->get();

            // Blog count
            $totalBlogs = $business->blogs()->count();

            // Team members (if premium)
            $teamMembers = $business->is_premium 
                ? $business->teamMembers()->count()
                : 0;

            // Wallet balance
            $wallet = $user->wallet;
            $walletBalance = $wallet ? (float) $wallet->balance : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'business' => new BusinessResource($business),
                    'stats' => [
                        'total_products' => $totalProducts,
                        'total_sold' => $totalSold,
                        'total_revenue' => (float) $totalRevenue,
                        'total_orders' => $totalOrders,
                        'total_blogs' => $totalBlogs,
                        'team_members' => $teamMembers,
                        'wallet_balance' => $walletBalance,
                    ],
                    'monthly' => [
                        'orders' => $ordersThisMonth,
                        'revenue' => (float) $revenueThisMonth,
                    ],
                    'revenue_by_category' => $revenueByCategory->map(function ($item) {
                        return [
                            'category' => $item->category,
                            'total' => (float) $item->total,
                        ];
                    })->values(),
                    'top_products' => $topProducts->map(function ($product) {
                        return [
                            'id' => $product->id,
                            'name' => $product->name,
                            'price' => (float) $product->price,
                            'sold' => (int) $product->sold,
                            'revenue' => (float) $product->revenue,
                        ];
                    })->values(),
                    'recent_orders' => $recentOrders->map(function ($order) {
                        return [
                            'id' => $order->id,
                            'order_number' => $order->order_number ?? "ORD-{$order->id}",
                            'buyer_name' => $order->buyer->name ?? 'Unknown',
                            'total' => (float) $order->total_amount,
                            'status' => $order->status,
                            'items_count' => $order->items->count(),
                            'created_at' => $order->created_at->toIso8601String(),
                        ];
                    })->values(),
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Seller dashboard error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat dashboard',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/seller/analytics
     * Detailed analytics with time range
     */
    public function analytics(Request $request)
    {
        try {
            $request->validate([
                'period' => 'nullable|in:7days,30days,90days,1year,month,year',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date',
            ]);

            $user = auth()->user();
            $business = $user->business;

            if (!$business || $business->status !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Business not accessible',
                ], 404);
            }

            $period = $request->period ?? '30days';
            $startDate = $request->start_date
                ? Carbon::parse($request->start_date)->startOfDay()
                : $this->getPeriodStartDate($period)->startOfDay();

            $endDate = $request->end_date
                ? Carbon::parse($request->end_date)->endOfDay()
                : Carbon::now()->endOfDay();

            $productIds = Product::where('business_id', $business->id)->pluck('id');

            // Daily sales graph data (pakai tanggal pesanan, bukan created_at item sembarang)
            $dailySales = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->select(
                    DB::raw('DATE(orders.created_at) as date'),
                    DB::raw('COUNT(DISTINCT orders.id) as orders'),
                    DB::raw('SUM(order_items.quantity) as items_sold'),
                    DB::raw('SUM(order_items.subtotal) as revenue')
                )
                ->whereIn('order_items.product_id', $productIds)
                ->whereBetween('orders.created_at', [$startDate, $endDate])
                ->groupBy(DB::raw('DATE(orders.created_at)'))
                ->orderBy('date', 'asc')
                ->get();

            $revenueByCategory = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->whereIn('order_items.product_id', $productIds)
                ->whereBetween('orders.created_at', [$startDate, $endDate])
                ->select(
                    DB::raw('COALESCE(categories.name, "Tanpa Kategori") as category'),
                    DB::raw('SUM(order_items.subtotal) as total')
                )
                ->groupBy(DB::raw('COALESCE(categories.id, 0)'), DB::raw('COALESCE(categories.name, "Tanpa Kategori")'))
                ->orderByDesc('total')
                ->get();

            // Order status breakdown (hanya pesanan dalam rentang tanggal)
            $orderStatusBreakdown = Order::whereIn('id',
                OrderItem::whereIn('product_id', $productIds)->pluck('order_id')
            )
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select('status', DB::raw('COUNT(*) as count'))
             ->groupBy('status')
             ->get();

            // Conversion metrics
            $totalViews = 0; // This would come from tracking table if available
            $totalOrders = Order::whereIn('id',
                OrderItem::whereIn('product_id', $productIds)->pluck('order_id')
            )->whereBetween('created_at', [$startDate, $endDate])
             ->count();

            $totalRevenuePeriod = (float) $dailySales->sum('revenue');
            $totalItemsSoldPeriod = (int) $dailySales->sum('items_sold');
            $totalOrdersFromDaily = (int) $dailySales->sum('orders');

            $topProductsPeriod = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->where('products.business_id', $business->id)
                ->whereBetween('orders.created_at', [$startDate, $endDate])
                ->select(
                    'products.id',
                    'products.name',
                    'products.price',
                    DB::raw('SUM(order_items.quantity) as sold'),
                    DB::raw('SUM(order_items.subtotal) as revenue')
                )
                ->groupBy('products.id', 'products.name', 'products.price')
                ->orderByDesc('sold')
                ->limit(5)
                ->get();

            $wallet = $user->wallet;
            $walletBalance = $wallet ? (float) $wallet->balance : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'period' => $period,
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'stats' => [
                        'total_revenue' => $totalRevenuePeriod,
                        'total_sold' => $totalItemsSoldPeriod,
                        'total_orders' => $totalOrdersFromDaily,
                        'wallet_balance' => $walletBalance,
                    ],
                    'top_products' => $topProductsPeriod->map(function ($product) {
                        return [
                            'id' => $product->id,
                            'name' => $product->name,
                            'price' => (float) $product->price,
                            'sold' => (int) $product->sold,
                            'revenue' => (float) $product->revenue,
                        ];
                    })->values(),
                    'daily_sales' => $dailySales->map(function ($item) {
                        return [
                            'date' => $item->date,
                            'orders' => (int) $item->orders,
                            'items_sold' => (int) $item->items_sold,
                            'revenue' => (float) $item->revenue,
                        ];
                    })->values(),
                    'revenue_by_category' => $revenueByCategory->map(function ($item) {
                        return [
                            'category' => $item->category,
                            'total' => (float) $item->total,
                        ];
                    })->values(),
                    'order_status_breakdown' => $orderStatusBreakdown->map(function ($item) {
                        return [
                            'status' => $item->status,
                            'count' => (int) $item->count,
                        ];
                    })->values(),
                    'conversion_rate' => [
                        'total_orders' => $totalOrders,
                        'estimated_conversion' => $totalViews > 0 ? ($totalOrders / $totalViews * 100) : 0,
                    ],
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Analytics error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat analytics',
            ], 500);
        }
    }

    /**
     * Awal rentang waktu untuk preset filter analitik
     */
    private function getPeriodStartDate(string $period): Carbon
    {
        $now = now();
        return match ($period) {
            '7days' => $now->clone()->subDays(7),
            '30days' => $now->clone()->subDays(30),
            '90days' => $now->clone()->subDays(90),
            '1year' => $now->clone()->subYear(),
            'month' => $now->clone()->startOfMonth(),
            'year' => $now->clone()->startOfYear(),
            default => $now->clone()->subDays(30),
        };
    }

    /**
     * GET /api/seller/revenue
     * Revenue tracking with breakdown
     */
    public function revenue(Request $request)
    {
        try {
            $user = auth()->user();
            $business = $user->business;

            if (!$business || $business->status !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Business not accessible',
                ], 404);
            }

            $productIds = Product::where('business_id', $business->id)->pluck('id');

            // Today revenue
            $todayRevenue = OrderItem::whereIn('product_id', $productIds)
                ->whereDate('created_at', now())
                ->sum('subtotal');

            // This week
            $thisWeekRevenue = OrderItem::whereIn('product_id', $productIds)
                ->whereBetween('created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek(),
                ])
                ->sum('subtotal');

            // This month
            $thisMonthRevenue = OrderItem::whereIn('product_id', $productIds)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('subtotal');

            // All time
            $totalRevenue = OrderItem::whereIn('product_id', $productIds)
                ->sum('subtotal');

            return response()->json([
                'success' => true,
                'data' => [
                    'today' => (float) $todayRevenue,
                    'this_week' => (float) $thisWeekRevenue,
                    'this_month' => (float) $thisMonthRevenue,
                    'total' => (float) $totalRevenue,
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Revenue tracking error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch revenue data',
            ], 500);
        }
    }

    /**
     * GET /api/seller/orders
     * All orders for seller's products with filtering
     */
    public function orders(Request $request)
    {
        try {
            $request->validate([
                'status' => 'nullable|in:pending,processing,completed,cancelled',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:100',
            ]);

            $user = auth()->user();
            $business = $user->business;

            if (!$business || $business->status !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Business not accessible',
                ], 404);
            }

            $productIds = Product::where('business_id', $business->id)->pluck('id');
            $page = $request->page ?? 1;
            $perPage = $request->per_page ?? 15;

            $query = Order::whereIn('id',
                OrderItem::whereIn('product_id', $productIds)->pluck('order_id')
            )->with(['buyer', 'items.product']);

            if ($request->status) {
                $query->where('status', $request->status);
            }

            $orders = $query->latest()
                ->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'data' => $orders->items(),
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                    'last_page' => $orders->lastPage(),
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Orders fetch error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch orders',
            ], 500);
        }
    }
}
