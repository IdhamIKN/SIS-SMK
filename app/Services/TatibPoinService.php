<?php

namespace App\Services;

use App\Jobs\SendTatibPoinNotification;
use App\Models\BatasPoin;
use App\Models\Pelanggaran;
use App\Models\Penghargaan;
use App\Models\Siswa;
use App\Models\TblKategori;
use App\Models\TblPasal;
use App\Models\TblSubPasal;
use App\Models\SubPasal;

use App\Models\TatibPointNotification;
use App\Models\TransaksiPoin;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TatibPoinService
{
    public const POIN_AWAL_EDARAN = 300;

    public static function edaranThresholds(): array
    {
        return [
            [
                'batas_ke' => 1,
                'poin' => 100,
                'tindakan' => 'Panggilan Orang Tua ke-1',
                'sanksi' => 'Sisa poin 200',
            ],
            [
                'batas_ke' => 2,
                'poin' => 200,
                'tindakan' => 'Panggilan Orang Tua ke-2',
                'sanksi' => 'Sisa poin 100',
            ],
            [
                'batas_ke' => 3,
                'poin' => 275,
                'tindakan' => 'Panggilan Orang Tua ke-3',
                'sanksi' => 'Sisa poin 25',
            ],
            [
                'batas_ke' => 4,
                'poin' => 300,
                'tindakan' => 'Point 0',
                'sanksi' => 'Tidak naik kelas / dikembalikan kepada orang tua',
            ],
        ];
    }

    public function tahunAjaranAktif(): string
    {
        try {
            return tahun_ajaran_aktif();
        } catch (\Throwable) {
            return config('sekolah.tahun_ajaran_aktif', now()->year . '/' . now()->addYear()->year);
        }
    }

    public function subPasalOptions(string $jenis, string $tahunAjaran, bool $includeInactive = false): Collection
    {
        // P: Pelanggaran, R: Penghargaan
        $idGroup = $jenis === 'pelanggaran' ? 'P' : 'R';
        $filterAktif = ! $includeInactive && Schema::hasColumn('tblpasal', 'status_aktif');

        // tblkategori -> tblpasal -> tblsubpasal
        $query = TblKategori::query()
            ->where('idgroup', $idGroup)
            ->with(['pasal' => function ($q) use ($filterAktif) {
                if ($filterAktif) {
                    $q->where('status_aktif', true);
                }

                $q->orderBy('urut')->orderBy('idpasal');
            }])
            ->with(['pasal.subPasal' => function ($q) use ($tahunAjaran) {
                $q->where('thnajaran', $tahunAjaran)
                    ->orderBy('idpasal');
            }]);

        $items = $query->get();

        $flat = $items
            ->flatMap(fn($kategori) => $kategori->pasal ?? [])
            ->flatMap(fn($pasal) => $pasal->subPasal ?? [])
            ->values();

        // fallback kalau tidak ada di tahun tersebut
        if ($flat->isEmpty()) {
            $queryAll = TblKategori::query()
                ->where('idgroup', $idGroup)
                ->with(['pasal' => function ($q) use ($filterAktif) {
                    if ($filterAktif) {
                        $q->where('status_aktif', true);
                    }

                    $q->orderBy('urut')->orderBy('idpasal');
                }])
                ->with(['pasal.subPasal' => function ($q) {
                    $q->orderBy('idpasal');
                }]);

            $itemsAll = $queryAll->get();

            $flat = $itemsAll
                ->flatMap(fn($kategori) => $kategori->pasal ?? [])
                ->flatMap(fn($pasal) => $pasal->subPasal ?? [])
                ->values();
        }

        // ambil record terakhir berdasarkan thnajaran
        return $flat
            ->groupBy('idpasal')
            ->map(fn(Collection $group) => $group->sortByDesc('thnajaran')->first())
            ->sortBy('idpasal')
            ->values();
    }


    public function findSubPasal(?string $idPasal, string $tahunAjaran): ?SubPasal
    {
        if (! $idPasal) {
            return null;
        }

        return SubPasal::query()
            ->where('idpasal', $idPasal)
            ->where('thnajaran', $tahunAjaran)
            ->first()
            ?? SubPasal::query()
            ->where('idpasal', $idPasal)
            ->orderByDesc('thnajaran')
            ->first();
    }

    public function totalPelanggaran(Siswa|int $siswa, string $tahunAjaran): int
    {
        $siswa = $siswa instanceof Siswa ? $siswa : Siswa::findOrFail($siswa);

        return (int) $this->transaksiSiswaQuery($siswa, $tahunAjaran)->sum('poinp');
    }

    public function totalPenghargaan(Siswa|int $siswa, string $tahunAjaran): int
    {
        $siswa = $siswa instanceof Siswa ? $siswa : Siswa::findOrFail($siswa);

        return (int) $this->transaksiSiswaQuery($siswa, $tahunAjaran)->sum('poinr');
    }

    public function poinAwalEdaran(): int
    {
        return self::POIN_AWAL_EDARAN;
    }

    public function sisaPoin(Siswa|int $siswa, string $tahunAjaran): int
    {
        $totalPelanggaran = $this->totalPelanggaran($siswa, $tahunAjaran);
        $totalPenghargaan = $this->totalPenghargaan($siswa, $tahunAjaran);

        return $this->hitungTotalPoin($totalPelanggaran, $totalPenghargaan);
    }

    /**
     * Hitung total poin siswa.
     * Poin awal (300) adalah modal/saldo awal, bukan batas maksimal.
     * Total = poinAwal + penghargaan - pelanggaran (minimal 0)
     */
    public function hitungTotalPoin(int $totalPelanggaran, int $totalPenghargaan): int
    {
        return max(0, self::POIN_AWAL_EDARAN + $totalPenghargaan - $totalPelanggaran);
    }

    /**
     * @deprecated Gunakan hitungTotalPoin() — metode ini hanya mengurangi pelanggaran tanpa
     *             memperhitungkan penghargaan. Dipertahankan agar tidak breaking di kode lama.
     */
    public function sisaPoinDariTotalPelanggaran(int $totalPelanggaran): int
    {
        return max(0, self::POIN_AWAL_EDARAN - $totalPelanggaran);
    }

    public function historiTransaksi(Siswa $siswa, string $tahunAjaran): Collection
    {
        return $this->transaksiSiswaQuery($siswa, $tahunAjaran)
            ->with(['creator', 'subPasal'])
            ->orderByDesc('tanggal')
            ->get();
    }

    public function historiTransaksiPaginated(Siswa $siswa, string $tahunAjaran, int $perPage = 15): LengthAwarePaginator
    {
        return $this->transaksiSiswaQuery($siswa, $tahunAjaran)
            ->with(['creator', 'subPasal'])
            ->orderByDesc('tanggal')
            ->paginate($perPage);
    }

    public function thresholds(): Collection
    {
        $batas = BatasPoin::query()->first();

        $thresholds = collect($batas?->thresholds() ?? []);

        if ($thresholds->isEmpty()) {
            $thresholds = collect(self::edaranThresholds());
        }

        return $thresholds
            ->map(fn(array $threshold) => array_merge($threshold, [
                // sisa_poin di threshold dihitung dari modal awal dikurangi pelanggaran (tanpa penghargaan),
                // karena threshold menggambarkan kondisi saat pelanggaran mencapai nilai tersebut.
                'sisa_poin' => $this->sisaPoinDariTotalPelanggaran((int) $threshold['poin']),
            ]))
            ->sortBy('poin')
            ->values();
    }

    public function thresholdStatus(Siswa $siswa, string $tahunAjaran): Collection
    {
        $total = $this->totalPelanggaran($siswa, $tahunAjaran);
        $notifications = TatibPointNotification::query()
            ->where('siswa_id', $siswa->id)
            ->where('tahun_ajaran', $tahunAjaran)
            ->get()
            ->keyBy('batas_ke');

        return $this->thresholds()->map(function (array $threshold) use ($total, $notifications) {
            $notification = $notifications->get($threshold['batas_ke']);

            return [
                'batas_ke' => $threshold['batas_ke'],
                'poin' => $threshold['poin'],
                'tindakan' => $threshold['tindakan'],
                'sanksi' => $threshold['sanksi'],
                'sisa_poin' => $threshold['sisa_poin'],
                'tercapai' => $total >= $threshold['poin'],
                'notification' => $notification,
            ];
        });
    }

    public function triggerAmbangNotifications(Siswa $siswa, string $tahunAjaran): int
    {
        $total = $this->totalPelanggaran($siswa, $tahunAjaran);
        $created = 0;

        foreach ($this->thresholds() as $threshold) {
            if ($total < $threshold['poin']) {
                continue;
            }

            try {
                $notification = TatibPointNotification::firstOrCreate(
                    [
                        'siswa_id'     => $siswa->id,
                        'tahun_ajaran' => $tahunAjaran,
                        'batas_ke'     => $threshold['batas_ke'],
                    ],
                    [
                        'batas_poin'   => $threshold['poin'],
                        'total_poin'   => $total,
                        'tindakan'     => $threshold['tindakan'],
                        'sanksi'       => $threshold['sanksi'],
                        'nomor_tujuan' => $this->resolveNomorWali($siswa),
                        'status'       => $this->resolveNomorWali($siswa) ? 'pending' : 'skipped',
                    ]
                );
            } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                // Race condition: another concurrent request already inserted this threshold.
                // Safe to skip — notification already exists.
                continue;
            }

            if (! $notification->wasRecentlyCreated) {
                continue;
            }

            if ($notification->status === 'pending') {
                SendTatibPoinNotification::dispatch($notification);
            }

            $created++;
        }

        return $created;
    }

    public function createPelanggaranTransaction(Pelanggaran $pelanggaran, Siswa $siswa, ?string $catatan = null): TransaksiPoin
    {
        $noreff  = $this->referenceNumber('PN', $pelanggaran->tgl, $pelanggaran->getKey());
        $payload = array_merge($this->baseTransactionPayload($siswa, $pelanggaran->tgl, $pelanggaran->tahun_ajaran), [
            'idpasal' => $pelanggaran->idpasal ?: '',
            'poinr'   => 0,
            'poinp'   => $pelanggaran->poin,
            'pelapor' => $this->limit($pelanggaran->pelapor, 50),
            'ket'     => $this->limit($catatan ?: 'Pelanggaran an. ' . $siswa->nama_lengkap, 200),
        ]);

        // Gunakan updateOrCreate berbasis noreff — unique index di DB mencegah duplikat
        $existing = TransaksiPoin::withTrashed()->where('noreff', $noreff)->lockForUpdate()->first();

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
            }
            $existing->update($payload);
            return $existing->refresh();
        }

        return TransaksiPoin::create(array_merge(['noreff' => $noreff], $payload));
    }

    public function createPenghargaanTransaction(Penghargaan $penghargaan, Siswa $siswa): TransaksiPoin
    {
        $noreff  = $this->referenceNumber('RW', $penghargaan->tgl, $penghargaan->getKey());
        $payload = array_merge($this->baseTransactionPayload($siswa, $penghargaan->tgl, $penghargaan->tahun_ajaran), [
            'idpasal' => $penghargaan->idpasal ?: '',
            'poinr'   => $penghargaan->poin,
            'poinp'   => 0,
            'pelapor' => $this->limit($penghargaan->pelapor, 50),
            'ket'     => $this->limit($penghargaan->ket ?: 'Penghargaan an. ' . $siswa->nama_lengkap, 200),
        ]);

        // Gunakan updateOrCreate berbasis noreff — unique index di DB mencegah duplikat
        $existing = TransaksiPoin::withTrashed()->where('noreff', $noreff)->lockForUpdate()->first();

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
            }
            $existing->update($payload);
            return $existing->refresh();
        }

        return TransaksiPoin::create(array_merge(['noreff' => $noreff], $payload));
    }

    public function deleteTransaction(string $prefix, Carbon|string|null $tanggal, int $sourceId): void
    {
        if (! $tanggal) {
            return;
        }

        TransaksiPoin::query()
            ->where('noreff', $this->referenceNumber($prefix, $tanggal, $sourceId))
            ->delete();
    }

    public function resolveNomorWali(Siswa $siswa): ?string
    {
        return collect([$siswa->no_hp_ortu1, $siswa->no_hp_ortu2, $siswa->no_hp_siswa])
            ->filter(fn($nomor) => filled($nomor))
            ->map(fn($nomor) => trim((string) $nomor))
            ->first();
    }

    public function snapshotSiswa(Siswa $siswa): array
    {
        $kelas = $siswa->kelas;
        $namaKelas = $kelas?->nama_kelas ?: '-';

        return [
            'nis' => $this->limit($siswa->nis ?: '', 20),
            'noreg' => $this->limit($siswa->noreg_legacy ?: $siswa->nis ?: (string) $siswa->id, 15),
            'nama' => $this->limit($siswa->nama_lengkap, 30),
            'kelas' => $this->limit($namaKelas, 15),
            'tingkat' => $this->limit($kelas?->tingkat ?: '', 4),
            'nmkelas' => $this->limit($namaKelas, 10),
        ];
    }

    private function transaksiSiswaQuery(Siswa $siswa, string $tahunAjaran): Builder
    {
        return TransaksiPoin::query()
            ->where('thajaran', $tahunAjaran)
            ->where(function (Builder $query) use ($siswa) {
                $query->where('siswa_id', $siswa->id);

                if ($siswa->noreg_legacy) {
                    $query->orWhere('noreg', $siswa->noreg_legacy);
                }

                if ($siswa->nis) {
                    $query->orWhere('nis', $siswa->nis);
                }
            });
    }

    private function baseTransactionPayload(Siswa $siswa, Carbon|string $tanggal, string $tahunAjaran): array
    {
        $snapshot = $this->snapshotSiswa($siswa);
        $tanggal = $tanggal instanceof Carbon ? $tanggal : Carbon::parse($tanggal);

        return [
            'siswa_id' => $siswa->id,
            'tanggal' => $tanggal,
            'tglreward' => $tanggal->toDateString(),
            'nis' => $snapshot['nis'],
            'noreg' => $snapshot['noreg'],
            'jamke' => 0,
            'kelas' => $snapshot['tingkat'],
            'nmkelas' => $snapshot['nmkelas'],
            'smester' => (int) config('sekolah.semester_aktif', 1),
            'thajaran' => $tahunAjaran,
            'userx' => $this->limit(auth()->user()?->name ?? 'system', 100),
            'created_by' => auth()->id() ?? null,
        ];
    }

    private function referenceNumber(string $prefix, Carbon|string $tanggal, int $sourceId): string
    {
        $tanggal = $tanggal instanceof Carbon ? $tanggal : Carbon::parse($tanggal);

        return $prefix . $tanggal->format('ymd') . $sourceId;
    }

    private function limit(?string $value, int $limit): string
    {
        return Str::limit((string) $value, $limit, '');
    }
}
