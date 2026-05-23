<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBusinessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled in controller/policy
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => 'nullable|string|in:fashion,kuliner,kerajinan,digital,aksesoris,lainnya',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'theme_color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'logo.image' => 'Logo harus berupa gambar (JPEG, PNG, JPG).',
            'logo.max' => 'Ukuran logo maksimal 2MB.',
            'banner.image' => 'Banner harus berupa gambar (JPEG, PNG, JPG, GIF).',
            'banner.max' => 'Ukuran banner maksimal 5MB.',
            'theme_color.regex' => 'Warna tema harus dalam format hex (#RRGGBB).',
        ];
    }
}

