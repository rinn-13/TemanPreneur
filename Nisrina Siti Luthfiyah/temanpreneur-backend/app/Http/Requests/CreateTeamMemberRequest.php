<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTeamMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Hanya seller premium yang punya business
        $user = auth()->user();
        if (!$user || !$user->business) {
            return false;
        }
        
        return $user->business->is_premium;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email|unique:users',
            'name' => 'required|string|min:3|max:255',
            'role' => 'required|in:manager,staff',
        ];
    }

    /**
     * Get custom messages.
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Email anggota tim wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar di sistem.',
            'name.required' => 'Nama anggota tim wajib diisi.',
            'name.min' => 'Nama minimal 3 karakter.',
            'role.required' => 'Peran anggota tim wajib dipilih.',
            'role.in' => 'Peran anggota tim tidak valid.',
        ];
    }
}
