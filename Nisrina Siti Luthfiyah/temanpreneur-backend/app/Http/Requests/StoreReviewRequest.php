<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // User harus pembeli yang memiliki order ini
        $order = $this->route('order');
        return auth()->id() === $order->buyer_id;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:10|max:1000',
        ];
    }

    /**
     * Get custom messages.
     */
    public function messages(): array
    {
        return [
            'rating.required' => 'Rating wajib dipilih.',
            'rating.integer' => 'Rating harus berupa angka.',
            'rating.min' => 'Rating minimal 1 bintang.',
            'rating.max' => 'Rating maksimal 5 bintang.',
            'comment.required' => 'Komentar wajib diisi.',
            'comment.min' => 'Komentar minimal 10 karakter.',
            'comment.max' => 'Komentar maksimal 1000 karakter.',
        ];
    }
}
