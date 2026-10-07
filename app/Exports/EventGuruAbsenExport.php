<?php

namespace App\Exports;

use App\Models\AbsenEventGuru;
use App\Models\EventGuru;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class EventGuruAbsenExport extends DefaultValueBinder implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithTitle,
    WithColumnFormatting,
    WithCustomValueBinder
{
    public function __construct(protected EventGuru $event) {}

    public function collection(): Collection
    {
        return AbsenEventGuru::where('event_guru_id', $this->event->id)
            ->with('gtk')
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
            'Kode Guru',
            'Nama Guru',
            'NIP',
            'Jabatan',
            'Jenis Absen',
            'Waktu Scan'
        ];
    }

    public function map($row): array
    {
        return [
            $row->gtk->kd_guru ?? '-',
            $row->gtk->nama_lengkap ?? '-',
            $row->gtk->nip !== null
                ? (string) $row->gtk->nip
                : '-',
            $row->gtk->jabatan ?? '-',
            $row->jenis === 'masuk' ? 'Masuk' : 'Pulang',
            $row->waktu_scan->format('d M Y H:i:s'),
        ];
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function bindValue(Cell $cell, $value)
    {
        if ($cell->getColumn() === 'C' && $value !== null) {
            $cell->setValueExplicit(
                (string) $value,
                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
            );

            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
