<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class PelanggaranStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'siswa_id'       => ['required', 'integer', 'exists:siswas,id'],
            'tanggal'        => ['required', 'date'],
            'tahun_ajaran'   => ['required', 'string', 'max:9'],
            'idpasal'        => ['nullable', 'string', 'max:20'],
            'isi'            => ['required', 'string', 'max:500'],
            'poin'           => ['required', 'integer', 'min:0'],
            'catatan'        => ['nullable', 'string', 'max:200'],
            // ✅ Tambahkan di sini, bukan di controller
            'kirim_wa'       => ['nullable', 'boolean'],
            'nomor_wa'       => [
                'nullable',
                'required_if:kirim_wa,1',
                'regex:/^(08|628)[0-9]{8,12}$/',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'siswa_id.required'    => 'Siswa wajib dipilih.',
            'siswa_id.exists'      => 'Siswa tidak ditemukan.',
            'tanggal.required'     => 'Tanggal wajib diisi.',
            'isi.required'         => 'Uraian pelanggaran wajib diisi.',
            'poin.required'        => 'Poin wajib diisi.',
            'nomor_wa.required_if' => 'Nomor HP wajib diisi jika notifikasi WhatsApp diaktifkan.',
            'nomor_wa.regex'       => 'Format nomor HP tidak valid (contoh: 0812xxx atau 628xxx).',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $this->validateNoDuplicate($v);
        });
    }

    protected function validateNoDuplicate(Validator $validator): void
    {
        $siswaId = $this->input('siswa_id');
        $idpasal = $this->input('idpasal');
        $tanggal = $this->input('tanggal');

        if (blank($idpasal) || blank($siswaId) || blank($tanggal)) {
            return;
        }

        $tanggalDate = \Carbon\Carbon::parse($tanggal)->toDateString();

        $existing = \App\Models\Pelanggaran::query()
            ->where('siswa_id', $siswaId)
            ->where('idpasal', $idpasal)
            ->whereDate('tgl', $tanggalDate)
            ->exists();

        if ($existing) {
            $validator->errors()->add(
                'idpasal',
                'Pelanggaran jenis ini sudah pernah diberikan kepada siswa yang sama pada tanggal tersebut.'
            );
        }
    }
}
