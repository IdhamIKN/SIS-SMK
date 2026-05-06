<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AbsenPulangStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'foto_selfie' => 'required|image|max:2048',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ];
    }

    public function messages(): array
    {
        return [
            'foto_selfie.required' => 'Foto selfie wajib diambil sebelum absen pulang.',
            'foto_selfie.image' => 'Foto selfie harus berupa file gambar.',
            'foto_selfie.max' => 'Ukuran foto selfie maksimal 2MB.',
            'latitude.required' => 'Lokasi GPS belum terbaca. Aktifkan lokasi terlebih dahulu.',
            'latitude.numeric' => 'Format latitude tidak valid.',
            'latitude.between' => 'Latitude di luar batas yang diperbolehkan.',
            'longitude.required' => 'Lokasi GPS belum terbaca. Aktifkan lokasi terlebih dahulu.',
            'longitude.numeric' => 'Format longitude tidak valid.',
            'longitude.between' => 'Longitude di luar batas yang diperbolehkan.',
        ];
    }
}