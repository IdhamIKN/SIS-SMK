<?php

namespace App\Http\Controllers;

use App\Exports\EventAbsenExport;
use App\Http\Requests\AbsenEventRequest;
use App\Jobs\SendEventNotifJob;
use App\Models\AbsenEvent;
use App\Models\Event;
use App\Models\Siswa;
use App\Services\WhatsappService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
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

    /** Shortcut log ke channel event-scan */
    private function scanLog(string $level, string $status, string $keterangan, array $context = []): void
    {
        Log::channel('event-scan')->{$level}("[{$status}] {$keterangan}", $context);
    }

    /**
     * Proses scan barcode event.
     */
    public function processScan(AbsenEventRequest $request, string $eventId)
    {
        $event = Event::findOrFail($eventId);

        $scanAt  = now()->toDateTimeString();
        $userId  = auth()->id();
        $baseCtx = [
            'waktu'      => $scanAt,
            'event_id'   => $event->id,
            'nama_event' => $event->nama_event,
            'user_id'    => $userId,
        ];

        // Rate limit
        $key = 'event-scan:' . $eventId . ':' . $userId;
        if (! RateLimiter::attempt($key, 3, function () {
            return true;
        }, 60)) {
            $this->scanLog('warning', 'GAGAL', 'Rate limit tercapai — terlalu banyak scan dalam 60 detik', $baseCtx);
            return response()->json([
                'success' => false,
                'message' => 'Terlalu banyak scan. Coba lagi nanti.'
            ], 429);
        }

        $validated = $request->validated();

        $siswa = auth()->user()->siswa;

        if (! $siswa) {
            $this->scanLog('warning', 'GAGAL', 'Data siswa tidak ditemukan untuk user ini', $baseCtx);
            return response()->json([
                'success' => false,
                'message' => 'Data siswa tidak ditemukan. Hubungi admin.'
            ], 403);
        }

        // Tambahkan info siswa ke context setelah diketahui
        $baseCtx = array_merge($baseCtx, [
            'siswa_id'   => $siswa->id,
            'nama_siswa' => $siswa->nama_lengkap,
            'nis'        => $siswa->nis,
            'jenis'      => $validated['jenis'],
        ]);

        try {
            if (! $event->isActive()) {
                $now = now();
                if ($now->gt($event->tanggal_selesai)) {
                    $message = 'Waktu scan event sudah berakhir. Kehadiran tidak dapat dicatat.';
                    $this->scanLog('warning', 'GAGAL', 'Event sudah berakhir saat scan dilakukan', array_merge($baseCtx, [
                        'tanggal_selesai' => $event->tanggal_selesai->toDateTimeString(),
                        'selisih_menit'   => round($now->diffInMinutes($event->tanggal_selesai), 1) . ' menit setelah berakhir',
                    ]));
                } elseif ($now->lt($event->tanggal_mulai)) {
                    $message = 'Event belum dimulai.';
                    $this->scanLog('warning', 'GAGAL', 'Event belum dimulai saat scan dilakukan', array_merge($baseCtx, [
                        'tanggal_mulai' => $event->tanggal_mulai->toDateTimeString(),
                    ]));
                } else {
                    $message = 'Event tidak aktif.';
                    $this->scanLog('warning', 'GAGAL', 'Event tidak aktif', $baseCtx);
                }
                return response()->json(['success' => false, 'message' => $message], 400);
            }

            if (! $event->appliesToSiswa($siswa->id)) {
                $this->scanLog('warning', 'GAGAL', 'Siswa bukan peserta event ini', $baseCtx);
                return response()->json([
                    'success' => false,
                    'message' => 'Event ini tidak berlaku untuk Anda.'
                ], 403);
            }

            if (! $event->canAbsen($validated['jenis'])) {
                $this->scanLog('warning', 'GAGAL', 'Jenis absen tidak diizinkan untuk event ini', $baseCtx);
                return response()->json([
                    'success' => false,
                    'message' => 'Absen jenis ini tidak diizinkan.'
                ], 400);
            }

            // Validasi barcode: cocokkan nilai yang dikirim dengan yang ada di DB
            if ($validated['barcode'] !== $event->barcode_value) {
                $this->scanLog('warning', 'GAGAL', 'Barcode tidak cocok (mungkin sudah dirotasi)', array_merge($baseCtx, [
                    'barcode_dikirim' => substr($validated['barcode'], 0, 8) . '...',
                ]));
                return response()->json([
                    'success' => false,
                    'message' => 'Barcode tidak valid atau sudah kadaluarsa. Refresh halaman dan coba lagi.'
                ], 400);
            }

            if ($event->barcode_rotate_detik > 0 && ! $event->isBarcodeValid()) {
                $this->scanLog('warning', 'GAGAL', 'Barcode sudah melewati batas waktu rotasi', array_merge($baseCtx, [
                    'barcode_rotate_detik' => $event->barcode_rotate_detik,
                ]));
                return response()->json([
                    'success' => false,
                    'message' => 'Barcode sudah kadaluarsa. Refresh halaman.'
                ], 400);
            }

            $jenis = $validated['jenis'];

            // Cari atau buat 1 record per siswa per event (pola unified)
            $existingRecord = AbsenEvent::where('event_id', $event->id)
                ->where('siswa_id', $siswa->id)
                ->first();

            // Validasi: jangan duplikasi pada jenis yang sama
            if ($existingRecord) {
                if ($jenis === 'masuk' && $existingRecord->waktu_masuk !== null) {
                    $this->scanLog('info', 'DUPLIKAT', 'Siswa sudah absen masuk sebelumnya', array_merge($baseCtx, [
                        'waktu_masuk_sebelumnya' => $existingRecord->waktu_masuk?->toDateTimeString(),
                    ]));
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda sudah absen masuk untuk event ini.'
                    ], 409);
                }
                if ($jenis === 'pulang' && $existingRecord->waktu_pulang !== null) {
                    $this->scanLog('info', 'DUPLIKAT', 'Siswa sudah absen pulang sebelumnya', array_merge($baseCtx, [
                        'waktu_pulang_sebelumnya' => $existingRecord->waktu_pulang?->toDateTimeString(),
                    ]));
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda sudah absen pulang untuk event ini.'
                    ], 409);
                }
                if ($jenis === 'pulang' && $existingRecord->waktu_masuk === null) {
                    $this->scanLog('warning', 'GAGAL', 'Siswa mencoba absen pulang tanpa absen masuk terlebih dahulu', $baseCtx);
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda harus absen masuk terlebih dahulu.'
                    ], 409);
                }
            }

            $absenEvent = DB::transaction(function () use ($event, $siswa, $jenis, $existingRecord) {
                if ($existingRecord) {
                    // Update record yang sudah ada dengan data pulang
                    $existingRecord->update([
                        'waktu_pulang'      => now(),
                        'barcode_pulang'    => $event->barcode_value,
                        // legacy
                        'jenis'             => 'pulang',
                        'waktu_scan'        => now(),
                        'barcode_digunakan' => $event->barcode_value,
                    ]);
                    return $existingRecord->refresh();
                } else {
                    // Buat record baru dengan data masuk
                    return AbsenEvent::create([
                        'event_id'          => $event->id,
                        'siswa_id'          => $siswa->id,
                        'waktu_masuk'       => now(),
                        'barcode_masuk'     => $event->barcode_value,
                        'wa_terkirim_ortu'  => false,
                        'created_by'        => auth()->id(),
                        // legacy
                        'jenis'             => 'masuk',
                        'waktu_scan'        => now(),
                        'barcode_digunakan' => $event->barcode_value,
                    ]);
                }
            });

            SendEventNotifJob::dispatch($absenEvent)->delay(now()->addSeconds(2));

            $this->scanLog('info', 'SUKSES', 'Absen event berhasil dicatat', array_merge($baseCtx, [
                'absen_event_id' => $absenEvent->id,
                'waktu_tercatat' => ($jenis === 'masuk'
                    ? $absenEvent->waktu_masuk
                    : $absenEvent->waktu_pulang)?->toDateTimeString(),
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Absen event berhasil! Notifikasi WA akan dikirim ke ortu.',
                'redirect' => route('event.rekap', $event)
            ]);
        } catch (\Throwable $e) {
            $this->scanLog('error', 'ERROR', 'Exception tidak terduga saat memproses scan', array_merge($baseCtx, [
                'exception' => class_basename($e),
                'pesan'     => $e->getMessage(),
                'file'      => $e->getFile(),
                'baris'     => $e->getLine(),
            ]));

            Log::error('AbsenEvent error: ' . $e->getMessage(), [
                'file'     => $e->getFile(),
                'line'     => $e->getLine(),
                'event_id' => $eventId,
                'user_id'  => $userId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan. Coba lagi. (' . class_basename($e) . ': ' . $e->getMessage() . ')'
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
            'rekap-absen-event-' . $event->nama_event . '-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function jurnal(Event $event)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(300);
        $data = $this->jurnalData($event);

        if (request()->boolean('preview')) {
            return view('pdf.event.cetak', array_merge($data, [
                'isPdf' => false,
            ]));
        }
        logger()->info(memory_get_usage(true));
        logger()->info(memory_get_peak_usage(true));

        $pdf = Pdf::loadView('pdf.event.cetak', array_merge($data, [
            'isPdf' => true,
        ]))->setPaper('a4', 'portrait');
        // $html = view('pdf.event.cetak', array_merge($data, [
        //     'isPdf' => true,
        // ]))->render();

        // dd(strlen($html));

        logger()->info(memory_get_peak_usage(true));

        return $pdf->download('jurnal-absen-event-' . $event->id . '-' . Str::slug($event->nama_event) . '.pdf');
    }

    private function jurnalData(Event $event): array
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(300);
        $event->loadMissing([
            'kelas',
            'siswa.kelas',
            'creator.gtk',
            'absenEvent.siswa.kelas',
            'photos',
        ]);

        $peserta = $this->pesertaEvent($event);
        // Pola unified: 1 record per siswa per event
        $absenBySiswa = $event->absenEvent
            ->filter(fn($absen) => $absen->siswa_id !== null)
            ->keyBy('siswa_id');

        $rows = $peserta->map(function (Siswa $siswa) use ($absenBySiswa, $event) {
            // 1 record per siswa (unified)
            $absen      = $absenBySiswa->get($siswa->id);
            // Kompatibilitas dengan data lama (masuk & pulang = record terpisah)
            $masuk  = $absen ?? null;
            $pulang = ($absen && $absen->waktu_pulang) ? $absen : null;

            $hadir      = $absen && ($absen->waktu_masuk !== null || $absen->waktu_scan !== null);
            $status     = $hadir ? 'Hadir' : 'Alpa';
            $waktuMasuk = $absen?->waktu_masuk ?? $absen?->waktu_scan;
            $jamHadir   = $waktuMasuk?->format('H:i') ?? '-';

            $keterangan = $this->keteranganAbsenUnified($event, $absen);

            return [
                'nis' => $siswa->nis ?: '-',
                'nama' => $siswa->nama_lengkap ?: '-',
                'kelas' => $siswa->kelas->nama_kelas ?? '-',
                'status' => $status,
                'jam_hadir' => $jamHadir,
                'keterangan' => $keterangan,
            ];
        })->values();

        $totalSiswa = $rows->count();
        $hadir = $rows->where('status', 'Hadir')->count();
        $alpa = $rows->where('status', 'Alpa')->count();

        $user = $event->creator ?: request()->user();
        $gtk = $user?->gtk;
        $penanggungJawab = [
            'nama' => $gtk?->nama_lengkap ?: ($user?->name ?: '-'),
            'nip' => $gtk?->nip ?: '-',
        ];

        $sekolah = sekolah_data();

        // Logo sebagai base64 agar tampil di PDF dan print (sama seperti sp/surat.blade.php)
        $logoSmkPath   = public_path('images/logo/smk.png');
        $logoJatimPath = public_path('images/logo/jatim.png');

        if (! file_exists($logoSmkPath)) {
            $logoSmkPath = resource_path('views/pdf/logo/smk.png');
        }
        if (! file_exists($logoJatimPath)) {
            $logoJatimPath = resource_path('views/pdf/logo/jatim.png');
        }

        $logoSmk   = file_exists($logoSmkPath)   ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoSmkPath))   : null;
        $logoJatim = file_exists($logoJatimPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoJatimPath)) : null;

        // Foto kegiatan: konversi ke base64 agar tampil di PDF
        $fotoKegiatan = $event->photos->map(function ($photo) {
            $absPath = storage_path('app/public/' . $photo->path);
            if (! file_exists($absPath)) {
                return null;
            }
            $mime = mime_content_type($absPath) ?: 'image/jpeg';
            return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($absPath));
        })->filter()->values()->all();

        return [
            'event' => $event,
            'sekolah' => $sekolah,
            'logoSmk' => $logoSmk,
            'logoJatim' => $logoJatim,
            'rows' => $rows,
            'rekap' => [
                'hadir' => $hadir,
                'sakit' => 0,
                'izin' => 0,
                'alpa' => $alpa,
                'total' => $totalSiswa,
                'persentase_hadir' => $totalSiswa > 0 ? round(($hadir / $totalSiswa) * 100, 1) : 0,
            ],
            'nomorDokumen' => sprintf('421.5 / EVT-%04d / %s / %s', $event->id, now()->format('m'), now()->format('Y')),
            'labelPeserta' => $this->labelPeserta($event),
            'penanggungJawab' => $penanggungJawab,
            'tanggalCetak' => now(),
            'fotoKegiatan' => $fotoKegiatan,
        ];
    }

    private function pesertaEvent(Event $event)
    {
        $query = Siswa::with('kelas')
            ->where(function ($q) {
                $q->where('status_aktif', true)->orWhereNull('status_aktif');
            });

        if (! $event->berlaku_untuk_semua) {
            if ($event->mode_peserta === 'kelas') {
                $kelasIds = $event->kelas->pluck('id');
                $query->whereIn('kelas_id', $kelasIds->all());
            } else {
                $siswaIds = $event->siswa->pluck('id');
                $query->whereIn('id', $siswaIds->all());
            }
        }

        $peserta = $query->get();
        $siswaDenganAbsen = $event->absenEvent
            ->pluck('siswa')
            ->filter()
            ->unique('id');
        return $peserta
            ->merge($siswaDenganAbsen)
            ->unique('id')
            ->sortBy(fn(Siswa $siswa) => ($siswa->kelas->nama_kelas ?? 'ZZZ') . '|' . ($siswa->nama_lengkap ?? ''))
            ->values();
    }

    private function keteranganAbsenUnified(Event $event, ?AbsenEvent $absen): string
    {
        if (! $absen) {
            return 'Belum melakukan scan absen event';
        }

        $parts = [];
        $waktuMasuk = $absen->waktu_masuk ?? $absen->waktu_scan;

        if ($event->ada_absen_masuk) {
            $parts[] = $waktuMasuk
                ? 'Masuk ' . $waktuMasuk->format('H:i')
                : 'Belum scan masuk';
        }

        if ($event->ada_absen_pulang) {
            $parts[] = $absen->waktu_pulang
                ? 'Pulang ' . $absen->waktu_pulang->format('H:i')
                : 'Belum scan pulang';
        }

        return implode(', ', $parts) ?: 'Sudah melakukan scan';
    }

    private function keteranganAbsen(Event $event, ?AbsenEvent $masuk, ?AbsenEvent $pulang, ?AbsenEvent $scanPertama): string
    {
        if (! $scanPertama) {
            return 'Belum melakukan scan absen event';
        }

        $parts = [];

        if ($event->ada_absen_masuk) {
            $parts[] = $masuk
                ? 'Masuk ' . $masuk->waktu_scan?->format('H:i')
                : 'Belum scan masuk';
        }

        if ($event->ada_absen_pulang) {
            $parts[] = $pulang
                ? 'Pulang ' . $pulang->waktu_scan?->format('H:i')
                : 'Belum scan pulang';
        }

        return implode(', ', $parts) ?: 'Sudah melakukan scan';
    }

    private function labelPeserta(Event $event): string
    {
        if ($event->berlaku_untuk_semua) {
            return 'Semua kelas';
        }

        if ($event->mode_peserta === 'kelas') {
            return $event->kelas->pluck('nama_kelas')->filter()->join(', ') ?: 'Kelas tertentu';
        }

        return 'Siswa pilihan';
    }
}
