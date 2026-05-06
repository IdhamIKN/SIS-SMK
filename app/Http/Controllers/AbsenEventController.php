<?php

namespace App\Http\Controllers;

use App\Exports\EventAbsenExport;
use App\Http\Requests\AbsenEventRequest;
use App\Jobs\SendEventNotifJob;
use App\Models\AbsenEvent;
use App\Models\Event;
use App\Services\WhatsappService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class AbsenEventController extends Controller
{
    public function __construct(
        protected WhatsappService $whatsappService
    ) {}

    /**
     * Halaman scan barcode event.
     */
    public function scan(string $eventId): View
    {
        $event = Event::findOrFail($eventId);
        $jenis = request('jenis', 'masuk');

        if (! $event->isActive()) {
            return view('event.error', ['message' => 'Event tidak aktif atau sudah berakhir.']);
        }
        if (! $event->canAbsen($jenis)) {
            return view('event.error', ['message' => 'Tipe absen tidak diizinkan untuk event ini.']);
        }

        return view('event.scan', compact('event', 'jenis'));
    }

    /**
     * Proses scan barcode event.
     */
    public function processScan(AbsenEventRequest $request, string $eventId)
    {
        $event = Event::findOrFail($eventId);

        // Rate limit
        $key = 'event-scan:'.$eventId.':'.auth()->id();
        if (! RateLimiter::attempt($key, 3, function () {
            return true;
        }, 60)) {
            return response()->json([
                'success' => false,
                'message' => 'Terlalu banyak scan. Coba lagi nanti.'
            ], 429);
        }

        $validated = $request->validated();

        $siswa = auth()->user()->siswa;

        DB::beginTransaction();
        try {
            if (! $event->isActive()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Event tidak aktif.'
                ], 400);
            }

            if (! $event->appliesToSiswa($siswa->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Event ini tidak berlaku untuk Anda.'
                ], 403);
            }

            if (! $event->canAbsen($validated['jenis'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Absen jenis ini tidak diizinkan.'
                ], 400);
            }

            // Validasi geolokasi
            if ($event->hasLocation()) {
                $lat = $validated['lat'] ?? null;
                $lng = $validated['lng'] ?? null;

                if (! $lat || ! $lng) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Aktifkan GPS/Lokasi untuk absen event ini.'
                    ], 400);
                }

                if (! $event->isWithinRadius($lat, $lng)) {
                    $distance = round($event->distanceTo($lat, $lng), 0);

                    return response()->json([
                        'success' => false,
                        'message' => 'Anda berada di luar area absen ('.$distance.'m dari lokasi event, radius: '.$event->radius_meter.'m).'
                    ], 400);
                }
            }

            // Validasi barcode
            if ($event->barcode_rotate_detik > 0 && ! $event->isBarcodeValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Barcode sudah kadaluarsa. Refresh halaman.'
                ], 400);
            }

            // Validasi duplikasi
            $exists = AbsenEvent::where('event_id', $event->id)
                ->where('siswa_id', $siswa->id)
                ->where('jenis', $validated['jenis'])
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah absen '.$validated['jenis'].' untuk event ini.'
                ], 409);
            }

            $absenEvent = AbsenEvent::create([
                'event_id' => $event->id,
                'siswa_id' => $siswa->id,
                'jenis' => $validated['jenis'],
                'waktu_scan' => now(),
                'barcode_digunakan' => $event->barcode_value,
                'wa_terkirim_ortu' => false,
            ]);

            DB::commit();

            SendEventNotifJob::dispatch($absenEvent)->delay(now()->addSeconds(2));

            return response()->json([
                'success' => true,
                'message' => 'Absen event berhasil! Notifikasi WA akan dikirim ke ortu.',
                'redirect' => route('event.rekap', $event)
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('AbsenEvent error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan. Coba lagi.'
            ], 500);
        }
    }

    public function rekap(Event $event): View
    {
        $event->load(['kelas', 'absenEvent.siswa']);

        return view('event.rekap', compact('event'));
    }

    public function export(Event $event)
    {
        return Excel::download(
            new EventAbsenExport($event),
            'rekap-absen-event-'.$event->nama_event.'-'.now()->format('Y-m-d').'.xlsx'
        );
    }
}
