<?php

namespace App\Http\Requests;

class PenghargaanUpdateRequest extends PenghargaanStoreRequest
{
    /**
     * Saat update, kecualikan record yang sedang diedit dari cek duplikat.
     */
    protected function validateNoDuplicate($validator, mixed $siswaId, mixed $idpasal, mixed $tanggal): void
    {
        if (blank($idpasal) || blank($siswaId) || blank($tanggal)) {
            return;
        }

        $tanggalDate = \Carbon\Carbon::parse($tanggal)->toDateString();

        // Ambil id record yang sedang diedit dari route parameter
        $currentId = $this->route('penghargaan')?->getKey();

        $existing = \App\Models\Penghargaan::query()
            ->where('siswa_id', $siswaId)
            ->where('idpasal', $idpasal)
            ->whereDate('tgl', $tanggalDate)
            ->when($currentId, fn ($q) => $q->where('idpen', '!=', $currentId))
            ->exists();

        if ($existing) {
            $validator->errors()->add(
                'idpasal',
                'Penghargaan jenis ini sudah pernah diberikan kepada siswa yang sama pada hari ini. Poin yang sama baru dapat diberikan kembali pada hari berikutnya.'
            );
        }
    }
}
