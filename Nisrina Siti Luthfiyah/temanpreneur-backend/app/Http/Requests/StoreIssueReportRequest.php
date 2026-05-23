<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIssueReportRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'order_id' => 'nullable|integer|exists:orders,id',
            'subject' => 'required|string|min:5|max:255',
            'type' => 'required|in:produk_rusak,produk_tidak_sesuai,pengiriman_terlambat,pengiriman_salah,penipuan,pengiriman,seller,pembayaran,lainnya',
            'message' => 'required_without:description|string|min:20|max:1000',
            'description' => 'nullable|string|min:20|max:1000',
            'files' => 'array|max:5',
            'files.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
        ];
    }

    public function messages()
    {
        return [
            'order_id.required' => 'ID pesanan wajib diisi.',
            'order_id.integer' => 'ID pesanan harus berupa angka.',
            'order_id.exists' => 'ID pesanan tidak valid.',
            'subject.required' => 'Judul laporan wajib diisi.',
            'subject.min' => 'Judul laporan minimal 5 karakter.',
            'subject.max' => 'Judul laporan maksimal 255 karakter.',
            'type.required' => 'Jenis masalah wajib dipilih.',
            'type.in' => 'Jenis masalah tidak valid.',
            'message.required_without' => 'Pesan laporan wajib diisi.',
            'message.min' => 'Pesan laporan minimal 20 karakter.',
            'message.max' => 'Pesan laporan maksimal 1000 karakter.',
            'description.min' => 'Deskripsi laporan minimal 20 karakter.',
            'description.max' => 'Deskripsi laporan maksimal 1000 karakter.',
            'files.array' => 'Lampiran harus berupa daftar file.',
            'files.max' => 'Maksimal 5 file lampiran.',
            'files.*.image' => 'Lampiran harus berupa gambar.',
            'files.*.mimes' => 'Format lampiran harus JPG, JPEG, PNG, atau GIF.',
            'files.*.max' => 'Ukuran lampiran maksimal 5MB per file.',
        ];
    }
}
