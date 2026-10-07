<?php

namespace App\Exports;

use App\Models\LaporanKehadiranGuru;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanKehadiranGuruExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        private string $tanggalMulai,
        private string $tanggalSelesai,
        private ?int   $kelasId = null,
        private ?int   $gtkId   = null,
        private ?int   $mapelId = null,   // belum dipakai — hook untuk filter mapel via jadwalKbm
    ) {}

    public function collection(): Collection
    {
        $query = LaporanKehadiranGuru::with(['gtk', 'kelas', 'jadwalKbm', 'dilaporkanOlehSiswa'])
            ->whereBetween('tanggal', [$this->tanggalMulai, $this->tanggalSelesai])
            ->orderBy('tanggal')
            ->orderBy('jam_ke');

        if ($this->kelasId) {
            $query->where('kelas_id', $this->kelasId);
        }

        if ($this->gtkId) {
            $query->where('gtk_id', $this->gtkId);
        }

        if ($this->mapelId) {
            // Filter berdasarkan mata pelajaran via jadwal KBM
            $query->whereHas('jadwalKbm', function ($q) {
                $q->where('mata_pelajaran_id', $this->mapelId);
            });
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Hari',
            'Jam Ke',
            'Jam Mulai',
            'Jam Selesai',
            'Kelas',
            'Mata Pelajaran',
            'Guru',
            'Status',
            'Waktu Laporan',
            'Pelapor',
            'Catatan',
        ];
    }

    private int $row = 0;

    public function map($laporan): array
    {
        $this->row++;

        $hari = [
            0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa',
            3 => 'Rabu',   4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu',
        ];

        $jamMulai   = $laporan->jadwalKbm?->jam_mulai
            ? Carbon::parse($laporan->jadwalKbm->jam_mulai)->format('H:i')
            : '-';
        $jamSelesai = $laporan->jadwalKbm?->jam_selesai
            ? Carbon::parse($laporan->jadwalKbm->jam_selesai)->format('H:i')
            : '-';

        $pelapor = $laporan->dilaporkan_oleh_siswa_id
            ? ($laporan->dilaporkanOlehSiswa?->nama_lengkap ?? 'Siswa')
            : 'Guru Sendiri';

        return [
            $this->row,
            Carbon::parse($laporan->tanggal)->format('d/m/Y'),
            $hari[Carbon::parse($laporan->tanggal)->dayOfWeek] ?? '-',
            $laporan->jam_ke ?? '-',
            $jamMulai,
            $jamSelesai,
            $laporan->kelas?->nama_kelas ?? '-',
            $laporan->jadwalKbm?->mata_pelajaran ?? '-',
            $laporan->gtk?->nama_lengkap ?? '-',
            $laporan->status_label ?? ucfirst($laporan->status ?? '-'),
            $laporan->waktu_laporan ? Carbon::parse($laporan->waktu_laporan)->format('d/m/Y H:i') : '-',
            $pelapor,
            $laporan->catatan ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $sheet->getHighestRow();
        $lastCol = 'M';

        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font'    => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0F766E']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
        ]);

        if ($lastRow > 1) {
            $sheet->getStyle("A2:{$lastCol}{$lastRow}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
            ]);
        }

        return [];
    }

    public function title(): string
    {
        return 'Kehadiran Guru';
    }
}
