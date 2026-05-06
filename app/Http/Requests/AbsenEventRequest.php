<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AbsenEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'barcode' => 'required|string',
            'jenis' => 'required|in:masuk,pulang',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
        ];
    }

    public function messages(): array
    {
        return [
            'barcode.required' => 'Barcode wajib diisi.',
            'jenis.required' => 'Jenis absen wajib dipilih.',
            'jenis.in' => 'Jenis absen harus masuk atau pulang.',
            'lat.between' => 'Latitude harus antara -90 hingga 90.',
            'lat.numeric' => 'Latitude harus berupa angka.',
            'lng.between' => 'Longitude harus antara -180 hingga 180.',
            'lng.numeric' => 'Longitude harus berupa angka.',
        ];
    }
}