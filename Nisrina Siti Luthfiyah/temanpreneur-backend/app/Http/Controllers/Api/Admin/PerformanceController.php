<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\PerformanceExport;

class PerformanceController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->start_date
            ? Carbon::parse($request->start_date)
            : now()->startOfMonth();

        $end = $request->end_date
            ? Carbon::parse($request->end_date)
            : now();

        $ordersQuery = Order::whereBetween('created_at', [$start, $end]);
        $revenueField = Schema::hasColumn('orders', 'total_amount') ? 'total_amount' : 'total_price';

        $salesByCategory = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->select(
                DB::raw('COALESCE(categories.id, 0) as category_id'),
                DB::raw('COALESCE(categories.name, "Tanpa Kategori") as category_name'),
                DB::raw('COALESCE(categories.slug, "uncategorized") as category_slug'),
                DB::raw('SUM(order_items.subtotal) as revenue'),
                DB::raw('SUM(order_items.quantity) as qty')
            )
            ->groupByRaw('COALESCE(categories.id, 0), COALESCE(categories.name, "Tanpa Kategori"), COALESCE(categories.slug, "uncategorized")')
            ->orderByDesc('revenue')
            ->get()
            ->map(fn ($row) => [
                'id' => (int) $row->category_id,
                'name' => $row->category_name,
                'slug' => $row->category_slug,
                'revenue' => (float) $row->revenue,
                'quantity' => (int) $row->qty,
            ])
            ->values()
            ->toArray();

        $revenueByDay = Order::selectRaw('DATE(created_at) as date, SUM('.$revenueField.') as revenue, COUNT(*) as orders')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($item) => [
                'date' => $item->date,
                'revenue' => (float) $item->revenue,
                'orders' => (int) $item->orders,
            ])
            ->toArray();

        $data = [
            'total_users'    => User::count(),
            'total_orders'   => $ordersQuery->count(),
            'total_revenue'  => (float) $ordersQuery->sum($revenueField),
            'total_products' => Product::count(),

            /** @deprecated Gunakan revenue_by_day untuk grafik omzet */
            'orders_per_day' => Order::selectRaw('DATE(created_at) as date, COUNT(*) as total')
                ->whereBetween('created_at', [$start, $end])
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(fn($item) => ['date' => $item->date, 'total' => $item->total])
                ->toArray(),

            'revenue_by_day' => $revenueByDay,
            'sales_by_category' => $salesByCategory,
        ];

        return response()->json($data);
    }

    public function export(Request $request)
    {
        $type = $request->type; // excel | pdf

        $data = $this->index($request)->getData(true);

        if ($type === 'excel') {
            return Excel::download(new PerformanceExport($data), 'laporan.xlsx');
        }

        if ($type === 'pdf') {
            $pdf = Pdf::loadView('exports.performance', ['data' => $data]);
            return $pdf->download('laporan.pdf');
        }

        return response()->json(['message' => 'Format tidak valid'], 400);
    }
}