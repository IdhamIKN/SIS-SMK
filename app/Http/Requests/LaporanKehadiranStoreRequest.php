<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LaporanKehadiranStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jadwal_kbm_id' => 'required|exists:jadwal_kbm,id',
            'status' => 'required|in:hijau,kuning,merah,abu,biru,pink',
            'catatan' => 'nullable|string|max:500',
            'foto_kelas' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'jadwal_kbm_id.required' => 'Jadwal KBM wajib dipilih.',
            'jadwal_kbm_id.exists' => 'Jadwal KBM tidak ditemukan.',
            'status.required' => 'Status laporan wajib dipilih.',
            'status.in' => 'Status laporan tidak valid.',
            'catatan.string' => 'Catatan harus berupa teks.',
            'catatan.max' => 'Catatan maksimal 500 karakter.',
            'foto_kelas.image' => 'Foto kelas harus berupa file gambar.',
            'foto_kelas.mimes' => 'Foto kelas harus berformat JPEG, JPG, atau PNG.',
            'foto_kelas.max' => 'Ukuran foto kelas maksimal 5MB.',
        ];
    }

    public function attributes(): array
    {
        return [
            'jadwal_kbm_id' => 'Jadwal KBM',
            'status' => 'Status laporan',
            'catatan' => 'Catatan',
            'foto_kelas' => 'Foto kelas',
        ];
    }
}