<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CancelOrderRequest;
use App\Http\Requests\CheckoutRequest;
use App\Http\Requests\UpdateOrderTrackingRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderTrackingResource;
use App\Http\Controllers\Api\CartController;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderTracking;
use App\Models\Notification;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Get orders based on user role
     * GET /api/orders
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            $currentRole = $this->getEffectiveRole($request);

            if ($currentRole === 'admin') {
                // Admin sees all orders
                $orders = Order::with('items.product.business', 'items.product.category', 'buyer', 'trackings', 'orderGroup')
                    ->latest()
                    ->paginate(20);
            } elseif ($currentRole === 'seller' || $currentRole === 'seller_premium') {
                // Seller sees only orders for their products
                $orders = Order::whereHas('items.product.business', fn($q) => $q->where('user_id', $user->id))
                    ->with('items.product.business', 'items.product.category', 'buyer', 'trackings', 'orderGroup')
                    ->latest()
                    ->paginate(20);
            } else {
                // Buyer sees only their own orders
                $orders = Order::where('buyer_id', $user->id)
                    ->with('items.product.business', 'items.product.category', 'trackings', 'orderGroup')
                    ->latest()
                    ->paginate(20);
            }

            return response()->json([
                'success' => true,
                'message' => 'Daftar pesanan berhasil diambil',
                'data' => OrderResource::collection($orders)->response()->getData(true)['data'],
                'meta' => [
                    'total' => $orders->total(),
                    'per_page' => $orders->perPage(),
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching orders: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil daftar pesanan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single order details
     * GET /api/orders/:id
     */
    private function getEffectiveRole(Request $request): string
    {
        $user = $request->user();
        $activeRole = strtolower((string) $request->header('X-Active-Role', ''));

        if ($activeRole && $user->hasRole($activeRole)) {
            return $activeRole;
        }

        if ($user->hasRole('admin')) {
            return 'admin';
        }

        if ($user->hasRole('seller_premium')) {
            return 'seller_premium';
        }

        if ($user->hasRole('seller')) {
            return 'seller';
        }

        if ($user->hasRole('buyer')) {
            return 'buyer';
        }

        return $user->role ?? 'buyer';
    }

    public function show(Request $request, Order $order)
    {
        try {
            $user = $request->user();

            // Authorization check
            $isBuyer = $order->buyer_id === $user->id;
            $isSeller = $order->items()
                ->whereHas('product.business', fn($q) => $q->where('user_id', $user->id))
                ->exists();
            $isAdmin = $user->role === 'admin';

            if (!$isBuyer && !$isSeller && !$isAdmin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke pesanan ini'
                ], 403);
            }

            $order->load('items.product.business', 'buyer', 'trackings.updater', 'orderGroup');

            return response()->json([
                'success' => true,
                'message' => 'Detail pesanan berhasil diambil',
                'data' => new OrderResource($order)
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail pesanan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create order directly via /api/orders
     * POST /api/orders
     */
    public function store(CheckoutRequest $request)
    {
        return app(CartController::class)->checkout($request);
    }

    /**
     * Get order tracking history
     * GET /api/orders/:id/track
     */
    public function track(Request $request, Order $order)
    {
        try {
            $user = $request->user();

            // Authorization check
            $isBuyer = $order->buyer_id === $user->id;
            $isSeller = $order->items()
                ->whereHas('product.business', fn($q) => $q->where('user_id', $user->id))
                ->exists();
            $isAdmin = $user->role === 'admin';

            if (!$isBuyer && !$isSeller && !$isAdmin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke riwayat pesanan ini'
                ], 403);
            }

            $trackings = $order->trackings()
                ->with('updater')
                ->latest()
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Riwayat pesanan berhasil diambil',
                'data' => OrderTrackingResource::collection($trackings)
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching order tracking: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil riwayat pesanan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update order status (seller or admin only)
     * PATCH /api/orders/:id/status
     */
    public function updateStatus(UpdateOrderTrackingRequest $request, Order $order)
    {
        try {
            $user = $request->user();

            // Authorization check
            $isBuyer = $order->buyer_id === $user->id;
            $isSeller = $order->items()
                ->whereHas('product.business', fn($q) => $q->where('user_id', $user->id))
                ->exists();
            $isAdmin = $user->role === 'admin';

            if (!$isBuyer && !$isSeller && !$isAdmin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk memperbarui pesanan ini'
                ], 403);
            }

            // Validate status transition based on user role
            $currentStatus = $order->status;
            $newStatus = $request->status;

            // Define allowed transitions per role
            $allTransitions = [
                'pending' => ['diproses', 'dibatalkan'],
                'diproses' => ['dikemas', 'dibatalkan'],
                'dikemas' => ['diantarkan', 'dibatalkan'],
                'diantarkan' => ['selesai'],
                'selesai' => [],
                'dibatalkan' => []
            ];

            // Seller transitions: seller can process orders up to shipment
            $sellerTransitions = [
                'pending' => ['diproses', 'dibatalkan'],
                'diproses' => ['dikemas', 'dibatalkan'],
                'dikemas' => ['diantarkan', 'dibatalkan'],
                'diantarkan' => [],
                'selesai' => [],
                'dibatalkan' => []
            ];

            // Buyer transitions: buyer can cancel before shipment and confirm receipt after delivery
            $buyerTransitions = [
                'pending' => ['dibatalkan'],
                'diproses' => ['dibatalkan'],
                'dikemas' => ['dibatalkan'],
                'diantarkan' => ['selesai'],
                'selesai' => [],
                'dibatalkan' => []
            ];

            // Determine allowed transitions based on role
            if ($isSeller && !$isAdmin) {
                $allowedTransitions = $sellerTransitions;
            } elseif ($isBuyer && !$isAdmin) {
                $allowedTransitions = $buyerTransitions;
            } else {
                // Admin can do any transition
                $allowedTransitions = $allTransitions;
            }

            if (!isset($allowedTransitions[$currentStatus]) || 
                !in_array($newStatus, $allowedTransitions[$currentStatus])) {
                return response()->json([
                    'success' => false,
                    'message' => "Transisi dari status '{$currentStatus}' ke '{$newStatus}' tidak diizinkan untuk role Anda",
                    'current_status' => $currentStatus,
                    'allowed_next' => $allowedTransitions[$currentStatus] ?? []
                ], 422);
            }

            DB::beginTransaction();
            try {
                $previousStatus = $order->status;
                $updateData = ['status' => $newStatus];

                if ($newStatus === 'dibatalkan') {
                    $updateData['cancellation_reason'] = $request->input('notes') ?? $request->input('reason');
                    $updateData['cancelled_at'] = now();
                    $updateData['cancelled_by'] = $user->id;
                }

                $order->update($updateData);

                if ($newStatus === 'dibatalkan') {
                    $this->restoreOrderStock($order, $previousStatus);
                }

                // If order completed, increment product total_sold
                if ($newStatus === 'selesai') {
                    foreach ($order->items()->get() as $it) {
                        try {
                            $product = $it->product;
                            if ($product) {
                                $product->increment('total_sold', $it->quantity);
                            }
                        } catch (\Exception $e) {
                            Log::warning('Failed to increment total_sold for product in order ' . $order->id . ': ' . $e->getMessage());
                        }
                    }
                }

                // Create tracking record
                OrderTracking::create([
                    'order_id' => $order->id,
                    'status' => $newStatus,
                    'updated_by' => $user->id,
                ]);

                // Notify buyer about status change
                $statusLabels = [
                    'diproses' => 'Diproses',
                    'dikemas' => 'Dikemas',
                    'diantarkan' => 'Diantarkan',
                    'selesai' => 'Selesai',
                    'dibatalkan' => 'Dibatalkan'
                ];

                Notification::create([
                    'user_id' => $order->buyer_id,
                    'type' => 'order_status_changed',
                    'title' => 'Status Pesanan Berubah ✨',
                    'message' => "Pesanan Anda sekarang: {$statusLabels[$newStatus]}",
                    'related_id' => $order->id,
                    'is_read' => false,
                ]);

                if ($isBuyer && $newStatus === 'dibatalkan') {
                    $sellerUsers = $order->items()
                        ->with('product.business.user')
                        ->get()
                        ->pluck('product.business.user')
                        ->filter()
                        ->unique('id');

                    foreach ($sellerUsers as $seller) {
                        Notification::create([
                            'user_id' => $seller->id,
                            'type' => 'order_cancelled',
                            'title' => 'Pesanan Dibatalkan',
                            'message' => "Pembeli telah membatalkan pesanan #{$order->id}. Silakan cek pesanan Anda.",
                            'related_id' => $order->id,
                            'is_read' => false,
                        ]);
                    }
                }

                DB::commit();

                $order->load('items.product.business', 'buyer', 'trackings');

                return response()->json([
                    'success' => true,
                    'message' => "Status pesanan berhasil diperbarui ke '{$newStatus}'",
                    'data' => new OrderResource($order)
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating order status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status pesanan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel order (buyer only) with reason
     * POST /api/orders/:id/cancel
     */
    public function cancel(CancelOrderRequest $request, Order $order)
    {
        try {
            $user = $request->user();

            if (! in_array($order->status, Order::CANCELLABLE_STATUSES, true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesanan tidak dapat dibatalkan pada status saat ini',
                    'current_status' => $order->status,
                    'allowed_cancel_statuses' => Order::CANCELLABLE_STATUSES,
                ], 422);
            }

            DB::beginTransaction();

            $previousStatus = $order->status;

            $order->update([
                'status' => 'dibatalkan',
                'cancellation_reason' => $request->reason,
                'cancelled_at' => now(),
                'cancelled_by' => $user->id,
            ]);

            $this->restoreOrderStock($order, $previousStatus);

            OrderTracking::create([
                'order_id' => $order->id,
                'status' => 'dibatalkan',
                'updated_by' => $user->id,
            ]);

            $sellerUsers = $order->items()
                ->with('product.business.user')
                ->get()
                ->pluck('product.business.user')
                ->filter()
                ->unique('id');

            foreach ($sellerUsers as $seller) {
                Notification::create([
                    'user_id' => $seller->id,
                    'type' => 'order_cancelled',
                    'title' => 'Pesanan Dibatalkan',
                    'message' => "Pembeli membatalkan pesanan #{$order->id}. Alasan: {$request->reason}",
                    'related_id' => $order->id,
                    'is_read' => false,
                ]);
            }

            Notification::create([
                'user_id' => $order->buyer_id,
                'type' => 'order_cancelled',
                'title' => 'Pesanan Dibatalkan',
                'message' => 'Pesanan Anda telah berhasil dibatalkan. Stok produk telah dikembalikan.',
                'related_id' => $order->id,
                'is_read' => false,
            ]);

            DB::commit();

            $order->load('items.product.business', 'buyer', 'trackings');

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibatalkan',
                'data' => new OrderResource($order),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error cancelling order: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan pesanan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Restore product stock when order is cancelled
     */
    private function restoreOrderStock(Order $order, ?string $previousStatus = null): void
    {
        $prev = $previousStatus ?? $order->getOriginal('status') ?? $order->status;

        if ($prev === 'dibatalkan') {
            return;
        }

        foreach ($order->items()->with('product')->get() as $item) {
            if ($item->product) {
                $item->product->increment('stock', $item->quantity);
            }
        }
    }

    /**
     * Download invoice as PDF
     * GET /api/orders/:id/invoice/pdf
     */
    public function invoicePdf(Request $request, Order $order)
    {
        try {
            $user = $request->user();

            if ($order->buyer_id !== $user->id && $user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke invoice ini',
                ], 403);
            }

            $order->load('items.product.business', 'buyer', 'business');

            $subtotal = (float) $order->items()->sum('subtotal');
            $shippingCost = (float) $order->shipping_cost;
            $totalAmount = $subtotal + $shippingCost;

            $invoiceData = [
                'order' => $order,
                'order_number' => '#ORD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
                'buyer' => $order->buyer,
                'business' => $order->business ?? $order->items->first()?->product?->business,
                'items' => $order->items,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'total_amount' => $totalAmount,
                'generated_at' => now()->format('d M Y H:i'),
            ];

            $pdf = Pdf::loadView('exports.invoice', $invoiceData)
                ->setPaper('a4', 'portrait');

            $filename = 'struk-' . str_pad($order->id, 6, '0', STR_PAD_LEFT) . '.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Error generating invoice PDF: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat PDF struk',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get order invoice
     * GET /api/orders/:id/invoice
     */
    public function invoice(Request $request, Order $order)
    {
        try {
            $user = $request->user();

            // Only buyer can view their own invoice
            if ($order->buyer_id !== $user->id && $user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke invoice ini'
                ], 403);
            }

            $subtotal = (float) $order->items()->sum('subtotal');
            $shippingCost = (float) $order->shipping_cost;
            $totalAmount = $subtotal + $shippingCost;

            return response()->json([
                'success' => true,
                'message' => 'Invoice berhasil diambil',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => '#ORD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
                    'buyer' => [
                        'name' => $order->buyer->name,
                        'email' => $order->buyer->email,
                        'phone' => $order->buyer->phone,
                    ],
                    'shipping' => [
                        'name' => $order->shipping_name,
                        'address' => $order->shipping_address,
                        'phone' => $order->shipping_phone,
                        'notes' => $order->buyer_notes,
                    ],
                    'items' => $order->items()->with('product')->get()->map(fn($item) => [
                        'product_name' => $item->product->name,
                        'quantity' => $item->quantity,
                        'price' => (float) $item->price,
                        'subtotal' => (float) $item->subtotal,
                    ]),
                    'subtotal' => $subtotal,
                    'shipping_cost' => $shippingCost,
                    'total_amount' => $totalAmount,
                    'payment_method' => $order->payment_method,
                    'status' => $order->status,
                    'created_at' => $order->created_at->toIso8601String(),
                    'updated_at' => $order->updated_at->toIso8601String(),
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error generating invoice: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat invoice',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Confirm payment for an order (called by buyer after successful payment)
     * POST /api/orders/:id/confirm-payment
     */
    public function confirmPayment(Request $request, Order $order)
    {
        try {
            $user = $request->user();

            if ($order->buyer_id !== $user->id && $user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak diizinkan mengkonfirmasi pembayaran untuk pesanan ini'
                ], 403);
            }

            if ($order->status !== 'diproses') {
                return response()->json([
                    'success' => false,
                    'message' => 'Status pesanan tidak memungkinkan konfirmasi pembayaran lagi',
                    'current_status' => $order->status
                ], 422);
            }

            DB::beginTransaction();
            try {
                // Move order to next status indicating payment received
                $order->update(['status' => 'dikemas']);

                // Create tracking record for payment confirmation
                OrderTracking::create([
                    'order_id' => $order->id,
                    'status' => 'dikemas',
                    'updated_by' => $user->id,
                ]);

                // Notify sellers about paid order
                $sellerNotifications = [];
                foreach ($order->items as $it) {
                    $businessId = $it->product->business_id ?? null;
                    if (!$businessId) continue;
                    if (!isset($sellerNotifications[$businessId])) $sellerNotifications[$businessId] = [];
                    $sellerNotifications[$businessId][] = $it;
                }

                foreach ($sellerNotifications as $businessId => $items) {

    // 🔥 ambil business + user sekalian
    $business = \App\Models\Business::with('user')->find($businessId);

    // ❗ validasi aman
    if (!$business || !$business->user) {
        continue;
    }

    $seller = $business->user;

    // Add earnings
    $sellerEarnings = collect($items)->sum(function ($item) {
        return $item['subtotal'];
    });

    $wallet = $seller->wallet ?? \App\Models\Wallet::create([
        'user_id' => $seller->id,
        'balance' => 0,
        'total_earned' => 0,
        'total_withdrawn' => 0,
    ]);

    $wallet->addBalance($sellerEarnings, "Penjualan dari pesanan #{$order->id}", $order->id);

    $productCount = count($items);

    Notification::create([
        'user_id' => $seller->id,
        'type' => 'order_created',
        'title' => 'Pesanan Baru! 🎉',
        'message' => "Pesanan baru dari {$user->name} untuk {$productCount} produk. Pendapatan: Rp " . number_format($sellerEarnings, 0, ',', '.'),
        'related_id' => $order->id,
        'is_read' => false,
    ]);
}

                // Notify buyer
                Notification::create([
                    'user_id' => $order->buyer_id,
                    'type' => 'payment_confirmed',
                    'title' => 'Pembayaran Berhasil',
                    'message' => 'Pembayaran Anda telah diterima. Pesanan akan diproses oleh penjual.',
                    'related_id' => $order->id,
                    'is_read' => false,
                ]);

                DB::commit();

                $order->load('items.product.business', 'buyer', 'trackings');

                return response()->json([
                    'success' => true,
                    'message' => 'Pembayaran dikonfirmasi dan pesanan diperbarui',
                    'data' => new OrderResource($order)
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Error confirming payment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengkonfirmasi pembayaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }


   public function complete(Request $request, Order $order)
{
    try {
        $user = $request->user();

        if ($order->buyer_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Ini bukan pesanan Anda'
            ], 403);
        }

        if ($order->status !== 'diantarkan') {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan belum bisa dikonfirmasi'
            ], 422);
        }

        DB::beginTransaction();

        $order->update(['status' => 'selesai']);

        OrderTracking::create([
            'order_id' => $order->id,
            'status' => 'selesai',
            'updated_by' => $user->id,
        ]);

        $firstItem = $order->items()->with('product.business')->first();

        if ($firstItem && $firstItem->product && $firstItem->product->business) {
            Notification::create([
                'user_id' => $firstItem->product->business->user_id,
                'type' => 'order_completed',
                'title' => 'Pesanan Selesai',
                'message' => "Pesanan #{$order->id} telah dikonfirmasi selesai oleh pembeli.",
                'related_id' => $order->id,
                'is_read' => false,
            ]);
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil dikonfirmasi'
        ]);

    } catch (\Exception $e) {
        DB::rollBack();

        \Log::error('COMPLETE ERROR: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Gagal konfirmasi pesanan',
            'error' => $e->getMessage()
        ], 500);
    }
}
}