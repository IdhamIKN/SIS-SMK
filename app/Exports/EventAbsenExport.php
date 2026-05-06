<?php

namespace App\Exports;

use App\Models\AbsenEvent;
use App\Models\Event;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class EventAbsenExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return AbsenEvent::where('event_id', $this->event->id)
            ->with('siswa')
            ->orderBy('jenis')
            ->orderBy('waktu_scan')
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
            'Jenis Absen',
            'Waktu Scan',
            'Barcode Digunakan',
        ];
    }

    public function map($row): array
    {
        return [
            $row->siswa->nis ?? '-',
            $row->siswa->nama_lengkap ?? '-',
            $row->siswa->kelas->nama_kelas ?? '-',
            $row->jenis === 'masuk' ? 'Masuk' : 'Pulang',
            $row->waktu_scan->format('d M Y H:i:s'),
            $row->barcode_digunakan,
        ];
    }
}
