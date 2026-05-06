<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GTKStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kd_guru' => 'required|string|max:10|unique:gtks',
            'nip' => 'nullable|string|max:20',
            'nik' => 'nullable|string|max:20',
            'nuptk' => 'nullable|string|max:20',
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'no_hp' => 'nullable|string|max:20',
            'foto' => 'nullable|image|max:2048',
            'mata_pelajaran' => 'nullable|string|max:255',
            'jabatan' => 'required|string|max:255',
            'status_aktif' => 'boolean',
            'acc_absen' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'kd_guru.required' => 'Kode guru wajib diisi.',
            'kd_guru.max' => 'Kode guru maksimal 10 karakter.',
            'kd_guru.unique' => 'Kode guru sudah digunakan.',
            'nip.max' => 'NIP maksimal 20 karakter.',
            'nik.max' => 'NIK maksimal 20 karakter.',
            'nuptk.max' => 'NUPTK maksimal 20 karakter.',
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nama_lengkap.max' => 'Nama lengkap maksimal 255 karakter.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin.in' => 'Jenis kelamin harus L atau P.',
            'no_hp.max' => 'Nomor HP maksimal 20 karakter.',
            'foto.image' => 'Foto harus berupa file gambar.',
            'foto.max' => 'Ukuran foto maksimal 2MB.',
            'mata_pelajaran.max' => 'Mata pelajaran maksimal 255 karakter.',
            'jabatan.required' => 'Jabatan wajib diisi.',
            'jabatan.max' => 'Jabatan maksimal 255 karakter.',
        ];
    }

    public function attributes(): array
    {
        return [
            'kd_guru' => 'Kode guru',
            'nip' => 'NIP',
            'nik' => 'NIK',
            'nuptk' => 'NUPTK',
            'nama_lengkap' => 'Nama lengkap',
            'jenis_kelamin' => 'Jenis kelamin',
            'no_hp' => 'Nomor HP',
            'foto' => 'Foto',
            'mata_pelajaran' => 'Mata pelajaran',
            'jabatan' => 'Jabatan',
            'status_aktif' => 'Status aktif',
            'acc_absen' => 'Akses absen',
        ];
    }
}