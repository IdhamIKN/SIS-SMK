<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_event' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'lokasi' => 'nullable|string|max:255',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
            'radius_meter' => 'nullable|integer|min:10|max:5000',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'barcode_rotate_detik' => 'nullable|integer|min:0|max:3600',
            'mode_peserta' => 'required|in:kelas,siswa',
            'kelas_id' => 'nullable|array',
            'kelas_id.*' => 'exists:kelas,id',
            'siswa_id' => 'nullable|array',
            'siswa_id.*' => 'exists:siswas,id',
            'berlaku_untuk_semua' => 'boolean',
            'ada_absen_masuk' => 'boolean',
            'ada_absen_pulang' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_event.required' => 'Nama event wajib diisi.',
            'nama_event.max' => 'Nama event maksimal 255 karakter.',
            'lokasi.max' => 'Lokasi maksimal 255 karakter.',
            'lat.between' => 'Latitude harus antara -90 hingga 90.',
            'lat.numeric' => 'Latitude harus berupa angka.',
            'lng.between' => 'Longitude harus antara -180 hingga 180.',
            'lng.numeric' => 'Longitude harus berupa angka.',
            'radius_meter.integer' => 'Radius harus berupa bilangan bulat.',
            'radius_meter.min' => 'Radius minimal 10 meter.',
            'radius_meter.max' => 'Radius maksimal 5000 meter.',
            'tanggal_mulai.required' => 'Tanggal mulai wajib diisi.',
            'tanggal_mulai.date' => 'Format tanggal mulai tidak valid.',
            'tanggal_selesai.required' => 'Tanggal selesai wajib diisi.',
            'tanggal_selesai.date' => 'Format tanggal selesai tidak valid.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            'barcode_rotate_detik.integer' => 'Interval rotasi barcode harus berupa bilangan bulat.',
            'barcode_rotate_detik.min' => 'Interval rotasi barcode minimal 0 detik.',
            'barcode_rotate_detik.max' => 'Interval rotasi barcode maksimal 3600 detik.',
            'mode_peserta.required' => 'Mode peserta wajib dipilih.',
            'mode_peserta.in' => 'Mode peserta harus kelas atau siswa.',
            'kelas_id.array' => 'Kelas harus berupa array.',
            'kelas_id.*.exists' => 'Kelas yang dipilih tidak ditemukan.',
            'siswa_id.array' => 'Siswa harus berupa array.',
            'siswa_id.*.exists' => 'Siswa yang dipilih tidak ditemukan.',
        ];
    }
}