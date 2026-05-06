<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KelasStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_kelas' => 'required|string|max:50|unique:kelas,nama_kelas',
            'tingkat' => 'required|in:X,XI,XII',
            'jurusan_id' => 'required|exists:jurusans,id',
            'wali_kelas_id' => 'nullable|exists:gtks,id',
            'bk_id' => 'nullable|exists:gtks,id',
            'shift' => 'required|in:Pagi,Siang',
            'wa_group' => 'nullable|string|max:30',
            'tahun_ajaran' => 'nullable|string|max:9',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_kelas.required' => 'Nama kelas wajib diisi.',
            'nama_kelas.unique' => 'Nama kelas sudah terdaftar.',
            'tingkat.required' => 'Tingkat wajib dipilih.',
            'tingkat.in' => 'Tingkat harus X, XI, atau XII.',
            'jurusan_id.required' => 'Jurusan wajib dipilih.',
            'jurusan_id.exists' => 'Jurusan tidak ditemukan.',
            'wali_kelas_id.exists' => 'Wali kelas tidak ditemukan.',
            'bk_id.exists' => 'Guru BK tidak ditemukan.',
            'shift.required' => 'Shift wajib dipilih.',
            'shift.in' => 'Shift harus Pagi atau Siang.',
            'wa_group.max' => 'Nomor grup WA maksimal 30 karakter.',
            'tahun_ajaran.max' => 'Tahun ajaran maksimal 9 karakter.',
        ];
    }
}
