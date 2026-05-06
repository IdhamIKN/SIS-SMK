<?php

namespace App\Console\Commands;

use App\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RotateEventBarcodes extends Command
{
    protected $signature = 'event:rotate-barcodes';

    protected $description = 'Otomatis rotate barcode untuk event yang aktif dan rotate_detik > 0';

    public function handle(): int
    {
        $events = Event::where('barcode_rotate_detik', '>', 0)
            ->where('tanggal_mulai', '<=', now())
            ->where('tanggal_selesai', '>=', now())
            ->get();

        $rotated = 0;
        foreach ($events as $event) {
            $elapsed = now()->diffInSeconds($event->barcode_updated_at);

            if ($elapsed >= $event->barcode_rotate_detik) {
                $oldBarcode = substr($event->barcode_value, 0, 16);
                $event->rotateBarcode();
                $rotated++;

                Log::channel('sis')->info('[Event] Barcode auto-rotated', [
                    'event_id' => $event->id,
                    'event_nama' => $event->nama_event,
                    'old_barcode' => $oldBarcode.'...',
                    'new_barcode' => substr($event->barcode_value, 0, 16).'...',
                    'rotate_detik' => $event->barcode_rotate_detik,
                    'elapsed' => $elapsed,
                ]);
            }
        }

        $this->info("Barcode berhasil di-rotate: {$rotated} event.");

        return self::SUCCESS;
    }
}
