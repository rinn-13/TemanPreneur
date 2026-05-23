<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Http\Resources\CartItemResource;
use App\Http\Resources\OrderResource;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderTracking;
use App\Models\Product;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    /**
     * Get all cart items for authenticated user
     * GET /api/cart
     */
    public function index(Request $request)
    {
        try {
            $items = CartItem::with(['product.business.user', 'product.category'])
                ->where('user_id', $request->user()->id)
                ->limit(20)
                ->get();

            // Filter items dengan product yang hilang
            $validItems = $items->filter(fn ($item) => $item->product !== null);

            $total = $validItems->sum(function ($item) {
                $price = $item->product?->price ?? 0;
                return $price * $item->quantity;
            });

            // Transform dengan safe resource
            $cartData = [];
            foreach ($validItems as $item) {
                try {
                    $cartData[] = new CartItemResource($item);
                } catch (\Exception $e) {
                    Log::warning('Error transforming cart item ' . $item->id . ': ' . $e->getMessage());
                    // Skip item yang error saat transform
                    continue;
                }
            }

            return response()->json([
                'success' => true,
                'data' => $cartData,
                'summary' => [
                    'items_count' => $validItems->count(),
                    'total_quantity' => $validItems->sum('quantity'),
                    'total_price' => (float) $total,
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching cart: ' . $e->getMessage() . ' | ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil keranjang',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get cart count for authenticated user
     * GET /api/cart/count
     */
    public function count(Request $request)
    {
        try {
            $count = CartItem::where('user_id', $request->user()->id)->sum('quantity');
            return response()->json(['count' => $count], 200);
        } catch (\Exception $e) {
            return response()->json(['count' => 0], 200);
        }
    }

    private function getEffectiveRole(Request $request): string
    {
        $user = $request->user();
        $activeRole = strtolower((string) $request->header('X-Active-Role', ''));

        if ($activeRole && $user->hasRole($activeRole)) {
            return $activeRole;
        }

        if ($user->hasRole('buyer')) {
            return 'buyer';
        }

        if ($user->hasRole('seller_premium')) {
            return 'seller_premium';
        }

        if ($user->hasRole('seller')) {
            return 'seller';
        }

        if ($user->hasRole('admin')) {
            return 'admin';
        }

        return $user->role ?? 'buyer';
    }

    /**
     * Add product to cart
     * POST /api/cart
     */
    public function store(Request $request)
    {
        try {
            $user = $request->user();
            $effectiveRole = $this->getEffectiveRole($request);

            // Only buyer mode may add products to cart
            if ($effectiveRole !== 'buyer') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya buyer yang dapat menambahkan produk ke keranjang.',
                ], 403);
            }

            $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'nullable|integer|min:1|max:100',
            ]);

            $product = Product::find($request->product_id);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk tidak ditemukan',
                ], 404);
            }

            $quantity = $request->quantity ?? 1;

            // Check stock
            if ($product->stock < $quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stok tidak cukup',
                    'available_stock' => $product->stock,
                    'requested_quantity' => $quantity
                ], 400);
            }

            // Add or update cart item
            $cartItem = CartItem::firstOrNew([
                'user_id' => $request->user()->id,
                'product_id' => $product->id
            ]);

            $oldQuantity = (int) ($cartItem->quantity ?? 0);
            $cartItem->quantity = max(1, $oldQuantity + $quantity);
            $cartItem->save();
            $cartItem->load(['product.business.user', 'product.category']);

            return response()->json([
                'success' => true,
                'message' => 'Produk ditambahkan ke keranjang',
                'data' => new CartItemResource($cartItem)
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error adding to cart: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan ke keranjang',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update cart item quantity
     * PUT /api/cart/{cartItem}
     */
    public function update(Request $request, CartItem $cartItem)
    {
        try {
            if ($cartItem->user_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak diizinkan mengubah keranjang orang lain'
                ], 403);
            }

            $request->validate([
                'quantity' => 'required|integer|min:1|max:100'
            ]);

            // Check stock
            if ($cartItem->product->stock < $request->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stok tidak cukup',
                    'available_stock' => $cartItem->product->stock,
                    'requested_quantity' => $request->quantity
                ], 400);
            }

            $cartItem->update(['quantity' => $request->quantity]);
            $cartItem->load(['product.business.user', 'product.category']);

            return response()->json([
                'success' => true,
                'message' => 'Keranjang diperbarui',
                'data' => new CartItemResource($cartItem)
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating cart: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui keranjang',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove item from cart
     * DELETE /api/cart/{cartItem}
     */
    public function destroy(Request $request, CartItem $cartItem)
    {
        try {
            if ($cartItem->user_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak diizinkan menghapus keranjang orang lain'
                ], 403);
            }

            $cartItem->delete();

            return response()->json([
                'success' => true,
                'message' => 'Produk dihapus dari keranjang'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting cart item: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus dari keranjang',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process checkout - creates OrderGroup + separate Orders per store
     * POST /api/cart/checkout
     */
    public function checkout(CheckoutRequest $request)
    {
        try {
            $user = $request->user();
            $effectiveRole = $this->getEffectiveRole($request);

            // Only buyer mode may checkout
            if ($effectiveRole !== 'buyer') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya buyer yang dapat melakukan checkout.',
                ], 403);
            }

            // Get cart items with relations
            $cartItems = CartItem::whereIn('id', $request->item_ids)
                ->where('user_id', $user->id)
                ->with(['product.business', 'product.category'])
                ->get();

            if ($cartItems->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Keranjang kosong atau item tidak ditemukan'
                ], 400);
            }

            DB::beginTransaction();

            try {
                $groupedByStore = [];
                $totalSubtotal = 0;

                foreach ($cartItems as $cartItem) {
                    $product = $cartItem->product;
                    $businessId = $product?->business_id;

                    if (!$product || !$businessId) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Produk tidak valid atau tidak terhubung dengan toko yang valid',
                        ], 400);
                    }

                    if ($product->stock < $cartItem->quantity) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "Stok tidak cukup untuk {$product->name}",
                            'product_id' => $product->id
                        ], 400);
                    }

                    $subtotal = $product->price * $cartItem->quantity;
                    $totalSubtotal += $subtotal;

                    if (!isset($groupedByStore[$businessId])) {
                        $groupedByStore[$businessId] = [
                            'business' => $product->business,
                            'subtotal' => 0,
                            'items' => [],
                        ];
                    }

                    $groupedByStore[$businessId]['items'][] = [
                        'product_id' => $product->id,
                        'quantity' => $cartItem->quantity,
                        'price' => $product->price,
                        'subtotal' => $subtotal,
                    ];
                    $groupedByStore[$businessId]['subtotal'] += $subtotal;
                }

                // Shipping cost feature removed: always treat shipping as zero
                $shippingCost = 0.0;
                $grandTotal = $totalSubtotal;

                $orderGroup = \App\Models\OrderGroup::create([
                    'group_code' => 'GRP-' . strtoupper(substr(uniqid(), -8)),
                    'buyer_id' => $user->id,
                    'payment_method' => $request->payment_method,
                    'shipping_name' => $request->shipping_name ?? $user->name,
                    'shipping_phone' => $request->shipping_phone,
                    'shipping_address' => $request->shipping_address,
                    'total_items_price' => $totalSubtotal,
                    'total_shipping_cost' => 0,
                    'grand_total' => $grandTotal,
                    'buyer_notes' => $request->input('buyer_notes'),
                ]);

                $createdOrders = [];
                $storeCount = count($groupedByStore);
                $storeIndex = 0;

                foreach ($groupedByStore as $businessId => $storeData) {
                    $storeIndex++;
                    // No per-store shipping; always zero
                    $storeShipping = 0;

                    $order = Order::create([
                        'buyer_id' => $user->id,
                        'business_id' => $businessId,
                        'order_group_id' => $orderGroup->id,
                        'product_id' => $storeData['items'][0]['product_id'],
                        'quantity' => array_sum(array_column($storeData['items'], 'quantity')),
                        'total_price' => $storeData['subtotal'],
                        'status' => 'diproses',
                        'payment_method' => $request->payment_method,
                        'shipping_cost' => $storeShipping,
                        'total_amount' => $storeData['subtotal'] + $storeShipping,
                        'shipping_address' => $request->shipping_address,
                        'shipping_phone' => $request->shipping_phone,
                        'shipping_name' => $request->shipping_name ?? $user->name,
                        'buyer_notes' => $request->input('buyer_notes'),
                    ]);

                    foreach ($storeData['items'] as $itemData) {
                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $itemData['product_id'],
                            'quantity' => $itemData['quantity'],
                            'price' => $itemData['price'],
                            'subtotal' => $itemData['subtotal'],
                        ]);

                        Product::where('id', $itemData['product_id'])
                            ->decrement('stock', $itemData['quantity']);
                    }

                    OrderTracking::create([
                        'order_id' => $order->id,
                        'status' => 'diproses',
                        'updated_by' => $user->id,
                    ]);

                    if ($storeData['business'] && $storeData['business']->user) {
                        Notification::create([
                            'user_id' => $storeData['business']->user->id,
                            'type' => 'order_created',
                            'title' => 'Pesanan Baru! 🎉',
                            'message' => "Pesanan baru dari {$user->name} untuk toko {$storeData['business']->name}",
                            'related_id' => $order->id,
                            'is_read' => false,
                        ]);
                    }

                    $createdOrders[] = $order;
                }

                CartItem::whereIn('id', $cartItems->pluck('id'))->delete();

                DB::commit();

                foreach ($createdOrders as $order) {
                    $order->load('items.product.business', 'items.product.category', 'trackings', 'orderGroup', 'buyer');
                }

                $orderGroup->load('orders.items.product.business', 'orders.items.product.category', 'buyer');

                Log::info("OrderGroup {$orderGroup->id} created by user {$user->id} with " . count($createdOrders) . " orders");

                return response()->json([
                    'success' => true,
                    'message' => 'Checkout berhasil! Pesanan Anda sedang diproses.',
                    'data' => [
                        'order_group' => [
                            'id' => $orderGroup->id,
                            'group_code' => $orderGroup->group_code,
                            'grand_total' => (float) $orderGroup->grand_total,
                            'total_items_price' => (float) $orderGroup->total_items_price,
                            'total_shipping_cost' => (float) $orderGroup->total_shipping_cost,
                            'orders' => OrderResource::collection($createdOrders),
                        ],
                        'order' => $createdOrders[0] ? new OrderResource($createdOrders[0]) : null,
                    ]
                ], 201);
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
            Log::error('Checkout error: ' . $e->getMessage() . ' | ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan checkout',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
