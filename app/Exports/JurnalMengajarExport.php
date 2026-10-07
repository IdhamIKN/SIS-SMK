<?php

namespace App\Exports;

use App\Models\GTK;
use App\Models\TblJurnalPembelajaran;
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

class JurnalMengajarExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        private string  $tanggalMulai,
        private string  $tanggalSelesai,
        private ?string $kelas     = null,
        private ?string $pelajaran = null,
        private ?int    $gtkId     = null,
        private ?string $kdGuru    = null,  // kd_guru owner jika bukan lihat-semua
    ) {}

    public function collection(): Collection
    {
        $query = TblJurnalPembelajaran::query()
            ->whereBetween('tanggal', [$this->tanggalMulai, $this->tanggalSelesai])
            ->orderBy('tanggal')
            ->orderBy('kelas')
            ->orderBy('jamke');

        // Batasi ke guru sendiri jika bukan admin/waka
        if ($this->kdGuru) {
            $query->where('kdguru', $this->kdGuru);
        }

        // Filter guru spesifik (mode lihat-semua)
        if (! $this->kdGuru && $this->gtkId) {
            $gtk = GTK::find($this->gtkId);
            if ($gtk?->kd_guru) {
                $query->where('kdguru', $gtk->kd_guru);
            }
        }

        if ($this->kelas) {
            $query->where('kelas', $this->kelas);
        }

        if ($this->pelajaran) {
            $query->where('pelajaran', $this->pelajaran);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Hari',
            'Kelas',
            'Mata Pelajaran',
            'Jam Ke',
            'Jam Mulai',
            'Jam Selesai',
            'Guru',
            'Kode Guru',
            'Hadir',
            'Tidak Hadir',
            'Nama Siswa TH',
            'Deskripsi / Materi',
        ];
    }

    private int $row = 0;

    public function map($jurnal): array
    {
        $this->row++;

        $jamMulai   = $jurnal->jam_mulai  ? Carbon::parse($jurnal->jam_mulai)->format('H:i')  : '-';
        $jamSelesai = $jurnal->jam_selesai ? Carbon::parse($jurnal->jam_selesai)->format('H:i') : '-';

        $hari = [
            0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa',
            3 => 'Rabu',   4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu',
        ];

        return [
            $this->row,
            Carbon::parse($jurnal->tanggal)->format('d/m/Y'),
            $hari[Carbon::parse($jurnal->tanggal)->dayOfWeek] ?? '-',
            $jurnal->kelas     ?? '-',
            $jurnal->pelajaran ?? '-',
            $jurnal->jamke     ?? '-',
            $jamMulai,
            $jamSelesai,
            $jurnal->guru      ?? '-',
            $jurnal->kdguru    ?? '-',
            (int) $jurnal->siswahadir,
            (int) $jurnal->siswatdkhadir,
            $jurnal->namasiswa ?? '-',
            strip_tags($jurnal->deskripsi ?? '-'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow   = $sheet->getHighestRow();
        $lastCol   = 'N';

        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font'    => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1D4ED8']],
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
        return 'Jurnal Mengajar';
    }
}
