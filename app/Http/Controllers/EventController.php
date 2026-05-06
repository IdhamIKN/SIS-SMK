<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventStoreRequest;
use App\Http\Requests\EventUpdateRequest;
use App\Models\Event;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Services\WhatsappService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EventController extends Controller
{
    public function __construct(
        protected WhatsappService $whatsappService
    ) {
    }

    /**
     * Display a listing of the events.
     */
    public function index(): View
    {
        $query = Event::withCount('absenEvent')
            ->orderBy('tanggal_mulai', 'desc');

        $user = auth()->user();
        if ($user->hasRole('siswa') && $user->siswa) {
            $siswa = $user->siswa;
            $query->where(function ($q) use ($siswa) {
                $q->where('berlaku_untuk_semua', true)
                    ->orWhere(function ($sq) use ($siswa) {
                        $sq->where('mode_peserta', 'kelas')
                            ->whereHas('kelas', fn($kq) => $kq->where('kelas.id', $siswa->kelas_id));
                    })
                    ->orWhere(function ($sq) use ($siswa) {
                        $sq->where('mode_peserta', 'siswa')
                            ->whereHas('siswa', fn($sq) => $sq->where('siswas.id', $siswa->id));
                    });
            });
        }

        $events = $query->paginate(20);

        Log::channel('sis')->info('[Event] Index - daftar event', [
            'user_id' => auth()->id(),
            'role' => $user->getRoleNames()->first(),
            'count' => $events->total(),
        ]);

        return view('event.index', compact('events'));
    }

    /**
     * Show the form for creating a new event.
     */
    public function create(): View
    {
        $kelas = Kelas::with('jurusan')
            ->orderBy('nama_kelas')
            ->get();

        $siswa = Siswa::with('kelas')
            ->where('status_aktif', true)
            ->orderBy('nama_lengkap')
            ->get();

        return view('event.create', compact('kelas', 'siswa'));
    }

    public function searchKelas(Request $request): JsonResponse
    {
        $q = trim($request->get('q', ''));

        $kelas = Kelas::with('jurusan')
            ->when($q, fn($query) => $query->where('nama_kelas', 'like', "%{$q}%"))
            ->orderBy('nama_kelas')
            ->limit(30)
            ->get();

        return response()->json($kelas->map(fn($k) => [
            'id' => $k->id,
            'text' => $k->nama_kelas,
            'meta' => $k->jurusan->nama_jurusan ?? '',
        ]));
    }

    public function searchSiswa(Request $request): JsonResponse
    {
        $q = trim($request->get('q', ''));

        $siswa = Siswa::with('kelas')
            ->when(
                $q,
                fn($query) => $query->where('nama_lengkap', 'like', "%{$q}%")
                    ->orWhere('nis', 'like', "%{$q}%")
            )
            ->orderBy('nama_lengkap')
            ->limit(30)
            ->get();

        return response()->json($siswa->map(fn($s) => [
            'id' => $s->id,
            'text' => $s->nama_lengkap,
            'meta' => $s->kelas->nama_kelas ?? '',
        ]));
    }

    /**
     * Store a newly created event in storage.
     */
    // public function store(Request $request): RedirectResponse
    // {
    //     $validated = $request->validate([
    //         'nama_event'          => 'required|string|max:255',
    //         'deskripsi'           => 'nullable|string',
    //         'tanggal_mulai'       => 'required|date',
    //         'tanggal_selesai'     => 'required|date|after_or_equal:tanggal_mulai',
    //         'lokasi'              => 'nullable|string|max:255',
    //         'lat'                 => 'nullable|numeric|between:-90,90',
    //         'lng'                 => 'nullable|numeric|between:-180,180',
    //         'radius_meter'        => 'nullable|integer|min:10|max:5000',
    //         'ada_absen_masuk'     => 'boolean',
    //         'ada_absen_pulang'    => 'boolean',
    //         'berlaku_untuk_semua' => 'boolean',
    //         'mode_peserta'        => 'required|in:kelas,siswa',
    //         'barcode_rotate_detik'=> 'nullable|integer|min:0|max:3600',
    //         'kelas_id'            => 'required_if:mode_peserta,kelas|array',
    //         'kelas_id.*'          => 'exists:kelas,id',
    //         'siswa_id'            => 'required_if:mode_peserta,siswa|array',
    //         'siswa_id.*'          => 'exists:siswas,id',
    //     ]);

    //     $barcodeRotate = $validated['barcode_rotate_detik'] ?? 0;
    //     $barcodeValue = hash('sha256', microtime() . random_bytes(16));

    //     DB::beginTransaction();
    //     try {
    //         $event = Event::create([
    //             'created_by'            => auth()->id(),
    //             'nama_event'            => $validated['nama_event'],
    //             'deskripsi'             => $validated['deskripsi'],
    //             'tanggal_mulai'         => $validated['tanggal_mulai'],
    //             'tanggal_selesai'       => $validated['tanggal_selesai'],
    //             'lokasi'                => $validated['lokasi'],
    //             'lat'                   => $validated['lat'],
    //             'lng'                   => $validated['lng'],
    //             'radius_meter'          => $validated['radius_meter'] ?? 100,
    //             'ada_absen_masuk'       => $validated['ada_absen_masuk'] ?? true,
    //             'ada_absen_pulang'      => $validated['ada_absen_pulang'] ?? false,
    //             'berlaku_untuk_semua'   => $validated['berlaku_untuk_semua'] ?? true,
    //             'mode_peserta'          => $validated['mode_peserta'],
    //             'barcode_rotate_detik'  => $barcodeRotate,
    //             'barcode_value'         => $barcodeValue,
    //             'barcode_updated_at'    => now(),
    //         ]);

    //         if (!$validated['berlaku_untuk_semua']) {
    //             if ($validated['mode_peserta'] === 'kelas' && !empty($validated['kelas_id'])) {
    //                 $event->kelas()->attach($validated['kelas_id']);
    //             } elseif ($validated['mode_peserta'] === 'siswa' && !empty($validated['siswa_id'])) {
    //                 $event->siswa()->attach($validated['siswa_id']);
    //             }
    //         }

    //         DB::commit();

    //         Log::channel('sis')->info('[Event] Dibuat', [
    //             'event_id'    => $event->id,
    //             'nama'        => $event->nama_event,
    //             'created_by'  => auth()->id(),
    //             'mode_peserta'=> $event->mode_peserta,
    //             'has_location'=> $event->hasLocation(),
    //         ]);

    //         return redirect()->route('event.index')
    //             ->with('success', 'Event berhasil dibuat.');

    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //         Log::channel('sis')->error('[Event] Gagal dibuat', [
    //             'error' => $e->getMessage(),
    //         ]);
    //         return back()->with('error', 'Gagal membuat event: ' . $e->getMessage())->withInput();
    //     }
    // }
    public function store(EventStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // ✅ Checkbox: pakai boolean() agar aman jika key tidak terkirim
        $berlakuUntukSemua = $request->boolean('berlaku_untuk_semua');
        $adaAbsenMasuk = $request->boolean('ada_absen_masuk');
        $adaAbsenPulang = $request->boolean('ada_absen_pulang');

        DB::beginTransaction();
        try {
            $event = Event::create(array_merge($validated, [
                'berlaku_untuk_semua' => $berlakuUntukSemua,
                'ada_absen_masuk' => $adaAbsenMasuk,
                'ada_absen_pulang' => $adaAbsenPulang,
            ]));

            if (!$berlakuUntukSemua) {
                if ($validated['mode_peserta'] === 'kelas') {
                    $event->kelas()->sync($validated['kelas_id'] ?? []);
                } else {
                    $event->siswa()->sync($validated['siswa_id'] ?? []);
                }
            } else {
                $event->kelas()->detach();
                $event->siswa()->detach();
            }

            DB::commit();

            return redirect()->route('event.show', $event)->with('success', 'Event berhasil dibuat.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Event store error: ' . $e->getMessage());

            return back()->withInput()->with('error', 'Gagal membuat event: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified event.
     */
    public function show(Event $event): View
    {
        $event->load(['kelas', 'siswa.kelas', 'absenEvent.siswa']);

        $rekap = $event->absenEvent()
            ->selectRaw('jenis, COUNT(*) as total')
            ->groupBy('jenis')
            ->pluck('total', 'jenis');

        Log::channel('sis')->info('[Event] Detail ditampilkan', [
            'event_id' => $event->id,
            'user_id' => auth()->id(),
        ]);

        return view('event.show', compact('event', 'rekap'));
    }

    /**
     * Show the form for editing the specified event.
     */
    public function edit(Event $event): View
    {
        $event->load(['kelas', 'siswa']);
        $kelas = Kelas::with('jurusan')
            ->orderBy('nama_kelas')
            ->get();

        $siswa = Siswa::with('kelas')
            ->where('status_aktif', true)
            ->orderBy('nama_lengkap')
            ->get();

        return view('event.edit', compact('event', 'kelas', 'siswa'));
    }

    /**
     * Update the specified event in storage.
     */
    // public function update(Request $request, Event $event): RedirectResponse
    // {
    //     $validated = $request->validate([
    //         'nama_event'          => 'required|string|max:255',
    //         'deskripsi'           => 'nullable|string',
    //         'tanggal_mulai'       => 'required|date',
    //         'tanggal_selesai'     => 'required|date|after_or_equal:tanggal_mulai',
    //         'lokasi'              => 'nullable|string|max:255',
    //         'lat'                 => 'nullable|numeric|between:-90,90',
    //         'lng'                 => 'nullable|numeric|between:-180,180',
    //         'radius_meter'        => 'nullable|integer|min:10|max:5000',
    //         'ada_absen_masuk'     => 'boolean',
    //         'ada_absen_pulang'    => 'boolean',
    //         'berlaku_untuk_semua' => 'boolean',
    //         'mode_peserta'        => 'required|in:kelas,siswa',
    //         'barcode_rotate_detik'=> 'nullable|integer|min:0|max:3600',
    //         'kelas_id'            => 'required_if:mode_peserta,kelas|array',
    //         'kelas_id.*'          => 'exists:kelas,id',
    //         'siswa_id'            => 'required_if:mode_peserta,siswa|array',
    //         'siswa_id.*'          => 'exists:siswas,id',
    //     ]);

    //     DB::beginTransaction();
    //     try {
    //         $event->update([
    //             'nama_event'            => $validated['nama_event'],
    //             'deskripsi'             => $validated['deskripsi'],
    //             'tanggal_mulai'         => $validated['tanggal_mulai'],
    //             'tanggal_selesai'       => $validated['tanggal_selesai'],
    //             'lokasi'                => $validated['lokasi'],
    //             'lat'                   => $validated['lat'],
    //             'lng'                   => $validated['lng'],
    //             'radius_meter'          => $validated['radius_meter'] ?? 100,
    //             'ada_absen_masuk'       => $validated['ada_absen_masuk'] ?? true,
    //             'ada_absen_pulang'      => $validated['ada_absen_pulang'] ?? false,
    //             'berlaku_untuk_semua'   => $validated['berlaku_untuk_semua'] ?? true,
    //             'mode_peserta'          => $validated['mode_peserta'],
    //             'barcode_rotate_detik'  => $validated['barcode_rotate_detik'] ?? 0,
    //         ]);

    //         // Sync relasi berdasarkan mode
    //         $event->kelas()->detach();
    //         $event->siswa()->detach();

    //         if (!$validated['berlaku_untuk_semua']) {
    //             if ($validated['mode_peserta'] === 'kelas' && !empty($validated['kelas_id'])) {
    //                 $event->kelas()->sync($validated['kelas_id']);
    //             } elseif ($validated['mode_peserta'] === 'siswa' && !empty($validated['siswa_id'])) {
    //                 $event->siswa()->sync($validated['siswa_id']);
    //             }
    //         }

    //         DB::commit();

    //         Log::channel('sis')->info('[Event] Diperbarui', [
    //             'event_id'     => $event->id,
    //             'user_id'      => auth()->id(),
    //             'mode_peserta' => $event->mode_peserta,
    //             'has_location' => $event->hasLocation(),
    //         ]);

    //         return redirect()->route('event.index')
    //             ->with('success', 'Event berhasil diperbarui.');

    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //         Log::channel('sis')->error('[Event] Gagal diperbarui', [
    //             'event_id' => $event->id,
    //             'error'    => $e->getMessage(),
    //         ]);
    //         return back()->with('error', 'Gagal memperbarui event: ' . $e->getMessage())->withInput();
    //     }
    // }
    public function update(EventUpdateRequest $request, Event $event): RedirectResponse
    {
        $validated = $request->validated();

        // ✅ Fix utama — boolean() aman untuk checkbox
        $berlakuUntukSemua = $request->boolean('berlaku_untuk_semua');
        $adaAbsenMasuk = $request->boolean('ada_absen_masuk');
        $adaAbsenPulang = $request->boolean('ada_absen_pulang');

        DB::beginTransaction();
        try {
            $event->update(array_merge($validated, [
                'berlaku_untuk_semua' => $berlakuUntukSemua,
                'ada_absen_masuk' => $adaAbsenMasuk,
                'ada_absen_pulang' => $adaAbsenPulang,
            ]));

            if (!$berlakuUntukSemua) {
                if ($validated['mode_peserta'] === 'kelas') {
                    $event->kelas()->sync($validated['kelas_id'] ?? []);
                    $event->siswa()->detach();
                } else {
                    $event->siswa()->sync($validated['siswa_id'] ?? []);
                    $event->kelas()->detach();
                }
            } else {
                $event->kelas()->detach();
                $event->siswa()->detach();
            }

            DB::commit();

            return redirect()->route('event.show', $event)->with('success', 'Event berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Event update error: ' . $e->getMessage());

            return back()->withInput()->with('error', 'Gagal memperbarui event: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified event from storage.
     */
    public function destroy(Event $event): RedirectResponse
    {
        $event->delete();

        Log::channel('sis')->info('[Event] Dihapus', [
            'event_id' => $event->id,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('event.index')
            ->with('success', 'Event berhasil dihapus.');
    }

    /**
     * Generate ulang barcode untuk event.
     */
    public function rotateBarcode(Event $event): RedirectResponse
    {
        $barcode = $event->rotateBarcode();

        Log::channel('sis')->info('[Event] Barcode di-rotate', [
            'event_id' => $event->id,
            'rotate_detik' => $event->barcode_rotate_detik,
        ]);

        return back()->with('success', 'Barcode di-rotate: ' . substr($barcode, 0, 16) . '...');
    }

    /**
     * API: Ambil barcode terbaru untuk polling QR code.
     */
    public function getBarcode(Event $event)
    {
        return response()->json([
            'barcode_value' => $event->barcode_value,
            'barcode_updated_at' => $event->barcode_updated_at?->toIso8601String(),
            'rotate_detik' => $event->barcode_rotate_detik,
            'is_valid' => $event->isBarcodeValid(),
        ]);
    }

    /**
     * API: Update barcode dari client-side auto-rotate.
     */
    public function updateBarcode(Event $event): JsonResponse
    {
        // Hitung selisih detik sejak update terakhir
        $updatedAt = $event->barcode_updated_at ?? now()->subYears(1);
        $secondsPassed = now()->diffInSeconds($updatedAt, true); // absolute
        $rotateDetik = (int) $event->barcode_rotate_detik;
        $tolerance = 2; // detik toleransi untuk network latency

        // Belum waktunya rotate → kembalikan barcode saat ini
        if ($rotateDetik > 0 && $secondsPassed < ($rotateDetik - $tolerance)) {
            return response()->json([
                'barcode_value' => $event->barcode_value,
                'updated_at_ms' => $updatedAt->valueOf(),    // timestamp ms untuk sync countdown
                'rotated' => false,
            ]);
        }

        // Saatnya rotate → generate barcode baru
        $newBarcode = Str::uuid()->toString();
        $now = now();

        $event->update([
            'barcode_value' => $newBarcode,
            'barcode_updated_at' => $now,
        ]);

        Log::channel('absen')->info('Barcode rotated', [
            'event_id' => $event->id,
            'barcode_value' => $newBarcode,
        ]);

        return response()->json([
            'barcode_value' => $newBarcode,
            'updated_at_ms' => $now->valueOf(),
            'rotated' => true,
        ]);
    }


    public function barcodeStream(Event $event): StreamedResponse
    {
        // Tutup session Laravel agar tidak memblokir request lain dari user yang sama
        // (Laravel session menggunakan file lock — SSE yang panjang akan memblokir tab lain)
        session()->save();

        return response()->stream(function () use ($event) {

            $maxDuration = 300;        // 5 menit — client reconnect otomatis
            $pollInterval = 2;          // cek DB setiap N detik
            $heartbeatEvery = 15;         // heartbeat setiap N detik
            $startTime = time();
            $lastBarcode = null;
            $lastHeartbeat = 0;
            $tickCount = 0;

            // ── Kirim state awal saat pertama connect ───────────────
            $event->refresh();
            $lastBarcode = $event->barcode_value;

            $this->sseEvent('barcode', [
                'barcode_value' => $event->barcode_value,
                'updated_at_ms' => optional($event->barcode_updated_at)->valueOf() ?? (time() * 1000),
            ]);

            // ── Loop utama ──────────────────────────────────────────
            while (true) {

                // Cek apakah client masih terhubung
                if (connection_aborted()) {
                    break;
                }

                // Cek batas waktu maksimum
                $elapsed = time() - $startTime;
                if ($elapsed >= $maxDuration) {
                    // Kirim event "reconnect" agar client tau harus reconnect
                    $this->sseEvent('reconnect', ['message' => 'Stream timeout, reconnect.']);
                    break;
                }

                sleep($pollInterval);
                $tickCount++;

                // ── Heartbeat ─────────────────────────────────────
                if (($elapsed - $lastHeartbeat) >= $heartbeatEvery) {
                    echo ": heartbeat\n\n";
                    $this->sseFlush();
                    $lastHeartbeat = $elapsed;
                }

                // ── Cek perubahan barcode di DB ───────────────────
                $event->refresh();

                if ($event->barcode_value !== $lastBarcode) {
                    $lastBarcode = $event->barcode_value;

                    $this->sseEvent('barcode', [
                        'barcode_value' => $event->barcode_value,
                        'updated_at_ms' => optional($event->barcode_updated_at)->valueOf() ?? (time() * 1000),
                    ]);
                }
            }

        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-store',
            'X-Accel-Buffering' => 'no',      // matikan buffer Nginx
            'Connection' => 'keep-alive',
        ]);
    }

    /**
     * Helper: tulis satu SSE event ke stream.
     */
    private function sseEvent(string $name, array $data): void
    {
        echo "event: {$name}\n";
        echo 'data: ' . json_encode($data) . "\n\n";
        $this->sseFlush();
    }

    /**
     * Helper: flush output buffer ke client.
     */
    private function sseFlush(): void
    {
        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
    }
}
