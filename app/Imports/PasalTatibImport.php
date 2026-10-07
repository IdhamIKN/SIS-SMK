<?php

namespace App\Imports;

use App\Models\TblKategori;
use App\Models\TblPasal;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Throwable;

class PasalTatibImport implements SkipsEmptyRows, ToCollection, WithHeadingRow
{
    private int $created = 0;
    private int $updated = 0;
    private int $skipped = 0;
    private array $errors = [];

    public function __construct(
        private readonly string $defaultJenis,
        private readonly string $defaultTahunAjaran,
        private readonly bool $supportsStatus = true
    ) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $line = $index + 2;

            try {
                $this->importRow($row, $line);
            } catch (Throwable $exception) {
                $this->skipped++;
                $this->errors[] = "Baris {$line}: ".$exception->getMessage();
            }
        }
    }

    public function summary(): array
    {
        return [
            'created' => $this->created,
            'updated' => $this->updated,
            'skipped' => $this->skipped,
        ];
    }

    public function errors(): array
    {
        return $this->errors;
    }

    private function importRow(Collection $row, int $line): void
    {
        $idPasal = strtoupper(trim((string) $this->value($row, ['idpasal', 'kode_pasal', 'kode'])));
        $idKategori = strtoupper(trim((string) $this->value($row, ['idkategori', 'kategori_id', 'kode_kategori'])));
        $uraian = trim((string) $this->value($row, ['pasal', 'uraian', 'deskripsi']));
        $tahunAjaran = trim((string) ($this->value($row, ['tahun_ajaran', 'thnajaran', 'tahun']) ?: $this->defaultTahunAjaran));
        $jenis = $this->normalizeJenis($this->value($row, ['jenis', 'tipe', 'group']) ?: $this->defaultJenis);
        $skorMin = $this->integerValue($this->value($row, ['skormin', 'skor_min', 'poin_min', 'poin']));
        $skorMax = $this->integerValue($this->value($row, ['skormax', 'skor_max', 'poin_max', 'poin']));
        $urut = $this->integerValue($this->value($row, ['urut', 'no', 'nomor']), null);

        if ($idPasal === '' && $idKategori === '' && $uraian === '') {
            return;
        }

        if ($idPasal === '' || strlen($idPasal) > 5) {
            throw new \InvalidArgumentException('kode pasal wajib diisi dan maksimal 5 karakter.');
        }

        if ($idKategori === '' || strlen($idKategori) > 5) {
            throw new \InvalidArgumentException('kode kategori wajib diisi dan maksimal 5 karakter.');
        }

        if ($uraian === '') {
            throw new \InvalidArgumentException('uraian pasal wajib diisi.');
        }

        if ($tahunAjaran === '' || strlen($tahunAjaran) > 9) {
            throw new \InvalidArgumentException('tahun ajaran wajib diisi dan maksimal 9 karakter.');
        }

        if ($skorMin === null || $skorMax === null) {
            throw new \InvalidArgumentException('skormin dan skormax wajib berupa angka.');
        }

        if ($skorMax < $skorMin) {
            throw new \InvalidArgumentException('skormax tidak boleh lebih kecil dari skormin.');
        }

        $idGroup = $jenis === 'penghargaan' ? 'R' : 'P';
        $kategori = TblKategori::query()->where('idkategori', $idKategori)->first();

        if (! $kategori) {
            throw new \InvalidArgumentException("kategori {$idKategori} tidak ditemukan.");
        }

        if ($kategori->idgroup !== $idGroup) {
            throw new \InvalidArgumentException("kategori {$idKategori} tidak sesuai dengan jenis {$jenis}.");
        }

        $existing = TblPasal::query()->where('idpasal', $idPasal)->first();

        if ($existing) {
            $this->skipped++;

            return;
        }

        $status = $this->statusValue($this->value($row, ['status_aktif', 'status', 'aktif']), null);
        $urut = $urut ?? $this->nextUrut($idKategori);

        DB::transaction(function () use ($idPasal, $idKategori, $urut, $status, $uraian, $skorMin, $skorMax, $tahunAjaran) {
            $payload = [
                'idkategori' => $idKategori,
                'urut' => $urut,
            ];

            if ($this->supportsStatus) {
                $payload['status_aktif'] = $status;
            }

            TblPasal::query()->create(array_merge($payload, [
                'idpasal' => $idPasal,
            ]));
            $this->created++;

            DB::table('tblsubpasal')
                ->where('idpasal', $idPasal)
                ->where('thnajaran', $tahunAjaran)
                ->delete();

            DB::table('tblsubpasal')->insert([
                'idpasal' => $idPasal,
                'pasal' => $uraian,
                'skormin' => $skorMin,
                'skormax' => $skorMax,
                'thnajaran' => $tahunAjaran,
            ]);
        });
    }

    private function value(Collection $row, array $keys): mixed
    {
        foreach ($keys as $key) {
            if ($row->has($key) && filled($row->get($key))) {
                return $row->get($key);
            }
        }

        return null;
    }

    private function integerValue(mixed $value, ?int $default = 0): ?int
    {
        if ($value === null || $value === '') {
            return $default;
        }

        if (! is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    private function normalizeJenis(mixed $value): string
    {
        $value = strtolower(trim((string) $value));

        return in_array($value, ['r', 'reward', 'penghargaan'], true)
            ? 'penghargaan'
            : 'pelanggaran';
    }

    private function statusValue(mixed $value, ?TblPasal $existing): bool
    {
        if ($value === null || $value === '') {
            return $existing ? (bool) ($existing->status_aktif ?? true) : true;
        }

        $value = strtolower(trim((string) $value));

        if (in_array($value, ['1', 'aktif', 'active', 'ya', 'yes', 'y', 'true'], true)) {
            return true;
        }

        if (in_array($value, ['0', 'nonaktif', 'inactive', 'tidak', 'no', 'n', 'false'], true)) {
            return false;
        }

        return true;
    }

    private function nextUrut(string $idKategori): int
    {
        return ((int) TblPasal::query()->where('idkategori', $idKategori)->max('urut')) + 1;
    }
}
