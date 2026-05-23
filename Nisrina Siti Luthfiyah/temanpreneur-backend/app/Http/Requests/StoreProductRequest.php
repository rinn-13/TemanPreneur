<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if user is authorized
     */
    public function authorize(): bool
    {
        // User harus sudah login dan punya bisnis yang verified
        $user = auth()->user();
        if (!$user || !$user->business) {
            return false;
        }
        
        // Business harus verified
        if (!$user->business->is_verified) {
            return false;
        }
        
        return true;
    }

    public function prepareForValidation()
    {
        $this->merge([
            'name' => trim($this->name ?? ''),
            'description' => trim($this->description ?? ''),
        ]);
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $user = auth()->user();
            $business = $user?->business;

            if (!$business) {
                return;
            }

            if (!$business->is_premium && $business->products()->count() >= 2) {
                $validator->errors()->add('business', 'Seller reguler hanya dapat membuat maksimal 2 produk. Upgrade ke seller premium untuk menjual lebih banyak produk.');
            }

            if (!$business->is_verified || !in_array($business->status, ['approved', 'active'], true)) {
                $validator->errors()->add('business', 'Bisnis Anda belum aktif atau belum disetujui untuk menambahkan produk.');
            }
        });
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|min:3',
            'description' => 'required|string|min:20|max:1000',
            'price' => 'required|numeric|min:1000|max:99999999',
            'stock' => 'required|integer|min:1|max:10000',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'images' => 'nullable|array|max:5', // Multiple images support
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama produk wajib diisi.',
            'name.min' => 'Nama produk minimal 3 karakter.',
            'name.max' => 'Nama produk maksimal 255 karakter.',
            'description.required' => 'Deskripsi produk wajib diisi.',
            'description.min' => 'Deskripsi produk minimal 20 karakter.',
            'description.max' => 'Deskripsi produk maksimal 1000 karakter.',
            'price.required' => 'Harga produk wajib diisi.',
            'price.numeric' => 'Harga produk harus berupa angka.',
            'price.min' => 'Harga produk minimal Rp 1.000.',
            'price.max' => 'Harga produk maksimal Rp 99.999.999.',
            'stock.required' => 'Stok produk wajib diisi.',
            'stock.integer' => 'Stok harus berupa angka bulat.',
            'stock.min' => 'Stok minimal 1 unit.',
            'stock.max' => 'Stok maksimal 10.000 unit.',
            'category_id.required' => 'Kategori produk wajib dipilih.',
            'category_id.exists' => 'Kategori produk tidak valid.',
            'category.required' => 'Kategori produk wajib diisi.',
            'image.image' => 'Gambar harus berformat JPEG, PNG, atau GIF.',
            'image.mimes' => 'Gambar harus berformat JPEG, PNG, atau GIF.',
            'image.max' => 'Ukuran gambar maksimal 5MB.',
            'images.array' => 'Gambar produk harus berupa array.',
            'images.max' => 'Maksimal 5 gambar produk.',
            'images.*.image' => 'Setiap gambar harus berformat JPEG, PNG, atau GIF.',
            'images.*.mimes' => 'Setiap gambar harus berformat JPEG, PNG, atau GIF.',
            'images.*.max' => 'Ukuran setiap gambar maksimal 5MB.',
        ];
    }
}
