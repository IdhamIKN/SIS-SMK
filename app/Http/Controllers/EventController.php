<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventStoreRequest;
use App\Http\Requests\EventUpdateRequest;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventPhoto;
use App\Models\Kelas;
use App\Models\Pelanggaran;
use App\Models\Penghargaan;
use App\Models\Siswa;
use App\Models\SubPasal;
use App\Services\EventRecurrenceService;
use App\Services\TatibPoinService;
use App\Services\WhatsappService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EventController extends Controller
{
    /**
     * Role yang otomatis dapat melihat SEMUA event tanpa filter.
     * Dilengkapi juga via permission 'view_all_events' agar user
     * tertentu (misal gtk tertentu) bisa diberi akses view all secara manual.
     */
    protected const ROLES_VIEW_ALL = [
        'superadmin',
        'kepsek',
        'waka',
        'kurikulum',
        'admin_tatib',
        'bk',
    ];

    public function __construct(
        protected WhatsappService $whatsappService,
        protected EventRecurrenceService $eventRecurrenceService,
        protected TatibPoinService $tatibPoinService,
    ) {}

    /**
     * Apakah user saat ini boleh melihat semua event?
     *
     * TRUE jika:
     *   - Punya salah satu role di ROLES_VIEW_ALL, ATAU
     *   - Punya permission 'view_all_events' (diberikan manual ke gtk/wali_kelas tertentu)
     */
    protected function canViewAllEvents(): bool
    {
        $user = auth()->user();
        return $user->hasAnyRole(self::ROLES_VIEW_ALL)
            || $user->hasPermissionTo('view_all_events');
    }

    /**
     * Display a listing of the events.
     *
     * Visibility:
     *   - superadmin/kepsek/waka/kurikulum/admin_tatib/bk → semua event
     *   - gtk/wali_kelas (tanpa permission) → hanya event yang dia buat (created_by)
     *   - gtk/wali_kelas (dengan permission view_all_events) → semua event
     *   - siswa → hanya event yang dia termasuk pesertanya
     */
    public function index(): View
    {
        $user        = auth()->user();
        $historyDays = 100;
        $request     = request();

        $query = Event::with(['category', 'recurrenceRule'])
            ->withCount('absenEvent');

        // ── FILTER VISIBILITY ─────────────────────────────────────────────
        if ($user->hasRole('siswa')) {
            $this->applyFilterSiswa($query, $user);
        } elseif (!$this->canViewAllEvents()) {
            $query->where('created_by', $user->id);
        }

        // ── FILTER PENCARIAN ──────────────────────────────────────────────
        if ($request->filled('search')) {
            $query->where('nama_event', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('tanggal')) {
            $tanggal = $request->tanggal;
            $query->where(function ($q) use ($tanggal) {
                $q->whereDate('tanggal_mulai', $tanggal)
                    ->orWhereDate('tanggal_selesai', $tanggal);
            });
        }

        // ── FILTER TAB ────────────────────────────────────────────────────
        $tab = $request->get('tab', 'semua'); // semua | aktif | upcoming | riwayat

        if ($tab === 'aktif') {
            $query->where('tanggal_mulai', '<=', now())
                ->where('tanggal_selesai', '>=', now());
        } elseif ($tab === 'upcoming') {
            $query->where('tanggal_mulai', '>', now());
        } elseif ($tab === 'riwayat') {
            $query->where('tanggal_selesai', '<', now());
            // Tidak ada filter historyDays saat tab riwayat — tampilkan semua riwayat
        } else {
            // Tab "semua": tampilkan semua (aktif + upcoming + riwayat)
            // Tidak ada filter tanggal tambahan
        }

        // ── URUTAN ────────────────────────────────────────────────────────
        // ── URUTAN ────────────────────────────────────────────────────────────
        if ($tab === 'riwayat') {
            // Riwayat: event paling baru (tanggal_selesai terbesar) di atas
            $query->orderBy('tanggal_selesai', 'desc');
        } elseif ($tab === 'upcoming') {
            // Upcoming: event paling dekat di atas
            $query->orderBy('tanggal_mulai', 'asc');
        } elseif ($tab === 'aktif') {
            $query->orderBy('tanggal_mulai', 'desc');
        } else {
            // Tab semua: aktif → upcoming → riwayat
            $query->orderByRaw("
        CASE
            WHEN tanggal_mulai <= NOW() AND tanggal_selesai >= NOW() THEN 0
            WHEN tanggal_mulai > NOW()                               THEN 1
            ELSE                                                          2
        END ASC
    ")->orderByRaw("
        CASE
            WHEN tanggal_mulai <= NOW() AND tanggal_selesai >= NOW()
                THEN tanggal_mulai
            WHEN tanggal_mulai > NOW()
                THEN tanggal_mulai
            ELSE
                tanggal_selesai
        END ASC   -- ← ganti DESC → ASC
    ");
        }

        $totalActive = Event::query()
            ->when(!$this->canViewAllEvents() && !$user->hasRole('siswa'), fn($q) => $q->where('created_by', $user->id))
            ->where('tanggal_mulai', '<=', now())
            ->where('tanggal_selesai', '>=', now())
            ->count();

        $events = $query->paginate(10)->withQueryString();

        Log::channel('sis')->info('[Event] Index', [
            'user_id'      => $user->id,
            'role'         => $user->getRoleNames()->first(),
            'can_view_all' => $this->canViewAllEvents(),
            'tab'          => $tab,
            'total'        => $events->total(),
            'active_count' => $totalActive,
        ]);

        return view('event.index', compact('events', 'totalActive', 'historyDays', 'tab'));
    }
    // public function index(): View
    // {
    //     $user        = auth()->user();
    //     $historyDays = 30;

    //     $query = Event::with(['category', 'recurrenceRule'])
    //         ->withCount('absenEvent')
    //         ->where(function ($q) use ($historyDays) {
    //             // ->where(function ($q)  {
    //             // 1. Sedang berlangsung sekarang
    //             $q->where(function ($active) {
    //                 $active->where('tanggal_mulai', '<=', now())
    //                     ->where('tanggal_selesai', '>=', now());
    //             })
    //                 // 2. Sudah selesai dalam N hari terakhir (riwayat)
    //                 ->orWhere(function ($recent) use ($historyDays) {
    //                     // ->orWhere(function ($recent) {
    //                     $recent->where('tanggal_selesai', '<', now())
    //                         //   ->where('tanggal_selesai', '>=', now()->subDays($historyDays)->startOfDay());
    //                         ->where('tanggal_selesai', '>=', now()->subDays($historyDays)->startOfDay());
    //                 })
    //                 // 3. Akan dimulai hari ini
    //                 ->orWhere(function ($upcoming) {
    //                     $upcoming->whereDate('tanggal_mulai', today())
    //                         ->where('tanggal_mulai', '>', now());
    //                 });
    //         });

    //     // Pencarian
    //     $request = request();

    //     // Filter nama event
    //     if ($request->filled('search')) {
    //         $query->where('nama_event', 'like', '%' . $request->search . '%');
    //     }

    //     // Filter tanggal
    //     if ($request->filled('tanggal')) {
    //         $tanggal = $request->tanggal;

    //         $query->where(function ($q) use ($tanggal) {
    //             $q->whereDate('tanggal_mulai', $tanggal)
    //                 ->orWhereDate('tanggal_selesai', $tanggal);
    //         });
    //     }

    //     // ── FILTER VISIBILITY ─────────────────────────────────────────────

    //     if ($user->hasRole('siswa')) {
    //         // SISWA: hanya event yang dia termasuk peserta
    //         $this->applyFilterSiswa($query, $user);
    //     } elseif (!$this->canViewAllEvents()) {
    //         // GTK / WALI_KELAS tanpa permission → hanya event buatan sendiri
    //         $query->where('created_by', $user->id);
    //     }
    //     // else: canViewAllEvents() = true → tidak ada filter tambahan

    //     // Urutan: aktif → upcoming → riwayat terbaru
    //     $query->orderByRaw("
    //         CASE
    //             WHEN tanggal_mulai <= NOW() AND tanggal_selesai >= NOW() THEN 0
    //             WHEN tanggal_mulai > NOW()                               THEN 1
    //             ELSE 2
    //         END ASC,
    //         tanggal_mulai DESC
    //     ");

    //     $totalActive = (clone $query)
    //         ->where('tanggal_mulai', '<=', now())
    //         ->where('tanggal_selesai', '>=', now())
    //         ->count();

    //     $events = $query->paginate(10);

    //     Log::channel('sis')->info('[Event] Index', [
    //         'user_id'       => $user->id,
    //         'role'          => $user->getRoleNames()->first(),
    //         'can_view_all'  => $this->canViewAllEvents(),
    //         'total'         => $events->total(),
    //         'active_count'  => $totalActive,
    //     ]);

    //     return view('event.index', compact('events', 'totalActive'));
    // }

    /**
     * Filter query untuk role siswa.
     * Hanya event yang:
     *   1. berlaku_untuk_semua = true, ATAU
     *   2. mode_peserta = kelas dan kelas siswa ada di daftar, ATAU
     *   3. mode_peserta = siswa dan siswa ada di daftar
     */
    protected function applyFilterSiswa($query, $user): void
    {
        $siswa = $user->siswa;
        if (!$siswa) {
            // Tidak punya data siswa → tidak tampilkan apapun
            $query->whereRaw('1 = 0');
            return;
        }

        $query->where(function ($q) use ($siswa) {
            $q->where('berlaku_untuk_semua', true)
                ->orWhere(function ($sq) use ($siswa) {
                    $sq->where('mode_peserta', 'kelas')
                        ->where('berlaku_untuk_semua', false)
                        ->whereHas('kelas', fn($kq) => $kq->where('kelas.id', $siswa->kelas_id));
                })
                ->orWhere(function ($sq) use ($siswa) {
                    $sq->where('mode_peserta', 'siswa')
                        ->where('berlaku_untuk_semua', false)
                        ->whereHas('siswa', fn($siq) => $siq->where('siswas.id', $siswa->id));
                });
        });
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

        $categories = EventCategory::orderBy('nama_kategori')->get();

        $pasalPelanggaran = $this->tatibPoinService->subPasalOptions('pelanggaran', $this->tatibPoinService->tahunAjaranAktif());
        $pasalPenghargaan = $this->tatibPoinService->subPasalOptions('penghargaan', $this->tatibPoinService->tahunAjaranAktif());

        return view('event.create', compact('kelas', 'siswa', 'categories', 'pasalPelanggaran', 'pasalPenghargaan'));
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
            'id'   => $k->id,
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
            'id'   => $s->id,
            'text' => $s->nama_lengkap,
            'meta' => $s->kelas->nama_kelas ?? '',
        ]));
    }

    /**
     * Store a newly created event in storage.
     */
    public function store(EventStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $berlakuUntukSemua = $request->boolean('berlaku_untuk_semua');
        $adaAbsenMasuk     = $request->boolean('ada_absen_masuk');
        $adaAbsenPulang    = $request->boolean('ada_absen_pulang');
        $autoPointPelanggaran = $request->boolean('auto_point_pelanggaran');
        $autoPenghargaan      = $request->boolean('auto_penghargaan');
        $isEkstrakurikuler    = $request->boolean('is_ekstrakurikuler');

        DB::beginTransaction();
        try {
            $category = EventCategory::findOrCreateByName($validated['event_category_name'] ?? null);

            $event = Event::create(array_merge($this->eventPayload($validated), [
                'created_by'                  => auth()->id(),
                'event_category_id'           => $category?->id,
                'berlaku_untuk_semua'         => $berlakuUntukSemua,
                'ada_absen_masuk'             => $adaAbsenMasuk,
                'ada_absen_pulang'            => $adaAbsenPulang,
                'auto_point_pelanggaran'      => $autoPointPelanggaran,
                'pasal_pelanggaran_id'        => $autoPointPelanggaran ? ($validated['pasal_pelanggaran_id'] ?? null) : null,
                'poin_pelanggaran_event'      => $autoPointPelanggaran ? ($validated['poin_pelanggaran_event'] ?? null) : null,
                'auto_penghargaan'            => $autoPenghargaan,
                'pasal_penghargaan_id'        => $autoPenghargaan ? ($validated['pasal_penghargaan_id'] ?? null) : null,
                'poin_penghargaan_event'      => $autoPenghargaan ? ($validated['poin_penghargaan_event'] ?? null) : null,
                'is_ekstrakurikuler'          => $isEkstrakurikuler,
                'pelatih_1'                   => $isEkstrakurikuler ? ($validated['pelatih_1'] ?? null) : null,
                'pelatih_2'                   => $isEkstrakurikuler ? ($validated['pelatih_2'] ?? null) : null,
                'pelatih_3'                   => $isEkstrakurikuler ? ($validated['pelatih_3'] ?? null) : null,
                'pembina_nama'                => $isEkstrakurikuler ? ($validated['pembina_nama'] ?? null) : null,
                'pembina_nip'                 => $isEkstrakurikuler ? ($validated['pembina_nip'] ?? null) : null,
                'barcode_value'               => hash('sha256', microtime() . random_bytes(16)),
                'barcode_updated_at'          => now(),
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

            // Upload foto kegiatan
            $this->syncEventPhotos($event, $request, []);

            $generatedCount = $this->eventRecurrenceService->sync($event, $validated);

            DB::commit();

            $message = 'Event berhasil dibuat.';
            if ($generatedCount > 0) {
                $message .= " {$generatedCount} event berulang otomatis dibuat.";
            }

            return redirect()->route('event.show', $event)->with('success', $message);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Event store error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal membuat event: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified event.
     *
     * Guard: guru yang bukan pemilik dan tidak punya akses view_all
     * akan mendapat 403.
     */
    public function show(Event $event): View
    {
        $this->authorizeEventAccess($event);

        $event->load([
            'category',
            'recurrenceRule',
            'recurringParent',
            'recurringChildren',
            'kelas',
            'siswa.kelas',
            'absenEvent.siswa.kelas',
            'photos',
        ]);

        $rekap = $event->absenEvent()
            ->selectRaw('jenis, COUNT(*) as total')
            ->groupBy('jenis')
            ->pluck('total', 'jenis');

        $user = auth()->user();
        $onlySiswaId = $user->hasRole('siswa') ? ($user->siswa->id ?? null) : null;
        $eventAttendanceRows = $this->buildEventAttendanceRows($event, $onlySiswaId);
        $eventAttendanceSummary = [
            'total' => $eventAttendanceRows->count(),
            'hadir' => $eventAttendanceRows->where('hadir', true)->count(),
            'tidak_hadir' => $eventAttendanceRows->where('hadir', false)->count(),
            'pelanggaran' => $eventAttendanceRows->sum(fn ($row) => $row['pelanggaran']->count()),
            'penghargaan' => $eventAttendanceRows->sum(fn ($row) => $row['penghargaan']->count()),
        ];

        $tahunAjaran = $this->tatibPoinService->tahunAjaranAktif();
        $pasalPelanggaranOptions = $this->tatibPoinService->subPasalOptions('pelanggaran', $tahunAjaran);
        $pasalPenghargaanOptions = $this->tatibPoinService->subPasalOptions('penghargaan', $tahunAjaran);

        Log::channel('sis')->info('[Event] Detail ditampilkan', [
            'event_id' => $event->id,
            'user_id'  => auth()->id(),
        ]);

        return view('event.show', compact(
            'event', 'rekap', 'eventAttendanceRows', 'eventAttendanceSummary',
            'pasalPelanggaranOptions', 'pasalPenghargaanOptions'
        ));
    }

    public function bulkDeleteEventTatibPoints(Request $request, Event $event): RedirectResponse
    {
        $this->authorizeEventAccess($event);

        if (auth()->user()->hasRole('siswa')) {
            abort(403, 'Siswa tidak dapat menghapus poin event.');
        }

        $validated = $request->validate([
            'pelanggaran_ids' => ['nullable', 'array'],
            'pelanggaran_ids.*' => ['integer'],
            'penghargaan_ids' => ['nullable', 'array'],
            'penghargaan_ids.*' => ['integer'],
        ]);

        $pelanggaranIds = array_values(array_unique(array_map('intval', $validated['pelanggaran_ids'] ?? [])));
        $penghargaanIds = array_values(array_unique(array_map('intval', $validated['penghargaan_ids'] ?? [])));

        if (empty($pelanggaranIds) && empty($penghargaanIds)) {
            return back()->with('error', 'Tidak ada pelanggaran atau penghargaan event yang dipilih.');
        }

        $deletedPelanggaran = 0;
        $deletedPenghargaan = 0;

        DB::transaction(function () use ($event, $pelanggaranIds, $penghargaanIds, &$deletedPelanggaran, &$deletedPenghargaan) {
            if (! empty($pelanggaranIds)) {
                Pelanggaran::query()
                    ->where('deviceid', $this->eventPelanggaranDeviceId($event))
                    ->whereIn('idpel', $pelanggaranIds)
                    ->get()
                    ->each(function (Pelanggaran $pelanggaran) use (&$deletedPelanggaran) {
                        $this->tatibPoinService->deleteTransaction('PN', $pelanggaran->tgl, $pelanggaran->getKey());
                        $pelanggaran->delete();
                        $deletedPelanggaran++;
                    });
            }

            if (! empty($penghargaanIds)) {
                Penghargaan::query()
                    ->where('deviceid', $this->eventPenghargaanDeviceId($event))
                    ->whereIn('idpen', $penghargaanIds)
                    ->get()
                    ->each(function (Penghargaan $penghargaan) use (&$deletedPenghargaan) {
                        $this->tatibPoinService->deleteTransaction('RW', $penghargaan->tgl, $penghargaan->getKey());
                        $penghargaan->delete();
                        $deletedPenghargaan++;
                    });
            }
        });

        $totalDeleted = $deletedPelanggaran + $deletedPenghargaan;

        Log::channel('sis')->info('[Event] Poin event dihapus massal', [
            'event_id' => $event->id,
            'user_id' => auth()->id(),
            'pelanggaran' => $deletedPelanggaran,
            'penghargaan' => $deletedPenghargaan,
        ]);

        if ($totalDeleted === 0) {
            return back()->with('error', 'Data yang dipilih tidak ditemukan pada event ini.');
        }

        return back()->with(
            'success',
            "{$deletedPelanggaran} pelanggaran dan {$deletedPenghargaan} penghargaan event berhasil dihapus beserta transaksinya."
        );
    }

    /**
     * Edit massal status kehadiran siswa beserta auto-create/delete pelanggaran & penghargaan.
     */
    public function bulkUpdateAttendanceStatus(Request $request, Event $event): RedirectResponse
    {
        $this->authorizeEventAccess($event);

        if (auth()->user()->hasRole('siswa')) {
            abort(403, 'Siswa tidak dapat mengubah status kehadiran event.');
        }

        // Time-gating: hanya setelah event selesai
        if (now()->lt($event->tanggal_selesai)) {
            return back()->with('error', 'Fitur ini hanya tersedia setelah event selesai.');
        }

        $validated = $request->validate([
            'updates'            => ['required', 'array', 'min:1'],
            'updates.*.siswa_id' => ['required', 'integer', 'exists:siswas,id'],
            'updates.*.status'   => ['required', 'in:hadir,tidak_hadir'],
            'updates.*.idpasal'  => ['required', 'string'],
        ]);

        $tahunAjaran  = $this->tatibPoinService->tahunAjaranAktif();
        $pelDeviceId  = $this->eventPelanggaranDeviceId($event);
        $penDeviceId  = $this->eventPenghargaanDeviceId($event);
        $pelapor      = auth()->user()->name ?? 'System';
        $tgl          = $event->tanggal_mulai;

        $processed = 0;

        DB::transaction(function () use (
            $validated, $event, $tahunAjaran, $pelDeviceId, $penDeviceId,
            $pelapor, $tgl, &$processed
        ) {
            foreach ($validated['updates'] as $item) {
                $siswaId    = (int) $item['siswa_id'];
                $newStatus  = $item['status'];    // 'hadir' | 'tidak_hadir'
                $idPasal    = $item['idpasal'];

                $siswa = Siswa::with('kelas')->find($siswaId);
                if (! $siswa) {
                    continue;
                }

                // Cek status kehadiran saat ini
                $absenRecord  = $event->absenEvent()->where('siswa_id', $siswaId)->first();
                $currentHadir = $absenRecord && ($absenRecord->waktu_masuk !== null || $absenRecord->waktu_scan !== null);

                // Cari pasal
                $subPasal = $this->tatibPoinService->findSubPasal($idPasal, $tahunAjaran);
                if (! $subPasal) {
                    continue;
                }

                // Hitung poin: gunakan skormax, fallback ke skormin, fallback ke 0
                $poinPasal = (int) ($subPasal->skormax ?: $subPasal->skormin ?: 0);

                if ($newStatus === 'tidak_hadir') {
                    // Hapus penghargaan event (jika ada)
                    Penghargaan::query()
                        ->where('deviceid', $penDeviceId)
                        ->where('siswa_id', $siswaId)
                        ->get()
                        ->each(function (Penghargaan $p) {
                            $this->tatibPoinService->deleteTransaction('RW', $p->tgl, $p->getKey());
                            $p->delete();
                        });

                    // Hapus pelanggaran event lama lalu buat baru
                    Pelanggaran::query()
                        ->where('deviceid', $pelDeviceId)
                        ->where('siswa_id', $siswaId)
                        ->get()
                        ->each(function (Pelanggaran $p) {
                            $this->tatibPoinService->deleteTransaction('PN', $p->tgl, $p->getKey());
                            $p->delete();
                        });

                    $snapshot = $this->tatibPoinService->snapshotSiswa($siswa);
                    $pelanggaran = Pelanggaran::create([
                        'siswa_id'     => $siswaId,
                        'tgl'          => $tgl,
                        'tahun_ajaran' => $tahunAjaran,
                        'deviceid'     => $pelDeviceId,
                        'noreg'        => $snapshot['noreg'],
                        'nama'         => $snapshot['nama'],
                        'kelas'        => $snapshot['nmkelas'],
                        'idpasal'      => $subPasal->idpasal,
                        'isi'          => $subPasal->pasal ?? $subPasal->idpasal,
                        'poin'         => $poinPasal,
                        'pelapor'      => $pelapor,
                        'created_by'   => auth()->id(),
                    ]);
                    $this->tatibPoinService->createPelanggaranTransaction($pelanggaran, $siswa);

                    // Update / hapus absen record
                    if ($absenRecord) {
                        $absenRecord->update(['waktu_masuk' => null, 'waktu_scan' => null, 'waktu_pulang' => null]);
                    }
                } else {
                    // $newStatus === 'hadir'
                    // Hapus pelanggaran event (jika ada)
                    Pelanggaran::query()
                        ->where('deviceid', $pelDeviceId)
                        ->where('siswa_id', $siswaId)
                        ->get()
                        ->each(function (Pelanggaran $p) {
                            $this->tatibPoinService->deleteTransaction('PN', $p->tgl, $p->getKey());
                            $p->delete();
                        });

                    // Hapus penghargaan event lama lalu buat baru
                    Penghargaan::query()
                        ->where('deviceid', $penDeviceId)
                        ->where('siswa_id', $siswaId)
                        ->get()
                        ->each(function (Penghargaan $p) {
                            $this->tatibPoinService->deleteTransaction('RW', $p->tgl, $p->getKey());
                            $p->delete();
                        });

                    $snapshot = $this->tatibPoinService->snapshotSiswa($siswa);
                    $penghargaan = Penghargaan::create([
                        'siswa_id'     => $siswaId,
                        'tgl'          => $tgl,
                        'tahun_ajaran' => $tahunAjaran,
                        'deviceid'     => $penDeviceId,
                        'noreg'        => $snapshot['noreg'],
                        'nama'         => $snapshot['nama'],
                        'kelas'        => $snapshot['nmkelas'],
                        'idpasal'      => $subPasal->idpasal,
                        'isi'          => $subPasal->pasal ?? $subPasal->idpasal,
                        'poin'         => $poinPasal,
                        'pelapor'      => $pelapor,
                        'created_by'   => auth()->id(),
                        'acc'          => 1,
                        'tglacc'       => now(),
                        'nmacc'        => $pelapor,
                    ]);
                    $this->tatibPoinService->createPenghargaanTransaction($penghargaan, $siswa);

                    // Tandai hadir jika belum ada record absen
                    if (! $absenRecord) {
                        $event->absenEvent()->create([
                            'siswa_id'    => $siswaId,
                            'waktu_scan'  => $tgl,
                            'waktu_masuk' => $tgl,
                            'jenis'       => 'masuk',
                        ]);
                    }
                }

                $processed++;
            }
        });

        Log::channel('sis')->info('[Event] Status kehadiran diupdate massal', [
            'event_id'  => $event->id,
            'user_id'   => auth()->id(),
            'processed' => $processed,
        ]);

        if ($processed === 0) {
            return back()->with('error', 'Tidak ada data yang berhasil diubah. Pastikan pasal dipilih dengan benar.');
        }

        return back()->with('success', "{$processed} data kehadiran berhasil diperbarui beserta poin terkait.");
    }

    /**
     * Show the form for editing the specified event.
     *
     * Guard: hanya pemilik atau yang punya canViewAllEvents + bukan siswa
     */
    public function edit(Event $event): View
    {
        $this->authorizeEventAccess($event);

        $event->load(['category', 'recurrenceRule', 'recurringParent', 'recurringChildren', 'kelas', 'siswa']);

        $kelas = Kelas::with('jurusan')->orderBy('nama_kelas')->get();
        $siswa = Siswa::with('kelas')->where('status_aktif', true)->orderBy('nama_lengkap')->get();
        $categories = EventCategory::orderBy('nama_kategori')->get();
        $pasalPelanggaran = $this->tatibPoinService->subPasalOptions('pelanggaran', $this->tatibPoinService->tahunAjaranAktif());
        $pasalPenghargaan = $this->tatibPoinService->subPasalOptions('penghargaan', $this->tatibPoinService->tahunAjaranAktif());

        return view('event.edit', compact('event', 'kelas', 'siswa', 'categories', 'pasalPelanggaran', 'pasalPenghargaan'));
    }

    /**
     * Update the specified event in storage.
     */
    public function update(EventUpdateRequest $request, Event $event): RedirectResponse
    {
        $this->authorizeEventAccess($event);

        $validated = $request->validated();

        $berlakuUntukSemua = $request->boolean('berlaku_untuk_semua');
        $adaAbsenMasuk     = $request->boolean('ada_absen_masuk');
        $adaAbsenPulang    = $request->boolean('ada_absen_pulang');
        $autoPointPelanggaran = $request->boolean('auto_point_pelanggaran');
        $autoPenghargaan      = $request->boolean('auto_penghargaan');
        $isEkstrakurikuler    = $request->boolean('is_ekstrakurikuler');

        DB::beginTransaction();
        try {
            $category = EventCategory::findOrCreateByName($validated['event_category_name'] ?? null);

            $event->update(array_merge($this->eventPayload($validated), [
                'event_category_id'           => $category?->id,
                'berlaku_untuk_semua'         => $berlakuUntukSemua,
                'ada_absen_masuk'             => $adaAbsenMasuk,
                'ada_absen_pulang'            => $adaAbsenPulang,
                'auto_point_pelanggaran'      => $autoPointPelanggaran,
                'pasal_pelanggaran_id'        => $autoPointPelanggaran ? ($validated['pasal_pelanggaran_id'] ?? null) : null,
                'poin_pelanggaran_event'      => $autoPointPelanggaran ? ($validated['poin_pelanggaran_event'] ?? null) : null,
                'auto_penghargaan'            => $autoPenghargaan,
                'pasal_penghargaan_id'        => $autoPenghargaan ? ($validated['pasal_penghargaan_id'] ?? null) : null,
                'poin_penghargaan_event'      => $autoPenghargaan ? ($validated['poin_penghargaan_event'] ?? null) : null,
                'is_ekstrakurikuler'          => $isEkstrakurikuler,
                'pelatih_1'                   => $isEkstrakurikuler ? ($validated['pelatih_1'] ?? null) : null,
                'pelatih_2'                   => $isEkstrakurikuler ? ($validated['pelatih_2'] ?? null) : null,
                'pelatih_3'                   => $isEkstrakurikuler ? ($validated['pelatih_3'] ?? null) : null,
                'pembina_nama'                => $isEkstrakurikuler ? ($validated['pembina_nama'] ?? null) : null,
                'pembina_nip'                 => $isEkstrakurikuler ? ($validated['pembina_nip'] ?? null) : null,
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

            // Hapus foto yang dipilih + upload foto baru
            $this->syncEventPhotos($event, $request, $validated['hapus_foto'] ?? []);

            $generatedCount = $this->eventRecurrenceService->sync($event, $validated);

            DB::commit();

            $message = 'Event berhasil diperbarui.';
            if ($generatedCount > 0) {
                $message .= " {$generatedCount} event berulang otomatis disiapkan.";
            }

            return redirect()->route('event.show', $event)->with('success', $message);
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
        $this->authorizeEventAccess($event);

        $event->delete();

        Log::channel('sis')->info('[Event] Dihapus', [
            'event_id' => $event->id,
            'user_id'  => auth()->id(),
        ]);

        return redirect()->route('event.index')->with('success', 'Event berhasil dihapus.');
    }

    /**
     * Generate ulang barcode untuk event (manual).
     */
    public function rotateBarcode(Event $event): RedirectResponse
    {
        $this->authorizeEventAccess($event);

        $barcode = $event->rotateBarcode();

        Log::channel('sis')->info('[Event] Barcode di-rotate', [
            'event_id'     => $event->id,
            'rotate_detik' => $event->barcode_rotate_detik,
        ]);

        return back()->with('success', 'Barcode di-rotate: ' . substr($barcode, 0, 16) . '...');
    }

    /**
     * API: Ambil barcode terbaru (GET) — untuk polling status saja.
     */
    public function getBarcode(Event $event): JsonResponse
    {
        return response()->json([
            'barcode_value'      => $event->barcode_value,
            'barcode_updated_at' => $event->barcode_updated_at?->toIso8601String(),
            'rotate_detik'       => $event->barcode_rotate_detik,
            'is_valid'           => $event->isBarcodeValid(),
        ]);
    }

    /**
     * API: Rotate barcode (POST) — dipanggil klien saat countdown habis.
     */
    public function updateBarcode(Event $event): JsonResponse
    {
        $updatedAt     = $event->barcode_updated_at ?? now()->subYears(1);
        $secondsPassed = abs(now()->diffInSeconds($updatedAt, false));
        $rotateDetik   = (int) $event->barcode_rotate_detik;
        $tolerance     = 2;

        if ($rotateDetik > 0 && $secondsPassed < ($rotateDetik - $tolerance)) {
            return response()->json([
                'barcode_value'  => $event->barcode_value,
                'updated_at_ms'  => $updatedAt->valueOf(),
                'rotated'        => false,
            ]);
        }

        $newBarcode = Str::uuid()->toString();
        $now        = now();

        $event->update([
            'barcode_value'      => $newBarcode,
            'barcode_updated_at' => $now,
        ]);

        Log::channel('absen')->info('[Event] Barcode rotated (auto)', [
            'event_id'      => $event->id,
            'barcode_value' => $newBarcode,
        ]);

        return response()->json([
            'barcode_value' => $newBarcode,
            'updated_at_ms' => $now->valueOf(),
            'rotated'       => true,
        ]);
    }

    public function streamBarcode(Event $event): StreamedResponse
    {
        return $this->barcodeStream($event);
    }

    public function barcodeStream(Event $event): StreamedResponse
    {
        session()->save();
        session_write_close();

        return response()->stream(function () use ($event) {
            $maxDuration    = 300;
            $pollInterval   = 2;
            $heartbeatEvery = 15;
            $startTime      = time();
            $lastBarcode    = null;
            $lastHeartbeat  = 0;

            $event->refresh();
            $lastBarcode = $event->barcode_value;

            $this->sseEvent('barcode', [
                'barcode_value' => $event->barcode_value,
                'updated_at_ms' => optional($event->barcode_updated_at)->valueOf() ?? (time() * 1000),
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
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache, no-store',
            'X-Accel-Buffering' => 'no',
            'Connection'        => 'keep-alive',
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function buildEventAttendanceRows(Event $event, ?int $onlySiswaId = null): Collection
    {
        $participants = $this->eventParticipants($event);

        if ($onlySiswaId) {
            $participants = $participants->where('id', $onlySiswaId)->values();

            if ($participants->isEmpty()) {
                $siswa = Siswa::with('kelas')->find($onlySiswaId);
                $participants = $siswa ? collect([$siswa]) : collect();
            }
        }

        $absenRecords = $event->absenEvent
            ->filter(fn ($absen) => $absen->siswa_id !== null)
            ->when($onlySiswaId, fn (Collection $rows) => $rows->where('siswa_id', $onlySiswaId))
            ->keyBy('siswa_id');

        $pelanggaranRecords = Pelanggaran::with(['siswa.kelas', 'subPasal'])
            ->where('deviceid', $this->eventPelanggaranDeviceId($event))
            ->when($onlySiswaId, fn ($query) => $query->where('siswa_id', $onlySiswaId))
            ->orderBy('idpel')
            ->get();

        $penghargaanRecords = Penghargaan::with(['siswa.kelas', 'subPasal'])
            ->where('deviceid', $this->eventPenghargaanDeviceId($event))
            ->when($onlySiswaId, fn ($query) => $query->where('siswa_id', $onlySiswaId))
            ->orderBy('idpen')
            ->get();

        $participants = $participants
            ->merge($pelanggaranRecords->pluck('siswa')->filter())
            ->merge($penghargaanRecords->pluck('siswa')->filter())
            ->unique('id')
            ->sortBy(fn (Siswa $siswa) => ($siswa->kelas->nama_kelas ?? 'ZZZ') . '|' . ($siswa->nama_lengkap ?? ''))
            ->values();

        $pelanggaranBySiswa = $pelanggaranRecords->groupBy('siswa_id');
        $penghargaanBySiswa = $penghargaanRecords->groupBy('siswa_id');

        // Untuk non-siswa: filter out siswa yang tidak hadir event DAN tidak hadir pagi.
        // Aturan: siswa ditampilkan HANYA jika (a) punya data scan event, ATAU (b) absen pagi = hadir/terlambat.
        // Siswa yang tidak scan event dan absen paginya alfa/sakit/izin/lainnya tidak perlu ditampilkan.
        $isNonSiswa = $onlySiswaId === null;

        if ($isNonSiswa) {
            $tanggalEvent = $event->tanggal_mulai->toDateString();
            $siswaIdsWithScan = $absenRecords->keys()->all();

            // Ambil semua siswa_id yang hadir pagi pada hari event (status_masuk = hadir/terlambat)
            $siswaHadirPagi = \App\Models\AbsenSiswa::whereIn(
                    'siswa_id',
                    $participants->pluck('id')->all()
                )
                ->whereDate('tanggal', $tanggalEvent)
                ->where(function ($q) {
                    $q->whereIn('status_masuk', ['hadir', 'terlambat'])
                      ->orWhere(function ($q2) {
                          $q2->whereNull('status_masuk')
                             ->whereIn('status', ['hadir', 'terlambat']);
                      });
                })
                ->pluck('siswa_id')
                ->flip() // jadikan key untuk O(1) lookup
                ->all();

            $participants = $participants->filter(function (Siswa $siswa) use ($siswaIdsWithScan, $siswaHadirPagi) {
                $hasScan = in_array($siswa->id, $siswaIdsWithScan, true);
                $hadirPagi = isset($siswaHadirPagi[$siswa->id]);

                // Tampilkan jika: ada scan event, ATAU hadir pagi
                return $hasScan || $hadirPagi;
            })->values();
        }

        return $participants->map(function (Siswa $siswa) use ($event, $absenRecords, $pelanggaranBySiswa, $penghargaanBySiswa) {
            $absen = $absenRecords->get($siswa->id);
            $waktuMasuk = $absen?->waktu_masuk ?? $absen?->waktu_scan;
            $waktuPulang = $absen?->waktu_pulang;
            $hadir = $waktuMasuk !== null;

            return [
                'siswa' => $siswa,
                'absen' => $absen,
                'hadir' => $hadir,
                'status' => $hadir ? 'Hadir' : 'Tidak hadir',
                'waktu_masuk' => $waktuMasuk,
                'waktu_pulang' => $waktuPulang,
                'keterangan' => $this->eventAttendanceDescription($event, $absen),
                'pelanggaran' => $pelanggaranBySiswa->get($siswa->id, collect())->values(),
                'penghargaan' => $penghargaanBySiswa->get($siswa->id, collect())->values(),
            ];
        });
    }

    private function eventParticipants(Event $event): Collection
    {
        $event->loadMissing(['kelas', 'siswa.kelas', 'absenEvent.siswa.kelas']);

        $query = Siswa::with('kelas')
            ->where(function ($q) {
                $q->where('status_aktif', true)
                    ->orWhereNull('status_aktif');
            });

        if (! $event->berlaku_untuk_semua) {
            if ($event->mode_peserta === 'kelas') {
                $query->whereIn('kelas_id', $event->kelas->pluck('id')->all());
            } else {
                $query->whereIn('id', $event->siswa->pluck('id')->all());
            }
        }

        $participants = $query->get();
        $studentsWithAttendance = $event->absenEvent
            ->pluck('siswa')
            ->filter()
            ->unique('id');

        return $participants
            ->merge($studentsWithAttendance)
            ->unique('id')
            ->sortBy(fn (Siswa $siswa) => ($siswa->kelas->nama_kelas ?? 'ZZZ') . '|' . ($siswa->nama_lengkap ?? ''))
            ->values();
    }

    private function eventAttendanceDescription(Event $event, $absen): string
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

    private function eventPelanggaranDeviceId(Event $event): string
    {
        return 'auto-event-' . $event->id;
    }

    private function eventPenghargaanDeviceId(Event $event): string
    {
        return 'auto-event-penghargaan-' . $event->id;
    }

    /**
     * Cek apakah user boleh mengakses event tertentu.
     *
     * Aturan:
     *   - Siswa: boleh jika event berlaku untuknya (cek via appliesToSiswa)
     *   - canViewAllEvents: selalu boleh
     *   - GTK/wali_kelas: hanya jika dia yang membuat (created_by)
     *
     * Melempar abort(403) jika tidak boleh.
     */
    protected function authorizeEventAccess(Event $event): void
    {
        $user = auth()->user();

        if ($user->hasRole('siswa')) {
            $siswa = $user->siswa;
            if (!$siswa || !$event->appliesToSiswa($siswa->id)) {
                abort(403, 'Kamu tidak terdaftar sebagai peserta event ini.');
            }
            return;
        }

        if ($this->canViewAllEvents()) {
            return; // akses penuh
        }

        // GTK / wali_kelas — hanya boleh akses event buatannya sendiri
        if ($event->created_by !== $user->id) {
            abort(403, 'Kamu tidak memiliki akses ke event ini.');
        }
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

    private function eventPayload(array $validated): array
    {
        return Arr::except($validated, [
            'kelas_id',
            'siswa_id',
            'event_category_name',
            'recurrence_type',
            'recurrence_interval',
            'recurrence_days',
            'recurrence_until',
            'recurrence_count',
            'auto_point_pelanggaran',
            'pasal_pelanggaran_id',
            'poin_pelanggaran_event',
            'auto_penghargaan',
            'pasal_penghargaan_id',
            'poin_penghargaan_event',
            'is_ekstrakurikuler',
            'pelatih_1',
            'pelatih_2',
            'pelatih_3',
            'pembina_nama',
            'pembina_nip',
            'foto_kegiatan',
            'hapus_foto',
        ]);
    }

    /**
     * Sync foto kegiatan: hapus yang dipilih & upload yang baru.
     * Batasi total foto per event maksimal 10.
     */
    private function syncEventPhotos(Event $event, Request $request, array $hapusFotoIds): void
    {
        // 1. Hapus foto yang diminta
        if (!empty($hapusFotoIds)) {
            $fotosToDelete = EventPhoto::where('event_id', $event->id)
                ->whereIn('id', $hapusFotoIds)
                ->get();
            foreach ($fotosToDelete as $foto) {
                Storage::delete($foto->path);
                $foto->delete();
            }
        }

        // 2. Upload foto baru (jika ada)
        $files = $request->file('foto_kegiatan', []);
        if (empty($files)) {
            return;
        }

        $existingCount = EventPhoto::where('event_id', $event->id)->count();
        $maxUpload     = max(0, 10 - $existingCount);

        foreach (array_slice($files, 0, $maxUpload) as $i => $file) {
            $path = $file->store("event_photos/{$event->id}", 'public');
            EventPhoto::create([
                'event_id'      => $event->id,
                'path'          => $path,
                'original_name' => $file->getClientOriginalName(),
                'urutan'        => $existingCount + $i,
                'uploaded_by'   => auth()->id(),
            ]);
        }
    }
}
