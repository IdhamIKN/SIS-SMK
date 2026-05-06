<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IzinUpdateStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:disetujui,ditolak',
            'catatan' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status verifikasi wajib dipilih.',
            'status.in' => 'Status verifikasi harus disetujui atau ditolak.',
            'catatan.string' => 'Catatan harus berupa teks.',
            'catatan.max' => 'Catatan maksimal 500 karakter.',
        ];
    }

    public function attributes(): array
    {
        return [
            'status' => 'Status verifikasi',
            'catatan' => 'Catatan',
        ];
    }
}