<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateBusinessRequest extends FormRequest
{
    /**
     * Determine if user is authorized
     */
    public function authorize(): bool
    {
        // User harus sudah login dan belum punya business
        return auth()->check() && !auth()->user()->business;
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string|min:20|max:1000',
           'phone' => [
    'nullable',
    'string',
    'regex:/^(\\+62|0)[0-9]{8,12}$/'
],
            'address' => 'nullable|string|min:5|max:500',
        ];
    }

    /**
     * Custom error messages (Indonesian)
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama toko wajib diisi',
            'name.max' => 'Nama toko tidak boleh lebih dari 255 karakter',
            'description.required' => 'Deskripsi toko wajib diisi',
            'description.min' => 'Deskripsi toko minimal 20 karakter',
            'description.max' => 'Deskripsi toko tidak boleh lebih dari 1000 karakter',
            'phone.regex' => 'Format nomor telepon tidak valid',
            'address.min' => 'Alamat minimal 5 karakter',
            'address.max' => 'Alamat tidak boleh lebih dari 500 karakter',
        ];
    }
}
