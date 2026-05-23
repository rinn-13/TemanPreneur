<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Message;
use App\Models\Notification;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    protected function getOrderSellerId(Order $order)
    {
        $order->loadMissing('product.business', 'items.product.business');

        if ($order->product && $order->product->business) {
            return $order->product->business->user_id;
        }

        return optional($order->items->first()->product->business)->user_id;
    }

    public function index(Order $order)
    {
        $sellerId = $this->getOrderSellerId($order);
        if ($order->buyer_id !== auth()->id() && $sellerId !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $messages = Message::where('order_id', $order->id)
            ->with('sender')
            ->orderBy('created_at')
            ->get();

        Message::where('order_id', $order->id)
            ->where('receiver_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json($messages);
    }

    public function store(Request $request, Order $order)
    {
        $sellerId = $this->getOrderSellerId($order);
        if ($order->buyer_id !== auth()->id() && $sellerId !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate(['message' => 'required|string']);

        $receiverId = ($order->buyer_id == auth()->id())
            ? $sellerId
            : $order->buyer_id;

        $message = Message::create([
            'order_id' => $order->id,
            'sender_id' => auth()->id(),
            'receiver_id' => $receiverId,
            'message' => $request->message,
            'is_read' => false,
        ]);

        // notifikasi ke penerima
        Notification::create([
            'user_id' => $receiverId,
            'type' => 'new_message',
            'title' => 'Pesan Baru',
            'message' => substr($request->message, 0, 50) . '...',
        ]);

        return response()->json($message->load('sender'), 201);
    }
}