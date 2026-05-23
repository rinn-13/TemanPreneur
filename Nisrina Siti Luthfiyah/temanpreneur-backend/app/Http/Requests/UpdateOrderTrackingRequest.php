<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderTrackingRequest extends FormRequest
{
    /**
     * Determine if user is authorized
     */
    public function authorize(): bool
    {
        $user = auth()->user();
        $order = $this->route('order');

        if (!$user || !$order) {
            return false;
        }

        if ($user->role === 'admin') {
            return true;
        }

        if ($order->buyer_id === $user->id) {
            return true;
        }

        return $order->items()
            ->whereHas('product.business', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->exists();
    }
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'status' => 'required|in:diproses,dikemas,diantarkan,selesai,dibatalkan',
            'notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Status pesanan wajib dipilih',
            'status.in' => 'Status pesanan tidak valid. Harus: diproses, dikemas, diantarkan, selesai, atau dibatalkan',
            'notes.max' => 'Catatan maksimal 500 karakter',
        ];
    }
}
