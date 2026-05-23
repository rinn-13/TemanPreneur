<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

class CancelOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $order = $this->route('order');

        return $user && $order && $order->buyer_id === $user->id;
    }

    public function rules(): array
    {
        return [
            'reason' => 'required|string|min:10|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'Alasan pembatalan wajib diisi',
            'reason.min' => 'Alasan pembatalan minimal 10 karakter',
            'reason.max' => 'Alasan pembatalan maksimal 500 karakter',
        ];
    }
}
