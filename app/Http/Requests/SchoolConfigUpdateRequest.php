<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SchoolConfigUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Informasi Dasar Sekolah
            'sekolah' => 'required|string|max:255',
            'alsekolah' => 'nullable|string|max:500',
            'telp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'kab' => 'nullable|string|max:100',
            'alias' => 'nullable|string|max:50',

            // Kepala Sekolah
            'nama_ks' => 'nullable|string|max:255',
            'nip_ks' => 'nullable|string|max:50',

            // Wakil Kepala Sekolah
            'nama_waka' => 'nullable|string|max:255',
            'nip_waka' => 'nullable|string|max:50',

            // Ketua
            'nama_ketua' => 'nullable|string|max:255',
            'nip_ketua' => 'nullable|string|max:50',

            // Website & Media
            'site_url' => 'nullable|url|max:255',
            'site_logo' => 'nullable|url|max:255',
            'wasekolah' => 'nullable|string|max:20',

            // Jam Sekolah
            'jam_masuk' => 'nullable|date_format:H:i',
            'jam_pulang' => 'nullable|date_format:H:i|after:jam_masuk',
            'hari_efektif' => 'nullable|array|min:1',
            'hari_efektif.*' => 'string|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',

            // Lokasi & Sistem
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'system_name' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            // Informasi Dasar Sekolah
            'sekolah.required' => 'Nama sekolah wajib diisi.',
            'sekolah.max' => 'Nama sekolah maksimal 255 karakter.',
            'alsekolah.max' => 'Alamat sekolah maksimal 500 karakter.',
            'telp.max' => 'Nomor telepon maksimal 20 karakter.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 255 karakter.',
            'kab.max' => 'Kabupaten maksimal 100 karakter.',
            'alias.max' => 'Alias maksimal 50 karakter.',

            // Kepala Sekolah
            'nama_ks.max' => 'Nama kepala sekolah maksimal 255 karakter.',
            'nip_ks.max' => 'NIP kepala sekolah maksimal 50 karakter.',

            // Wakil Kepala Sekolah
            'nama_waka.max' => 'Nama wakil kepala sekolah maksimal 255 karakter.',
            'nip_waka.max' => 'NIP wakil kepala sekolah maksimal 50 karakter.',

            // Ketua
            'nama_ketua.max' => 'Nama ketua maksimal 255 karakter.',
            'nip_ketua.max' => 'NIP ketua maksimal 50 karakter.',

            // Website & Media
            'site_url.url' => 'URL website tidak valid.',
            'site_url.max' => 'URL website maksimal 255 karakter.',
            'site_logo.url' => 'URL logo tidak valid.',
            'site_logo.max' => 'URL logo maksimal 255 karakter.',
            'wasekolah.max' => 'Nomor WhatsApp sekolah maksimal 20 karakter.',

            // Jam Sekolah
            'jam_masuk.date_format' => 'Format jam masuk tidak valid (gunakan format HH:MM).',
            'jam_pulang.date_format' => 'Format jam pulang tidak valid (gunakan format HH:MM).',
            'jam_pulang.after' => 'Jam pulang harus setelah jam masuk.',
            'hari_efektif.min' => 'Minimal pilih 1 hari efektif sekolah.',
            'hari_efektif.*.in' => 'Hari efektif tidak valid.',

            // Lokasi & Sistem
            'latitude.between' => 'Latitude harus antara -90 hingga 90.',
            'latitude.numeric' => 'Latitude harus berupa angka.',
            'longitude.between' => 'Longitude harus antara -180 hingga 180.',
            'longitude.numeric' => 'Longitude harus berupa angka.',
            'system_name.max' => 'Nama sistem maksimal 255 karakter.',
        ];
    }
}