<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateAccessCodeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Hanya admin yang bisa create access codes
        return auth()->check() && auth()->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'code' => 'required|string|min:6|max:20|unique:access_codes',
            'quantity' => 'nullable|integer|min:1|max:1000',
            'expires_at' => 'nullable|date|after:today',
            'description' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages.
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Kode akses wajib diisi.',
            'code.min' => 'Kode akses minimal 6 karakter.',
            'code.max' => 'Kode akses maksimal 20 karakter.',
            'code.unique' => 'Kode akses sudah terdaftar.',
            'quantity.integer' => 'Jumlah harus berupa angka.',
            'quantity.min' => 'Jumlah minimal 1.',
            'expires_at.date' => 'Format tanggal tidak valid.',
            'expires_at.after' => 'Tanggal kadaluarsa harus lebih dari hari ini.',
            'description.max' => 'Deskripsi maksimal 500 karakter.',
        ];
    }
}
