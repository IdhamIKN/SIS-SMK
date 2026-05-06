<?php

namespace App\Exports;

use App\Models\AbsenEvent;
use App\Models\AbsenSiswa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapAbsenExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    private string $tanggalMulai;

    private string $tanggalSelesai;

    private ?int $kelasId;

    private ?string $status;

    public function __construct(string $tanggalMulai, string $tanggalSelesai, ?int $kelasId = null, ?string $status = null)
    {
        $this->tanggalMulai = $tanggalMulai;
        $this->tanggalSelesai = $tanggalSelesai;
        $this->kelasId = $kelasId;
        $this->status = $status;
    }

    public function collection(): Collection
    {
        // Query absen siswa
        $absenQuery = AbsenSiswa::with(['siswa.kelas', 'siswa.kelas.jurusan'])
            ->whereBetween('tanggal', [$this->tanggalMulai, $this->tanggalSelesai]);

        $absenEventQuery = AbsenEvent::with(['siswa.kelas', 'siswa.kelas.jurusan', 'event'])
            ->whereBetween('waktu_scan', [$this->tanggalMulai.' 00:00:00', $this->tanggalSelesai.' 23:59:59']);

        // Filter kelas
        if ($this->kelasId) {
            $absenQuery->where('kelas_id', $this->kelasId);
            $absenEventQuery->whereHas('siswa', fn ($q) => $q->where('kelas_id', $this->kelasId));
        }

        // Filter status
        if ($this->status) {
            $absenQuery->where('status', $this->status);
        }

        $absenSiswa = $absenQuery->orderBy('tanggal', 'desc')->orderBy('waktu_absen', 'desc')->get();
        $absenEvent = $absenEventQuery->orderBy('waktu_scan', 'desc')->get();

        // Gabungkan data
        $rekap = collect();

        foreach ($absenSiswa as $absen) {
            $rekap->push([
                'tipe' => 'absen_siswa',
                'tanggal' => $absen->tanggal,
                'waktu' => $absen->waktu_absen,
                'nis' => $absen->siswa->nis ?? '',
                'nama' => $absen->siswa->nama_lengkap ?? '',
                'kelas' => $absen->siswa->kelas->nama_kelas ?? '',
                'jurusan' => $absen->siswa->kelas->jurusan->nama_jurusan ?? '',
                'jenis' => $absen->jenis,
                'status' => $absen->status,
                'keterangan' => 'Absen Sekolah',
                'lokasi' => '',
                'catatan' => $absen->catatan ?? '',
            ]);
        }

        foreach ($absenEvent as $absen) {
            $rekap->push([
                'tipe' => 'absen_event',
                'tanggal' => $absen->waktu_scan->format('Y-m-d'),
                'waktu' => $absen->waktu_scan,
                'nis' => $absen->siswa->nis ?? '',
                'nama' => $absen->siswa->nama_lengkap ?? '',
                'kelas' => $absen->siswa->kelas->nama_kelas ?? '',
                'jurusan' => $absen->siswa->kelas->jurusan->nama_jurusan ?? '',
                'jenis' => $absen->jenis,
                'status' => 'hadir',
                'keterangan' => 'Event: '.$absen->event->nama_event,
                'lokasi' => $absen->event->lokasi ?? '',
                'catatan' => '',
            ]);
        }

        return $rekap->sortByDesc('waktu')->values();
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Waktu',
            'NIS',
            'Nama Siswa',
            'Kelas',
            'Jurusan',
            'Jenis',
            'Status',
            'Keterangan',
            'Lokasi',
            'Catatan',
        ];
    }

    public function map($row): array
    {
        return [
            $row['tanggal'],
            $row['waktu']->format('H:i:s'),
            $row['nis'],
            $row['nama'],
            $row['kelas'],
            $row['jurusan'],
            $row['jenis'] === 'masuk' ? 'Masuk' : 'Pulang',
            $row['tipe'] === 'absen_event' ? 'Event' : ucfirst($row['status']),
            $row['keterangan'],
            $row['lokasi'],
            $row['catatan'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header style
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F81BD'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Data borders
        $sheet->getStyle('A2:K'.($sheet->getHighestRow()))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
        ]);

        return [];
    }
}
