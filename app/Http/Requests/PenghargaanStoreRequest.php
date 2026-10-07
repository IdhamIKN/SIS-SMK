<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PenghargaanStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['superadmin', 'admin_tatib', 'bk', 'gtk', 'wali_kelas']) ?? false;
    }

    public function rules(): array
    {
        return [
            'siswa_id' => ['required', 'exists:siswas,id'],
            'tanggal' => ['required', 'date'],
            'tahun_ajaran' => ['required', 'string', 'max:9'],
            'idpasal' => ['nullable', 'string', 'max:5', 'exists:tblsubpasal,idpasal'],
            'isi' => ['required', 'string', 'max:500'],
            'poin' => ['required', 'integer'],
            'ket' => ['nullable', 'string', 'max:80'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // Tahun ajaran dikirim dari form tersembunyi; gunakan aktif hanya sebagai fallback.
        $this->merge([
            'tahun_ajaran' => (string) ($this->input('tahun_ajaran') ?: $this->tahunAjaranAktif()),
        ]);
    }

    private function tahunAjaranAktif(): string
    {
        try {
            return tahun_ajaran_aktif();
        } catch (\Throwable) {
            return config('sekolah.tahun_ajaran_aktif', now()->year.'/'.now()->addYear()->year);
        }
    }


    protected function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $data = $this->all();

            $idpasal = $data['idpasal'] ?? null;
            $poin = $data['poin'] ?? null;
            $tahun = $data['tahun_ajaran'] ?? null;
            $siswaId = $data['siswa_id'] ?? null;
            $tanggal = $data['tanggal'] ?? null;

            // ── Validasi rentang poin berdasarkan subpasal ────────────────
            if (filled($idpasal) && filled($poin) && filled($tahun)) {
                $row = \App\Models\TblSubPasal::query()
                    ->where('idpasal', $idpasal)
                    ->where('thnajaran', $tahun)
                    ->first();

                if (! $row) {
                    $row = \App\Models\TblSubPasal::query()
                        ->where('idpasal', $idpasal)
                        ->orderByDesc('thnajaran')
                        ->first();
                }

                if ($row) {
                    $min = (int) $row->skormin;
                    $max = (int) $row->skormax;
                    $poinInt = (int) $poin;

                    if ($poinInt < $min) {
                        $validator->errors()->add('poin', "Poin tidak boleh kurang dari {$min} untuk jenis ini.");
                    }

                    if ($poinInt > $max) {
                        $validator->errors()->add('poin', "Poin tidak boleh melebihi {$max} untuk jenis ini.");
                    }
                }
            }

            // ── Validasi duplikat: jenis penghargaan yang sama, siswa yang sama, hari yang sama ──
            $this->validateNoDuplicate($validator, $siswaId, $idpasal, $tanggal);
        });
    }

    /**
     * Cegah pencatatan penghargaan yang sama (idpasal + siswa_id) dalam satu hari.
     * Method ini dapat di-override di UpdateRequest untuk mengecualikan record saat ini.
     */
    protected function validateNoDuplicate($validator, mixed $siswaId, mixed $idpasal, mixed $tanggal): void
    {
        if (blank($idpasal) || blank($siswaId) || blank($tanggal)) {
            return;
        }

        $tanggalDate = \Carbon\Carbon::parse($tanggal)->toDateString();

        $existing = \App\Models\Penghargaan::query()
            ->where('siswa_id', $siswaId)
            ->where('idpasal', $idpasal)
            ->whereDate('tgl', $tanggalDate)
            ->exists();

        if ($existing) {
            $validator->errors()->add(
                'idpasal',
                'Penghargaan jenis ini sudah pernah diberikan kepada siswa yang sama pada hari ini. Poin yang sama baru dapat diberikan kembali pada hari berikutnya.'
            );
        }
    }

    public function messages(): array
    {
        return [
            'siswa_id.required' => 'Siswa wajib dipilih.',
            'siswa_id.exists' => 'Siswa yang dipilih tidak ditemukan.',
            'tanggal.required' => 'Tanggal penghargaan wajib diisi.',
            'tahun_ajaran.required' => 'Tahun ajaran wajib diisi.',
            'idpasal.exists' => 'Jenis penghargaan tidak valid.',
            'isi.required' => 'Uraian penghargaan wajib diisi.',
            'poin.required' => 'Poin penghargaan wajib diisi.',
            'poin.integer' => 'Poin penghargaan harus berupa angka.',
        ];
    }
}
