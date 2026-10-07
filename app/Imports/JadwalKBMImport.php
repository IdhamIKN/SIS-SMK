<?php

namespace App\Imports;

use App\Models\GTK;
use App\Models\JadwalKBM;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\SetJam;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class JadwalKBMImport implements ToCollection, WithHeadingRow
{
    private int $created   = 0;
    private int $updated   = 0;
    private int $skipped   = 0;
    private array $errors  = [];

    // Cache lookup agar tidak query berulang per baris
    private array $kelasCache   = [];
    private array $gtkCache     = [];
    private array $mapelCache   = [];
    private array $jamCache     = [];

    private const VALID_HARI = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

    public function collection(Collection $rows): void
    {
        // Preload semua data master ke cache agar efisien
        $this->preloadCache();

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // +2 karena baris 1 = heading
            $this->processRow($row->toArray(), $rowNum);
        }
    }

    private function preloadCache(): void
    {
        Kelas::all()->each(function (Kelas $k) {
            $key = strtolower(trim($k->nama_kelas));
            $this->kelasCache[$key] = $k;
        });

        GTK::all()->each(function (GTK $g) {
            // Index by kd_guru (kode) dan nama_lengkap (lowercase)
            if ($g->kd_guru) {
                $this->gtkCache[strtolower(trim($g->kd_guru))] = $g;
            }
            $this->gtkCache[strtolower(trim($g->nama_lengkap))] = $g;
        });

        MataPelajaran::all()->each(function (MataPelajaran $m) {
            if ($m->kode_mapel) {
                $this->mapelCache[strtolower(trim($m->kode_mapel))] = $m;
            }
            $this->mapelCache[strtolower(trim($m->nama_mapel))] = $m;
        });

        SetJam::where('statusjam', 1)->get()->each(function (SetJam $j) {
            // Index by nama_jam dan id_jam
            $this->jamCache[strtolower(trim((string) $j->nama_jam))] = $j;
            $this->jamCache[(string) $j->id_jam] = $j;
        });
    }

    private function processRow(array $row, int $rowNum): void
    {
        try {
            // ── Normalisasi kolom ──────────────────────────────────
            $namaKelas    = trim((string) ($row['nama_kelas']     ?? $row['kelas']           ?? ''));
            $kdGuru       = trim((string) ($row['kd_guru']        ?? $row['kode_guru']       ?? ''));
            $namaGuru     = trim((string) ($row['nama_guru']      ?? $row['guru']            ?? ''));
            $kodeMapel    = trim((string) ($row['kode_mapel']     ?? $row['kd_mapel']        ?? ''));
            $namaMapel    = trim((string) ($row['nama_mapel']     ?? $row['mata_pelajaran']  ?? ''));
            $hari         = ucfirst(strtolower(trim((string) ($row['hari'] ?? ''))));
            $jamMulaiStr  = trim((string) ($row['jam_mulai']      ?? $row['jam_ke']          ?? ''));
            $jamSelesaiStr= trim((string) ($row['jam_selesai']    ?? $row['jam_ke_selesai']  ?? ''));
            $tahunAjaran  = trim((string) ($row['tahun_ajaran']   ?? $row['tahun']           ?? ''));
            $semester     = trim((string) ($row['semester']       ?? '1'));

            // ── Validasi kolom wajib ───────────────────────────────
            if (empty($namaKelas)) {
                $this->addError($rowNum, 'Kolom nama_kelas kosong'); return;
            }
            if (empty($kdGuru) && empty($namaGuru)) {
                $this->addError($rowNum, 'Kolom kd_guru / nama_guru kosong'); return;
            }
            if (empty($kodeMapel) && empty($namaMapel)) {
                $this->addError($rowNum, 'Kolom kode_mapel / nama_mapel kosong'); return;
            }
            if (empty($hari) || !in_array($hari, self::VALID_HARI, true)) {
                $this->addError($rowNum, "Hari '{$hari}' tidak valid. Gunakan: Senin s.d. Minggu"); return;
            }
            if (empty($jamMulaiStr)) {
                $this->addError($rowNum, 'Kolom jam_mulai kosong'); return;
            }
            if (empty($jamSelesaiStr)) {
                $this->addError($rowNum, 'Kolom jam_selesai kosong'); return;
            }
            if (empty($tahunAjaran)) {
                $this->addError($rowNum, 'Kolom tahun_ajaran kosong'); return;
            }
            if (!in_array($semester, ['1', '2'], true)) {
                $this->addError($rowNum, "Semester '{$semester}' tidak valid. Gunakan 1 atau 2"); return;
            }

            // ── Lookup Kelas ───────────────────────────────────────
            $kelas = $this->kelasCache[strtolower($namaKelas)] ?? null;
            if (!$kelas) {
                $this->addError($rowNum, "Kelas '{$namaKelas}' tidak ditemukan"); return;
            }

            // ── Lookup GTK ─────────────────────────────────────────
            $gtk = null;
            if ($kdGuru) {
                $gtk = $this->gtkCache[strtolower($kdGuru)] ?? null;
            }
            if (!$gtk && $namaGuru) {
                $gtk = $this->gtkCache[strtolower($namaGuru)] ?? null;
            }
            if (!$gtk) {
                $label = $kdGuru ?: $namaGuru;
                $this->addError($rowNum, "Guru '{$label}' tidak ditemukan"); return;
            }

            // ── Lookup MataPelajaran ───────────────────────────────
            $mapel = null;
            if ($kodeMapel) {
                $mapel = $this->mapelCache[strtolower($kodeMapel)] ?? null;
            }
            if (!$mapel && $namaMapel) {
                $mapel = $this->mapelCache[strtolower($namaMapel)] ?? null;
            }
            if (!$mapel) {
                $label = $kodeMapel ?: $namaMapel;
                $this->addError($rowNum, "Mata pelajaran '{$label}' tidak ditemukan"); return;
            }

            // ── Lookup SetJam (mulai) ──────────────────────────────
            $jamMulaiModel = $this->resolveJam($jamMulaiStr);
            if (!$jamMulaiModel) {
                $this->addError($rowNum, "Jam mulai '{$jamMulaiStr}' tidak ditemukan di tabel jam"); return;
            }

            // ── Lookup SetJam (selesai) ────────────────────────────
            $jamSelesaiModel = $this->resolveJam($jamSelesaiStr);
            if (!$jamSelesaiModel) {
                $this->addError($rowNum, "Jam selesai '{$jamSelesaiStr}' tidak ditemukan di tabel jam"); return;
            }

            $jamMulaiVal   = $jamMulaiModel->time_in->format('H:i:s');
            $jamSelesaiVal = $jamSelesaiModel->time_out->format('H:i:s');

            if ($jamMulaiVal >= $jamSelesaiVal) {
                $this->addError($rowNum, "Jam selesai harus setelah jam mulai (baris {$rowNum})"); return;
            }

            // ── Cek konflik kelas ──────────────────────────────────
            $conflict = JadwalKBM::where('kelas_id', $kelas->id)
                ->where('hari', $hari)
                ->where('jam_mulai', '<', $jamSelesaiVal)
                ->where('jam_selesai', '>', $jamMulaiVal)
                ->first();

            if ($conflict) {
                // Jika sama persis (guru + mapel sama) → update
                if ($conflict->gtk_id == $gtk->id && $conflict->mata_pelajaran_id == $mapel->id) {
                    $conflict->update([
                        'jam_ke'         => $jamMulaiModel->id_jam,
                        'jam_mulai'      => $jamMulaiVal,
                        'jam_selesai'    => $jamSelesaiVal,
                        'mata_pelajaran' => $mapel->nama_mapel,
                        'tahun_ajaran'   => $tahunAjaran,
                        'semester'       => (int) $semester,
                    ]);
                    $this->updated++;
                    return;
                }

                $this->addError($rowNum,
                    "Konflik jadwal: {$kelas->nama_kelas} hari {$hari} jam {$jamMulaiStr}–{$jamSelesaiStr} sudah ada jadwal lain"
                );
                $this->skipped++;
                return;
            }

            // ── Cek konflik guru ───────────────────────────────────
            $guruConflict = JadwalKBM::where('gtk_id', $gtk->id)
                ->where('hari', $hari)
                ->where('jam_mulai', '<', $jamSelesaiVal)
                ->where('jam_selesai', '>', $jamMulaiVal)
                ->first();

            if ($guruConflict) {
                $this->addError($rowNum,
                    "Konflik jadwal guru: {$gtk->nama_lengkap} sudah mengajar di {$hari} jam tersebut"
                );
                $this->skipped++;
                return;
            }

            // ── Insert ─────────────────────────────────────────────
            DB::table('jadwal_kbm')->insert([
                'kelas_id'         => $kelas->id,
                'gtk_id'           => $gtk->id,
                'mata_pelajaran_id'=> $mapel->id,
                'hari'             => $hari,
                'jam_ke'           => $jamMulaiModel->id_jam,
                'jam_mulai'        => $jamMulaiVal,
                'jam_selesai'      => $jamSelesaiVal,
                'mata_pelajaran'   => $mapel->nama_mapel,
                'tahun_ajaran'     => $tahunAjaran,
                'semester'         => (int) $semester,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            $this->created++;
        } catch (\Throwable $e) {
            $this->addError($rowNum, 'Error tidak terduga: ' . $e->getMessage());
        }
    }

    /**
     * Resolve jam dari string: bisa nama_jam, id_jam, atau HH:MM.
     * Untuk import, prioritaskan kelompok 'reguler' agar tidak ambigu
     * antara reguler dan reguler_1112 yang punya waktu sama.
     */
    private function resolveJam(string $input): ?SetJam
    {
        // Coba langsung sebagai id atau nama
        $key = strtolower(trim($input));
        if (isset($this->jamCache[$key])) {
            return $this->jamCache[$key];
        }

        // Coba hapus prefix "Jam " / "jam "
        $stripped    = preg_replace('/^jam\s*/i', '', $input);
        $keyStripped = strtolower(trim($stripped));
        if (isset($this->jamCache[$keyStripped])) {
            return $this->jamCache[$keyStripped];
        }

        // Coba parse sebagai format jam HH:MM, cocokkan dengan time_in model
        // Prioritaskan kelompok reguler agar tidak bentrok dengan reguler_1112
        if (preg_match('/^\d{1,2}:\d{2}/', $input)) {
            $preferred = null;
            foreach ($this->jamCache as $jam) {
                if (!($jam instanceof SetJam)) continue;
                $timeIn  = $jam->time_in->format('H:i');
                $timeOut = $jam->time_out->format('H:i');
                if ($timeIn === substr($input, 0, 5) || $timeOut === substr($input, 0, 5)) {
                    if ($jam->kelompok_jam === SetJam::KELOMPOK_REGULER) {
                        return $jam; // langsung kembalikan reguler jika ketemu
                    }
                    $preferred = $preferred ?? $jam; // fallback ke lainnya
                }
            }
            if ($preferred) return $preferred;
        }

        return null;
    }

    private function addError(int $rowNum, string $message): void
    {
        $this->errors[] = "Baris {$rowNum}: {$message}";
        $this->skipped++;
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
}
