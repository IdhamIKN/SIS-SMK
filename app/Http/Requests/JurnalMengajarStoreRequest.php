<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JurnalMengajarStoreRequest extends FormRequest
{
    // Definisikan konstanta agar mudah diubah
    protected const MIMES_GAMBAR = 'jpeg,jpg,png,webp,jfif';
    protected const MIMES_FILE   = 'jpeg,jpg,png,webp,jfif,pdf';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'gtk_id'       => ['nullable', 'integer', 'exists:gtks,id'],
            'tanggal'      => ['required', 'date'],
            'jadwal_kbm_id' => ['required', 'exists:jadwal_kbm,id'],
            'deskripsi'    => ['required', 'string', 'min:1', 'max:2000'],
            'namasiswa'    => ['nullable', 'array'],
            'namasiswa.*'  => ['integer', 'exists:siswas,id'],
            'bukti_kamera' => ['required_without:bukti_file', 'nullable', 'image', 'mimes:' . self::MIMES_GAMBAR, 'max:10240'],
            'bukti_file'   => ['required_without:bukti_kamera', 'nullable', 'file', 'mimes:' . self::MIMES_FILE, 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal.required'               => 'Tanggal jurnal wajib diisi.',
            'tanggal.date'                   => 'Tanggal jurnal tidak valid.',
            'jadwal_kbm_id.required'         => 'Jadwal KBM wajib dipilih.',
            'jadwal_kbm_id.exists'           => 'Jadwal KBM tidak ditemukan.',
            'gtk_id.exists'                  => 'Guru yang dipilih tidak ditemukan.',
            'deskripsi.required'             => 'Deskripsi pembelajaran wajib diisi.',
            'deskripsi.min'                  => 'Deskripsi pembelajaran wajib diisi.',
            'namasiswa.array'                => 'Daftar siswa tidak hadir tidak valid.',
            'namasiswa.*.exists'             => 'Siswa yang dipilih tidak ditemukan.',
            'bukti_kamera.image'             => 'Bukti kamera harus berupa gambar.',
            'bukti_kamera.mimes'             => 'Bukti kamera harus berformat jpg, jpeg, png, webp, atau jfif.',
            'bukti_kamera.required_without'  => 'Bukti pembelajaran wajib dilampirkan (foto atau file).',
            'bukti_kamera.max'               => 'Ukuran bukti kamera maksimal 10MB.',
            'bukti_file.mimes'               => 'Bukti file harus berformat jpg, jpeg, png, webp, jfif, atau pdf.',
            'bukti_file.required_without'    => 'Bukti pembelajaran wajib dilampirkan (foto atau file).',
            'bukti_file.max'                 => 'Ukuran bukti file maksimal 10MB.',
        ];
    }

    public function attributes(): array
    {
        return [
            'tanggal'      => 'Tanggal jurnal',
            'gtk_id'       => 'Guru',
            'jadwal_kbm_id' => 'Jadwal KBM',
            'deskripsi'    => 'Deskripsi pembelajaran',
            'namasiswa'    => 'Siswa tidak hadir',
            'bukti_kamera' => 'Bukti kamera',
            'bukti_file'   => 'Bukti file',
        ];
    }
}
