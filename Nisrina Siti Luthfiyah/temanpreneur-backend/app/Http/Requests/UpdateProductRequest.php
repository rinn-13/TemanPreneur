<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if user is authorized
     */
    public function authorize(): bool
    {
        $product = $this->route('product');
        $user = auth()->user();
        
        // Handle case where product might be string ID
        if (is_string($product)) {
            $product = \App\Models\Product::find($product);
        }
        
        if (!$product || !$user || !$user->business) {
            return false;
        }
        
        return (int) $product->business_id === (int) $user->business->id;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255|min:3',
            'description' => 'sometimes|required|string|min:20|max:1000',
            'price' => 'sometimes|required|numeric|min:1000|max:99999999',
            'stock' => 'sometimes|required|integer|min:0|max:10000',
            'category_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama produk wajib diisi.',
            'name.min' => 'Nama produk minimal 3 karakter.',
            'description.required' => 'Deskripsi produk wajib diisi.',
            'description.min' => 'Deskripsi produk minimal 20 karakter.',
            'price.required' => 'Harga produk wajib diisi.',
            'price.min' => 'Harga produk minimal Rp 1.000.',
            'stock.required' => 'Stok produk wajib diisi.',
            'stock.integer' => 'Stok harus berupa angka bulat.',
            'image.image' => 'Gambar harus berformat JPEG, PNG, atau GIF.',
            'image.max' => 'Ukuran gambar maksimal 5MB.',
        ];
    }
}
