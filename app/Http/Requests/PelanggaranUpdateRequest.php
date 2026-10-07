<?php

namespace App\Http\Requests;

use Illuminate\Validation\Validator;

class PelanggaranUpdateRequest extends PelanggaranStoreRequest
{
    /**
     * Saat update, kecualikan record yang sedang diedit dari cek duplikat,
     * sehingga guru bisa menyimpan ulang tanpa error duplikat pada entri miliknya.
     */
    protected function validateNoDuplicate(Validator $validator): void
    {
        $siswaId = $this->input('siswa_id');
        $idpasal = $this->input('idpasal');
        $tanggal = $this->input('tanggal');

        if (blank($idpasal) || blank($siswaId) || blank($tanggal)) {
            return;
        }

        $tanggalDate = \Carbon\Carbon::parse($tanggal)->toDateString();

        // Ambil id record yang sedang diedit dari route parameter
        $currentId = $this->route('pelanggaran')?->getKey();

        $existing = \App\Models\Pelanggaran::query()
            ->where('siswa_id', $siswaId)
            ->where('idpasal', $idpasal)
            ->whereDate('tgl', $tanggalDate)
            ->when($currentId, fn ($q) => $q->where('idpel', '!=', $currentId))
            ->exists();

        if ($existing) {
            $validator->errors()->add(
                'idpasal',
                'Pelanggaran jenis ini sudah pernah diberikan kepada siswa yang sama pada hari ini. Poin yang sama baru dapat diberikan kembali pada hari berikutnya.'
            );
        }
    }
}
