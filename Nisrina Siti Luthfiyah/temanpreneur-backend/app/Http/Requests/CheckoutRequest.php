<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'payment_method' => $this->payment_method ?? 'transfer',
        ]);
    }

    public function rules(): array
    {
        return [
            'payment_method' => 'required|in:transfer,ewallet,cod',
            'shipping_address' => 'required|string|min:3|max:500',

            'shipping_phone' => 'required|string|min:8|max:20',

            'shipping_name' => 'required|string|min:2|max:100',

            'buyer_notes' => 'nullable|string|min:0|max:1000',

            'item_ids' => 'nullable|array|min:1|required_without:product_id',
            'item_ids.*' => 'integer|exists:cart_items,id',

            'product_id' => 'nullable|integer|exists:products,id|required_without:item_ids',
            'quantity' => 'nullable|integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.required' => 'Metode pembayaran wajib dipilih',
            'payment_method.in' => 'Metode pembayaran tidak valid',

            'shipping_address.required' => 'Alamat pengiriman wajib diisi',
            'shipping_address.min' => 'Alamat pengiriman minimal 3 karakter',

            'shipping_phone.required' => 'Nomor telepon wajib diisi',
            'shipping_phone.min' => 'Nomor telepon terlalu pendek',
            'shipping_name.required' => 'Nama penerima wajib diisi',

            'item_ids.required_without' => 'Pilih minimal satu produk untuk checkout',
            'item_ids.min' => 'Pilih minimal satu produk untuk checkout',
            'item_ids.*.exists' => 'Item keranjang tidak ditemukan',

            'product_id.required_without' => 'Produk atau keranjang wajib dipilih',
        ];
    }
}