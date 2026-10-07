<?php

namespace App\Http\Controllers;

use App\Exports\EventGuruAbsenExport;
use App\Models\AbsenEventGuru;
use App\Models\EventGuru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class AbsenEventGuruController extends Controller
{
    /**
     * Halaman scan barcode event guru.
     */
    public function scan(EventGuru $eventGuru): View
    {
        $jenis = request('jenis', 'masuk');

        if (! $eventGuru->isActive()) {
            return view('event-guru.error', ['message' => 'Event tidak aktif atau sudah berakhir.']);
        }
        if (! $eventGuru->canAbsen($jenis)) {
            return view('event-guru.error', ['message' => 'Tipe absen tidak diizinkan untuk event ini.']);
        }

        return view('event-guru.scan', compact('eventGuru', 'jenis'));
    }

    /**
     * Proses scan barcode event guru.
     */
    public function processScan(Request $request, EventGuru $eventGuru)
    {
        $validated = $request->validate([
            'barcode' => 'required|string',
            'jenis'   => 'required|in:masuk,pulang',
        ]);

        // Rate limit
        $key = 'event-guru-scan:' . $eventGuru->id . ':' . auth()->id();
        if (! RateLimiter::attempt($key, 3, fn() => true, 60)) {
            return response()->json(['success' => false, 'message' => 'Terlalu banyak scan. Coba lagi nanti.'], 429);
        }

        $gtk = auth()->user()->gtk ?? null;
        if (! $gtk) {
            return response()->json(['success' => false, 'message' => 'Data guru tidak ditemukan untuk akun Anda.'], 403);
        }

        try {
            if (! $eventGuru->isActive()) {
                return response()->json(['success' => false, 'message' => 'Event tidak aktif.'], 400);
            }

            if (! $eventGuru->canAbsen($validated['jenis'])) {
                return response()->json(['success' => false, 'message' => 'Absen jenis ini tidak diizinkan.'], 400);
            }

            // Validasi barcode
            if ($eventGuru->barcode_value !== $validated['barcode']) {
                return response()->json(['success' => false, 'message' => 'Barcode tidak valid atau sudah kadaluarsa.'], 400);
            }

            if ($eventGuru->barcode_rotate_detik > 0 && ! $eventGuru->isBarcodeValid()) {
                return response()->json(['success' => false, 'message' => 'Barcode sudah kadaluarsa. Refresh halaman.'], 400);
            }

            // Cek duplikasi
            $exists = AbsenEventGuru::where('event_guru_id', $eventGuru->id)
                ->where('gtk_id', $gtk->id)
                ->where('jenis', $validated['jenis'])
                ->exists();

            if ($exists) {
                return response()->json(['success' => false, 'message' => 'Anda sudah absen ' . $validated['jenis'] . ' untuk event ini.'], 409);
            }

            $absen = DB::transaction(function () use ($eventGuru, $gtk, $validated) {
                return AbsenEventGuru::create([
                    'event_guru_id'     => $eventGuru->id,
                    'gtk_id'            => $gtk->id,
                    'jenis'             => $validated['jenis'],
                    'waktu_scan'        => now(),
                    'barcode_digunakan' => $eventGuru->barcode_value,
                    'created_by'        => auth()->id(),
                ]);
            });

            Log::channel('sis')->info('[AbsenEventGuru] Berhasil absen', [
                'absen_id'       => $absen->id,
                'gtk_id'         => $gtk->id,
                'event_guru_id'  => $eventGuru->id,
                'jenis'          => $validated['jenis'],
            ]);

            return response()->json([
                'success'  => true,
                'message'  => 'Absen event berhasil dicatat!',
                'redirect' => route('event-guru.rekap', $eventGuru),
            ]);
        } catch (\Throwable $e) {
            Log::error('AbsenEventGuru processScan error: ' . $e->getMessage(), [
                'file'          => $e->getFile(),
                'line'          => $e->getLine(),
                'event_guru_id' => $eventGuru->id,
                'user_id'       => auth()->id(),
            ]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan. Coba lagi.'], 500);
        }
    }

    /**
     * Index rekap semua event guru (untuk admin).
     */
    public function rekapIndex(): View
    {
        $events = EventGuru::withCount('absenEventGuru')
            ->where('tanggal_mulai', '<=', now())
            ->orderBy('tanggal_mulai', 'desc')
            ->paginate(20);

        return view('event-guru.rekap-index', compact('events'));
    }

    /**
     * Rekap absen guru per event.
     */
    public function rekap(EventGuru $eventGuru): View
    {
        $eventGuru->load('absenEventGuru.gtk');

        $isGuru  = auth()->user()->hasRole('gtk');
        $myGtkId = $isGuru ? (auth()->user()->gtk->id ?? null) : null;

        $absenByGuru = $eventGuru->absenEventGuru->groupBy('gtk_id');

        return view('event-guru.rekap', compact('eventGuru', 'isGuru', 'myGtkId', 'absenByGuru'));
    }

    /**
     * Export Excel rekap absen guru per event.
     */
    public function export(EventGuru $eventGuru)
    {
        return Excel::download(
            new EventGuruAbsenExport($eventGuru),
            'rekap-absen-guru-' . $eventGuru->nama_event . '-' . now()->format('Y-m-d') . '.xlsx'
        );
    }
}
