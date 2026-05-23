<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IssueReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $order = $this->order;
        $firstItem = optional($order?->items->first());
        $product = optional($firstItem->product);
        $business = optional($product->business);

        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'buyer_id' => $this->buyer_id,
            'subject' => $this->subject,
            'type' => $this->type,
            'description' => $this->description,
            'status' => $this->status,
            'status_display' => $this->getStatusDisplay(),
            'priority' => $this->getPriority(),
            'attachments' => $this->attachments,
            
            'buyer' => $this->whenLoaded('buyer', fn () => new UserResource($this->buyer)),
            'reporter_name' => optional($this->buyer)->name,
            'reporter_phone' => optional($this->buyer)->phone,
            'reporter_class' => optional($this->buyer)->class,
            
            'reported_id' => $business?->id,
            'reported_name' => $business?->name,
            'reported_phone' => $business?->phone,
            'reported_class' => $business?->category ?? null,
            
            'order' => $this->whenLoaded('order', fn () => [
                'id' => $order->id,
                'order_number' => 'ORD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
                'total_amount' => (float) $order->total_amount,
                'status' => $order->status,
                'product' => [
                    'id' => $product?->id,
                    'name' => $product?->name,
                    'business' => [
                        'id' => $business?->id,
                        'name' => $business?->name,
                        'user_id' => $business?->user_id,
                    ],
                ],
            ]),
            
            'products' => $this->whenLoaded('order.items', fn () => $this->order->items->map(fn ($item) => [
                'id' => $item->product?->id,
                'name' => $item->product?->name,
                'price' => (float) $item->price,
                'quantity' => $item->quantity,
            ])),
            
            'responses' => $this->whenLoaded('responses', fn () => $this->responses->map(fn ($response) => [
                'id' => $response->id,
                'admin_id' => $response->admin_id,
                'response_message' => $response->response_message,
                'action_type' => $response->action_type,
                'action_details' => $response->action_details,
                'status' => $response->status,
                'notified_at' => $response->notified_at,
                'completed_at' => $response->completed_at,
                'created_at' => $response->created_at,
                'admin' => optional($response->admin) ? [
                    'id' => $response->admin->id,
                    'name' => $response->admin->name,
                    'email' => $response->admin->email,
                ] : null,
            ])),
            
            'created_at' => $this->created_at?->format('d M Y H:i'),
            'created_at_full' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Get display name for status
     */
    private function getStatusDisplay(): string
    {
        return match ($this->status) {
            'open' => 'Menunggu Diproses',
            'in_progress' => 'Sedang Diproses',
            'closed' => 'Selesai',
            default => ucfirst($this->status),
        };
    }

    private function getPriority(): string
    {
        return match ($this->type) {
            'penipuan', 'produk_rusak', 'pengiriman_salah', 'seller', 'pembayaran' => 'high',
            'produk_tidak_sesuai', 'pengiriman_terlambat', 'pengiriman' => 'medium',
            default => 'low',
        };
    }
}
