<?php

namespace App\Http\Controllers;

use App\Models\EventCategory;
use App\Models\EventGuru;
use App\Models\GTK;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EventGuruController extends Controller
{
    public function index(Request $request): View
    {
        $tab    = $request->input('tab', 'semua');
        $search = $request->input('search');
        $tanggal = $request->input('tanggal');
        $now    = now();

        $query = EventGuru::withCount('absenEventGuru');

        // Filter pencarian
        if ($search) {
            $query->where('nama_event', 'like', "%{$search}%");
        }

        if ($tanggal) {
            $query->whereDate('tanggal_mulai', '<=', $tanggal)
                ->whereDate('tanggal_selesai', '>=', $tanggal);
        }

        // Filter tab
        match ($tab) {
            'aktif'    => $query->where('tanggal_mulai', '<=', $now)->where('tanggal_selesai', '>=', $now),
            'upcoming' => $query->where('tanggal_mulai', '>', $now),
            'riwayat'  => $query->where('tanggal_selesai', '<', $now),
            default    => $query->orderByRaw("
                CASE
                    WHEN tanggal_mulai <= ? AND tanggal_selesai >= ? THEN 0
                    WHEN tanggal_mulai > ? THEN 1
                    ELSE 2
                END
            ", [$now, $now, $now]),
        };

        // Sorting per tab
        if ($tab === 'semua') {
            $query->orderBy('tanggal_mulai', 'asc');
        } elseif ($tab === 'riwayat') {
            $query->orderBy('tanggal_selesai', 'desc');
        } else {
            $query->orderBy('tanggal_mulai', 'asc');
        }

        $events      = $query->paginate(20)->withQueryString();
        $totalActive = EventGuru::where('tanggal_mulai', '<=', $now)
            ->where('tanggal_selesai', '>=', $now)
            ->count();

        return view('event-guru.index', compact('events', 'tab', 'totalActive'));
    }

    public function create(): View
    {
        $categories = EventCategory::orderBy('nama_kategori')->get();
        return view('event-guru.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_event'           => 'required|string|max:255',
            'deskripsi'            => 'nullable|string',
            'lokasi'               => 'nullable|string|max:255',
            'lat'                  => 'nullable|numeric|between:-90,90',
            'lng'                  => 'nullable|numeric|between:-180,180',
            'radius_meter'         => 'nullable|integer|min:10|max:5000',
            'tanggal_mulai'        => 'required|date',
            'tanggal_selesai'      => 'required|date|after_or_equal:tanggal_mulai',
            'ada_absen_masuk'      => 'boolean',
            'ada_absen_pulang'     => 'boolean',
            'barcode_rotate_detik' => 'nullable|integer|min:0|max:3600',
            'event_category_name'  => 'nullable|string|max:100',
        ]);

        $event = EventGuru::create([
            'created_by'           => auth()->id(),
            'nama_event'           => $validated['nama_event'],
            'deskripsi'            => $validated['deskripsi'] ?? null,
            'lokasi'               => $validated['lokasi'] ?? null,
            'lat'                  => $validated['lat'] ?? null,
            'lng'                  => $validated['lng'] ?? null,
            'radius_meter'         => $validated['radius_meter'] ?? 100,
            'tanggal_mulai'        => $validated['tanggal_mulai'],
            'tanggal_selesai'      => $validated['tanggal_selesai'],
            'ada_absen_masuk'      => $request->boolean('ada_absen_masuk'),
            'ada_absen_pulang'     => $request->boolean('ada_absen_pulang'),
            'barcode_rotate_detik' => $validated['barcode_rotate_detik'] ?? 0,
            'barcode_value'        => hash('sha256', microtime() . random_bytes(16)),
            'barcode_updated_at'   => now(),
        ]);

        Log::channel('sis')->info('[EventGuru] Dibuat', [
            'event_id'   => $event->id,
            'nama'       => $event->nama_event,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('event-guru.show', $event)
            ->with('success', 'Event guru berhasil dibuat.');
    }

    public function show(EventGuru $eventGuru): View
    {
        $eventGuru->load('absenEventGuru.gtk');

        $masukCount  = $eventGuru->absenEventGuru->where('jenis', 'masuk')->count();
        $pulangCount = $eventGuru->absenEventGuru->where('jenis', 'pulang')->count();
        $totalScan   = $eventGuru->absenEventGuru->count();
        $totalGuru   = GTK::where('status_aktif', true)->count();

        $absenTerbaru = $eventGuru->absenEventGuru()
            ->with('gtk')
            ->latest('waktu_scan')
            ->take(10)
            ->get();

        return view('event-guru.show', compact(
            'eventGuru',
            'masukCount',
            'pulangCount',
            'totalScan',
            'totalGuru',
            'absenTerbaru'
        ));
    }

    public function edit(EventGuru $eventGuru): View
    {
        $categories = EventCategory::orderBy('nama_kategori')->get();
        return view('event-guru.edit', compact('eventGuru', 'categories'));
    }

    public function update(Request $request, EventGuru $eventGuru): RedirectResponse
    {
        $validated = $request->validate([
            'nama_event'           => 'required|string|max:255',
            'deskripsi'            => 'nullable|string',
            'lokasi'               => 'nullable|string|max:255',
            'lat'                  => 'nullable|numeric|between:-90,90',
            'lng'                  => 'nullable|numeric|between:-180,180',
            'radius_meter'         => 'nullable|integer|min:10|max:5000',
            'tanggal_mulai'        => 'required|date',
            'tanggal_selesai'      => 'required|date|after_or_equal:tanggal_mulai',
            'ada_absen_masuk'      => 'boolean',
            'ada_absen_pulang'     => 'boolean',
            'barcode_rotate_detik' => 'nullable|integer|min:0|max:3600',
            'event_category_name'  => 'nullable|string|max:100',
        ]);

        $eventGuru->update([
            'nama_event'           => $validated['nama_event'],
            'deskripsi'            => $validated['deskripsi'] ?? null,
            'lokasi'               => $validated['lokasi'] ?? null,
            'lat'                  => $validated['lat'] ?? null,
            'lng'                  => $validated['lng'] ?? null,
            'radius_meter'         => $validated['radius_meter'] ?? 100,
            'tanggal_mulai'        => $validated['tanggal_mulai'],
            'tanggal_selesai'      => $validated['tanggal_selesai'],
            'ada_absen_masuk'      => $request->boolean('ada_absen_masuk'),
            'ada_absen_pulang'     => $request->boolean('ada_absen_pulang'),
            'barcode_rotate_detik' => $validated['barcode_rotate_detik'] ?? 0,
        ]);

        return redirect()->route('event-guru.show', $eventGuru)
            ->with('success', 'Event guru berhasil diperbarui.');
    }

    public function destroy(EventGuru $eventGuru): RedirectResponse
    {
        $eventGuru->delete();
        return redirect()->route('event-guru.index')
            ->with('success', 'Event guru berhasil dihapus.');
    }

    public function rotateBarcode(EventGuru $eventGuru): RedirectResponse
    {
        $barcode = $eventGuru->rotateBarcode();
        return back()->with('success', 'Barcode di-rotate: ' . substr($barcode, 0, 16) . '...');
    }

    public function getBarcode(EventGuru $eventGuru): JsonResponse
    {
        return response()->json([
            'barcode_value'      => $eventGuru->barcode_value,
            'barcode_updated_at' => $eventGuru->barcode_updated_at?->toIso8601String(),
            'rotate_detik'       => $eventGuru->barcode_rotate_detik,
            'is_valid'           => $eventGuru->isBarcodeValid(),
        ]);
    }

    public function updateBarcode(EventGuru $eventGuru): JsonResponse
    {
        $updatedAt     = $eventGuru->barcode_updated_at ?? now()->subYears(1);
        $secondsPassed = abs(now()->diffInSeconds($updatedAt, false));
        $rotateDetik   = (int) $eventGuru->barcode_rotate_detik;

        if ($rotateDetik > 0 && $secondsPassed < ($rotateDetik - 1)) {
            return response()->json([
                'barcode_value' => $eventGuru->barcode_value,
                'updated_at_ms' => $updatedAt->valueOf(),
                'rotated'       => false,
            ]);
        }

        $newBarcode = hash('sha256', $eventGuru->id . microtime() . random_bytes(16));
        $now        = now();
        $eventGuru->update([
            'barcode_value'      => $newBarcode,
            'barcode_updated_at' => $now,
        ]);

        Log::channel('sis')->info('[EventGuru] Barcode rotated', [
            'event_guru_id' => $eventGuru->id,
            'barcode_value' => $newBarcode,
        ]);

        return response()->json([
            'barcode_value' => $newBarcode,
            'updated_at_ms' => $now->valueOf(),
            'rotated'       => true,
        ]);
    }

    public function barcodeStream(EventGuru $eventGuru): StreamedResponse
    {
        session()->save();
        session_write_close();

        return response()->stream(function () use ($eventGuru) {
            $maxDuration    = 300;
            $pollInterval   = 2;
            $heartbeatEvery = 15;
            $startTime      = time();
            $lastBarcode    = null;
            $lastHeartbeat  = 0;

            $eventGuru->refresh();
            $lastBarcode = $eventGuru->barcode_value;
            $this->sseEvent('barcode', [
                'barcode_value' => $eventGuru->barcode_value,
                'updated_at_ms' => optional($eventGuru->barcode_updated_at)->valueOf() ?? (time() * 1000),
            ]);

            while (true) {
                if (connection_aborted()) break;
                $elapsed = time() - $startTime;
                if ($elapsed >= $maxDuration) {
                    $this->sseEvent('reconnect', ['message' => 'Stream timeout, reconnect.']);
                    break;
                }
                sleep($pollInterval);
                if (($elapsed - $lastHeartbeat) >= $heartbeatEvery) {
                    echo ": heartbeat\n\n";
                    $this->sseFlush();
                    $lastHeartbeat = $elapsed;
                }
                $eventGuru->refresh();

                $rotateDetik = (int) $eventGuru->barcode_rotate_detik;
                if ($rotateDetik > 0) {
                    $updatedAt     = $eventGuru->barcode_updated_at ?? now()->subYears(1);
                    $secondsPassed = now()->diffInSeconds($updatedAt, true);
                    if ($secondsPassed >= $rotateDetik) {
                        $newBarcode = hash('sha256', $eventGuru->id . microtime() . random_bytes(16));
                        $now        = now();
                        $eventGuru->update([
                            'barcode_value'      => $newBarcode,
                            'barcode_updated_at' => $now,
                        ]);
                        $eventGuru->refresh();
                    }
                }

                if ($eventGuru->barcode_value !== $lastBarcode) {
                    $lastBarcode = $eventGuru->barcode_value;
                    $this->sseEvent('barcode', [
                        'barcode_value' => $eventGuru->barcode_value,
                        'updated_at_ms' => optional($eventGuru->barcode_updated_at)->valueOf() ?? (time() * 1000),
                    ]);
                }
            }
        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache, no-store',
            'X-Accel-Buffering' => 'no',
            'Connection'        => 'keep-alive',
        ]);
    }

    private function sseEvent(string $name, array $data): void
    {
        echo "event: {$name}\n";
        echo 'data: ' . json_encode($data) . "\n\n";
        $this->sseFlush();
    }

    private function sseFlush(): void
    {
        if (ob_get_level() > 0) ob_flush();
        flush();
    }
}
