<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SiswaUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $siswaId = $this->route('siswa')->id ?? null;

        return [
            'nis' => 'nullable|string|max:20|unique:siswas,nis,'.$siswaId,
            'nisn' => 'required|string|max:20|unique:siswas,nisn,'.$siswaId,
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'kelas_id' => 'required|exists:kelas,id',
            'angkatan' => 'nullable|integer|min:2000|max:'.(date('Y') + 1),
            'foto' => 'nullable|image|max:2048',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date|before:today',
            'alamat' => 'nullable|string|max:500',
            'desa' => 'nullable|string|max:100',
            'kelurahan' => 'nullable|string|max:100',
            'kecamatan' => 'nullable|string|max:100',
            'kabupaten' => 'nullable|string|max:100',
            'kode_pos' => 'nullable|string|max:10',
            'no_hp_siswa' => 'nullable|string|max:20',
            'no_hp_ortu1' => 'nullable|string|max:20',
            'no_hp_ortu2' => 'nullable|string|max:20',
            'nama_ortu1' => 'nullable|string|max:100',
            'nama_ortu2' => 'nullable|string|max:100',
            'nama_wali' => 'nullable|string|max:100',
            'status_aktif' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'nis.unique' => 'NIS sudah terdaftar.',
            'nis.max' => 'NIS maksimal 20 karakter.',
            'nisn.required' => 'NISN wajib diisi.',
            'nisn.unique' => 'NISN sudah terdaftar.',
            'nisn.max' => 'NISN maksimal 20 karakter.',
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nama_lengkap.max' => 'Nama lengkap maksimal 255 karakter.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin.in' => 'Jenis kelamin harus L atau P.',
            'kelas_id.required' => 'Kelas wajib dipilih.',
            'kelas_id.exists' => 'Kelas tidak ditemukan.',
            'angkatan.integer' => 'Angkatan harus berupa angka.',
            'angkatan.min' => 'Angkatan minimal 2000.',
            'angkatan.max' => 'Angkatan maksimal '.(date('Y') + 1).'.',
            'foto.image' => 'Foto harus berupa gambar.',
            'foto.max' => 'Foto maksimal 2MB.',
            'tempat_lahir.max' => 'Tempat lahir maksimal 100 karakter.',
            'tanggal_lahir.date' => 'Tanggal lahir harus berupa tanggal.',
            'tanggal_lahir.before' => 'Tanggal lahir harus sebelum hari ini.',
            'alamat.max' => 'Alamat maksimal 500 karakter.',
            'desa.max' => 'Desa maksimal 100 karakter.',
            'kelurahan.max' => 'Kelurahan maksimal 100 karakter.',
            'kecamatan.max' => 'Kecamatan maksimal 100 karakter.',
            'kabupaten.max' => 'Kabupaten maksimal 100 karakter.',
            'kode_pos.max' => 'Kode pos maksimal 10 karakter.',
            'no_hp_siswa.max' => 'No. HP siswa maksimal 20 karakter.',
            'no_hp_ortu1.max' => 'No. HP ortu 1 maksimal 20 karakter.',
            'no_hp_ortu2.max' => 'No. HP ortu 2 maksimal 20 karakter.',
            'nama_ortu1.max' => 'Nama ortu 1 maksimal 100 karakter.',
            'nama_ortu2.max' => 'Nama ortu 2 maksimal 100 karakter.',
            'nama_wali.max' => 'Nama wali maksimal 100 karakter.',
        ];
    }
}
