<?php

namespace App\Exports;

use App\Models\AbsenSiswa;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Export rekap absensi — pola unified (1 record per siswa per hari).
 * Satu baris = satu hari kehadiran, menampilkan info masuk & pulang sekaligus.
 */
class RekapAbsenExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private string  $tanggalMulai,
        private string  $tanggalSelesai,
        private ?int    $kelasId = null,
        private ?string $status  = null,
    ) {}

    public function collection(): Collection
    {
        $query = AbsenSiswa::with(['siswa.kelas', 'siswa.kelas.jurusan'])
            ->whereBetween('tanggal', [$this->tanggalMulai, $this->tanggalSelesai]);

        if ($this->kelasId) {
            $query->where('kelas_id', $this->kelasId);
        }

        if ($this->status) {
            $query->where(function ($q) {
                $q->where('status_masuk', $this->status)
                  ->orWhere(function ($q2) {
                      $q2->whereNull('status_masuk')->where('status', $this->status);
                  });
            });
        }

        return $query
            ->orderBy('tanggal', 'desc')
            ->orderBy('kelas_id')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'NIS',
            'Nama Siswa',
            'Kelas',
            'Jurusan',
            'Jam Masuk',
            'Status Masuk',
            'Jam Pulang',
            'Status Pulang',
            'Catatan',
        ];
    }

    public function map($absen): array
    {
        $statusMasuk  = $absen->status_masuk ?? $absen->status ?? 'alfa';
        $statusPulang = $absen->status_pulang ?? '-';

        $jamMasuk = null;
        if (! empty($absen->jam_masuk)) {
            $jamMasuk = substr($absen->jam_masuk, 0, 5); // HH:MM
        } elseif ($absen->waktu_absen) {
            $jamMasuk = Carbon::parse($absen->waktu_absen)->format('H:i');
        }

        $jamPulang = null;
        if (! empty($absen->jam_pulang)) {
            $jamPulang = substr($absen->jam_pulang, 0, 5);
        }

        return [
            Carbon::parse($absen->tanggal)->format('d/m/Y'),
            $absen->siswa->nis ?? '',
            $absen->siswa->nama_lengkap ?? '',
            $absen->siswa->kelas->nama_kelas ?? '',
            $absen->siswa->kelas->jurusan->nama_jurusan ?? '',
            $jamMasuk ?? '-',
            ucfirst($statusMasuk),
            $jamPulang ?? '-',
            $statusPulang !== '-' ? ucfirst($statusPulang) : '-',
            $absen->catatan ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $sheet->getHighestRow();

        $sheet->getStyle('A1:J1')->applyFromArray([
            'font'    => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F81BD']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
        ]);

        $sheet->getStyle("A2:J{$lastRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
        ]);

        return [];
    }
}
