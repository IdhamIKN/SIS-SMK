<?php

namespace App\Services;

use App\Models\AbsenSiswa;
use App\Models\PengajuanIzin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Intervention\Image\ImageManager;

class IzinService
{
    public function validateRequest(Request $request, ?PengajuanIzin $izin = null): array
    {
        return $request->validate(
            $this->validationRules($izin),
            $this->validationMessages(),
            $this->validationAttributes()
        );
    }

    public function buildPayload(array $validated, int $siswaId, ?string $buktiPath = null): array
    {
        $payload = [
            'siswa_id' => $siswaId,
            'jenis' => $validated['jenis'],
            'alasan' => $validated['alasan'],
            ...$this->resolveTanggalData($validated),
        ];

        if ($buktiPath) {
            $payload['bukti'] = $buktiPath;
        }

        return $payload;
    }

    public function resolveTanggalData(array $validated): array
    {
        if ($this->isRangeJenis($validated['jenis'])) {
            return [
                'tanggal_mulai' => $validated['tanggal_mulai'],
                'tanggal_sampai' => $validated['tanggal_sampai'],
            ];
        }

        return [
            'tanggal_mulai' => $validated['tanggal_izin'],
            'tanggal_sampai' => $validated['tanggal_izin'],
        ];
    }

    public function ensureTidakBentrokDenganAbsensi(int $siswaId, array $validated): void
    {
        $tanggal = $this->resolveTanggalData($validated);

        $absensi = AbsenSiswa::query()
            ->where('siswa_id', $siswaId)
            ->whereBetween('tanggal', [$tanggal['tanggal_mulai'], $tanggal['tanggal_sampai']])
            ->orderBy('tanggal')
            ->orderBy('jenis')
            ->get(['tanggal', 'jenis', 'status']);

        if ($absensi->isEmpty()) {
            return;
        }

        $rincian = $absensi
            ->map(fn ($item) => sprintf(
                '%s (%s - %s)',
                $item->tanggal->translatedFormat('d M Y'),
                $item->jenis,
                $item->status
            ))
            ->unique()
            ->implode(', ');

        throw ValidationException::withMessages([
            'tanggal_mulai' => 'Pengajuan izin tidak bisa diproses karena sudah ada data absensi pada tanggal tersebut: '.$rincian.'.',
        ]);
    }

    public function storeOptimizedBukti($file, int $siswaId): string
    {
        $image = ImageManager::gd()->read($file);
        $encoded = $image->scale(width: 1200)->toJpeg(70);

        $dir = 'izin/'.date('Y/m');
        Storage::disk('public')->makeDirectory($dir, 0755, true, false);

        $filename = time().'_'.$siswaId.'_bukti.jpg';
        $path = $dir.'/'.$filename;

        Storage::disk('public')->put($path, (string) $encoded);

        return $path;
    }

    public function deleteBukti(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    public function validationMessages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'required_if' => ':attribute wajib diisi untuk jenis izin yang dipilih.',
            'in' => ':attribute yang dipilih tidak valid.',
            'date' => ':attribute harus berupa tanggal yang valid.',
            'after_or_equal' => ':attribute tidak valid. Pastikan tanggal akhir tidak sebelum tanggal mulai atau sebelum hari ini.',
            'string' => ':attribute harus berupa teks.',
            'max' => ':attribute tidak boleh lebih dari :max karakter/KB.',
            'image' => ':attribute harus berupa file gambar.',
            'mimes' => ':attribute harus berformat: :values.',
        ];
    }

    public function validationAttributes(): array
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

    public function validationRules(?PengajuanIzin $izin = null): array
    {
        return [
            'jenis' => ['required', 'in:izin_sakit,izin_pulang_cepat,izin_terlambat,izin_lainnya'],
            'tanggal_izin' => ['required_if:jenis,izin_pulang_cepat,izin_terlambat', 'nullable', 'date', 'after_or_equal:today'],
            'tanggal_mulai' => ['required_if:jenis,izin_sakit,izin_lainnya', 'nullable', 'date', 'after_or_equal:today'],
            'tanggal_sampai' => ['required_if:jenis,izin_sakit,izin_lainnya', 'nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'alasan' => ['required', 'string', 'max:500'],
            'bukti' => [
                Rule::requiredIf(function () use ($izin) {
                    if (! request()->jenis || request()->jenis === 'izin_pulang_cepat') {
                        return false;
                    }

                    return $izin === null || ! $izin->bukti;
                }),
                'nullable',
                'image',
                'mimes:jpeg,jpg,png',
                'max:10240',
            ],
        ];
    }

    protected function isRangeJenis(string $jenis): bool
    {
        return in_array($jenis, ['izin_sakit', 'izin_lainnya'], true);
    }
}
