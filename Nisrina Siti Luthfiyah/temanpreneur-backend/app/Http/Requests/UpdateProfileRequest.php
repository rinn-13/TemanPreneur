<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // User hanya bisa update profil sendiri
        return auth()->id() == $this->user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255|min:3',
            'phone' => [
                'nullable',
                'string',
                'regex:/^(\\+62|0)[0-9]{9,13}$/'
            ],
            'class' => 'nullable|string|max:50',
        ];
    }

    /**
     * Get custom messages.
     */
    public function messages(): array
    {
        return [
            'name.min' => 'Nama minimal 3 karakter.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'phone.regex' => 'Format nomor telepon tidak valid.',
            'class.max' => 'Kelas maksimal 50 karakter.',
        ];
    }
}
