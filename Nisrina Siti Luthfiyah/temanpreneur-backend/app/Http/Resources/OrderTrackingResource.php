<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderTrackingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'status' => strtolower($this->status),
            'status_display' => $this->getStatusDisplay(),
            'updated_by' => $this->updated_by,
            'updater' => $this->whenLoaded('updater', fn() => new UserResource($this->updater)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Get display name for status
     */
    private function getStatusDisplay(): string
    {
        return match(strtolower($this->status)) {
            'diproses' => 'Diproses',
            'dikemas' => 'Dikemas',
            'diantarkan' => 'Diantarkan',
            'selesai' => 'Selesai',
            default => ucfirst($this->status),
        };
    }
}
