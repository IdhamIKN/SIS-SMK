<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IzinUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jenis' => ['required', 'in:izin_sakit,izin_pulang_cepat,izin_terlambat,izin_lainnya'],
            'tanggal_izin' => ['required_if:jenis,izin_pulang_cepat,izin_terlambat', 'nullable', 'date', 'after_or_equal:today'],
            'tanggal_mulai' => ['required_if:jenis,izin_sakit,izin_lainnya', 'nullable', 'date', 'after_or_equal:today'],
            'tanggal_sampai' => ['required_if:jenis,izin_sakit,izin_lainnya', 'nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'alasan' => ['required', 'string', 'max:500'],
            'bukti' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png',
                'max:10240',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'jenis.required' => 'Jenis izin wajib dipilih.',
            'jenis.in' => 'Jenis izin yang dipilih tidak valid.',
            'tanggal_izin.required_if' => 'Tanggal izin wajib diisi untuk jenis izin yang dipilih.',
            'tanggal_izin.date' => 'Tanggal izin harus berupa tanggal yang valid.',
            'tanggal_izin.after_or_equal' => 'Tanggal izin tidak boleh sebelum hari ini.',
            'tanggal_mulai.required_if' => 'Tanggal mulai wajib diisi untuk jenis izin yang dipilih.',
            'tanggal_mulai.date' => 'Tanggal mulai harus berupa tanggal yang valid.',
            'tanggal_mulai.after_or_equal' => 'Tanggal mulai tidak boleh sebelum hari ini.',
            'tanggal_sampai.required_if' => 'Tanggal sampai wajib diisi untuk jenis izin yang dipilih.',
            'tanggal_sampai.date' => 'Tanggal sampai harus berupa tanggal yang valid.',
            'tanggal_sampai.after_or_equal' => 'Tanggal sampai tidak boleh sebelum tanggal mulai.',
            'alasan.required' => 'Alasan wajib diisi.',
            'alasan.string' => 'Alasan harus berupa teks.',
            'alasan.max' => 'Alasan maksimal 500 karakter.',
            'bukti.image' => 'Bukti izin harus berupa file gambar.',
            'bukti.mimes' => 'Bukti izin harus berformat JPEG, JPG, atau PNG.',
            'bukti.max' => 'Ukuran bukti izin maksimal 10MB.',
        ];
    }

    public function attributes(): array
    {
        return [
            'jenis' => 'Jenis izin',
            'tanggal_izin' => 'Tanggal izin',
            'tanggal_mulai' => 'Tanggal mulai',
            'tanggal_sampai' => 'Tanggal sampai',
            'alasan' => 'Alasan',
            'bukti' => 'Bukti izin',
        ];
    }
}