<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get validation rules
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|min:3',

            'email' => [
                'required',
                'email',
                'unique:users,email',
                // FIX: pakai regex aman + escape benar
                'regex:/^[a-zA-Z0-9._%+\-]+@smk\.belajar\.id$/'
            ],

            'password' => 'required|string|min:8|confirmed',

            'class' => 'nullable|string|max:50',

            'access_code' => 'nullable|string|min:6',
        ];
    }

    /**
     * Custom messages
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'name.min' => 'Nama minimal 3 karakter.',

            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'email.regex' => 'Email harus menggunakan domain @smk.belajar.id',

            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',

            'access_code.min' => 'Kode akses minimal 6 karakter.',
        ];
    }
}