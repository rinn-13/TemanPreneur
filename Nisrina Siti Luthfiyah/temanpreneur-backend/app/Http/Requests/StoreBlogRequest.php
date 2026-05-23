<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBlogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Policy handles authorization
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'image' => 'nullable|image|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul blog wajib diisi.',
            'content.required' => 'Isi blog wajib diisi.',
            'image.image' => 'Gambar harus berformat JPG, PNG, atau WebP.',
        ];
    }
}

