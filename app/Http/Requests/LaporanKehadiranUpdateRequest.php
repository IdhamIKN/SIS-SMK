<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LaporanKehadiranUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'  => 'required|in:hijau,kuning,merah,abu,biru,pink,orange',
            'catatan' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status laporan wajib dipilih.',
            'status.in'       => 'Status laporan tidak valid.',
            'catatan.string'  => 'Catatan harus berupa teks.',
            'catatan.max'     => 'Catatan maksimal 500 karakter.',
        ];
    }

    public function attributes(): array
    {
        return [
            'status'  => 'Status laporan',
            'catatan' => 'Catatan',
        ];
    }
}
