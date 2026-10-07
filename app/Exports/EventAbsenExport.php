<?php

namespace App\Exports;

use App\Models\AbsenEvent;
use App\Models\Event;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Export absen event — pola unified (1 record per siswa per event).
 * Satu baris = satu peserta, menampilkan waktu masuk & pulang sekaligus.
 */
class EventAbsenExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithTitle
{
    public function __construct(protected Event $event) {}

    public function collection(): Collection
    {
        return AbsenEvent::where('event_id', $this->event->id)
            ->with(['siswa.kelas'])
            ->orderBy('created_at')
            ->get();
    }

    public function title(): string
    {
        return substr($this->event->nama_event, 0, 30);
    }

    public function headings(): array
    {
        return [
            'NIS',
            'Nama Siswa',
            'Kelas',
            'Jam Masuk',
            'Jam Pulang',
            'Status',
        ];
    }

    public function map($row): array
    {
        // Waktu masuk: kolom baru → fallback legacy waktu_scan
        $waktuMasuk = $row->waktu_masuk ?? $row->waktu_scan;
        $waktuPulang = $row->waktu_pulang;

        $jamMasuk  = $waktuMasuk  ? $waktuMasuk->format('d/m/Y H:i')  : '-';
        $jamPulang = $waktuPulang ? $waktuPulang->format('d/m/Y H:i') : '-';

        $status = $waktuMasuk ? 'Hadir' : 'Alpa';

        return [
            $row->siswa->nis          ?? '-',
            $row->siswa->nama_lengkap ?? '-',
            $row->siswa->kelas->nama_kelas ?? '-',
            $jamMasuk,
            $jamPulang,
            $status,
        ];
    }
}
