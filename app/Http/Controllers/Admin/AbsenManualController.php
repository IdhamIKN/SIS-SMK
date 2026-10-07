<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbsenSiswa;
use App\Models\Kelas;
use App\Models\PengajuanIzin;
use App\Models\Siswa;
use App\Services\AbsenAdminService;
use App\Services\AttendanceSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * AbsenManualController
 *
 * Menangani 3 fitur admin:
 *  1. Buat Izin oleh Admin (langsung disetujui + sync absensi)
 *  2. Tambah Absensi Manual (rule lengkap: status, terlambat, dst.)
 *  3. Edit Absensi Manual  (recalculate semua rule)
 */
class AbsenManualController extends Controller
{
    public function __construct(
        protected AbsenAdminService    $absenAdminService,
        protected AttendanceSyncService $attendanceSync,
    ) {}

    // ══════════════════════════════════════════════════════════════════════
    //  INDEX — Halaman Manajemen Absensi Manual
    // ══════════════════════════════════════════════════════════════════════

    public function index(Request $request): View
    {
        $hasFilter = $request->hasAny(['tanggal_mulai', 'tanggal_selesai', 'kelas_id', 'status', 'search']);

        $tanggalMulai   = $request->get('tanggal_mulai', now()->toDateString());
        $tanggalSelesai = $request->get('tanggal_selesai', now()->toDateString());
        $kelasId        = $request->get('kelas_id');
        $status         = $request->get('status');
        $search         = trim($request->get('search', ''));

        $rekapPage = collect();
        $paginator = null;
        $stats     = [
            'total' => 0, 'hadir' => 0, 'terlambat' => 0,
            'izin'  => 0, 'sakit' => 0, 'alfa'       => 0,
        ];
        $kelas    = collect();
        $errorMsg = null;

        try {
            $absenQuery = AbsenSiswa::with([
                'siswa:id,nis,nama_lengkap,kelas_id',
                'siswa.kelas:id,nama_kelas,jurusan_id',
                'siswa.kelas.jurusan:id,nama_jurusan',
            ])
                ->whereHas('siswa')
                ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai]);

            if ($kelasId) {
                $absenQuery->where('kelas_id', $kelasId);
            }

            if ($search !== '') {
                $absenQuery->whereHas('siswa', fn ($q) => $q
                    ->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%"));
            }

            if ($status) {
                $absenQuery->where(function ($q) use ($status) {
                    $q->where('status_masuk', $status)
                        ->orWhere(fn ($q2) => $q2->whereNull('status_masuk')->where('status', $status));
                });
            }

            // Stats
            $statsRaw = (clone $absenQuery)
                ->selectRaw("
                    COUNT(*) AS total,
                    SUM(CASE WHEN COALESCE(status_masuk,status) = 'hadir'     THEN 1 ELSE 0 END) AS hadir,
                    SUM(CASE WHEN COALESCE(status_masuk,status) = 'terlambat' THEN 1 ELSE 0 END) AS terlambat,
                    SUM(CASE WHEN COALESCE(status_masuk,status) = 'izin'      THEN 1 ELSE 0 END) AS izin,
                    SUM(CASE WHEN COALESCE(status_masuk,status) = 'sakit'     THEN 1 ELSE 0 END) AS sakit,
                    SUM(CASE WHEN COALESCE(status_masuk,status) = 'alfa'      THEN 1 ELSE 0 END) AS alfa
                ")
                ->first();

            $stats = [
                'total'     => (int) ($statsRaw->total     ?? 0),
                'hadir'     => (int) ($statsRaw->hadir     ?? 0),
                'terlambat' => (int) ($statsRaw->terlambat ?? 0),
                'izin'      => (int) ($statsRaw->izin      ?? 0),
                'sakit'     => (int) ($statsRaw->sakit     ?? 0),
                'alfa'      => (int) ($statsRaw->alfa      ?? 0),
            ];

            $absenList = $absenQuery
                ->orderBy('tanggal', 'desc')
                ->orderBy('kelas_id')
                ->limit(2000)
                ->get();

            $rekapAll = $this->buatDataRekap($absenList);

            $page      = max(1, (int) $request->get('page', 1));
            $perPage   = 25;
            $total     = $rekapAll->count();
            $rekapPage = $rekapAll->slice(($page - 1) * $perPage, $perPage)->values();

            $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
                $rekapPage, $total, $perPage, $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            $kelas = Kelas::with('jurusan:id,nama_jurusan')
                ->orderBy('nama_kelas')
                ->get(['id', 'nama_kelas', 'jurusan_id']);

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::channel('sis')->error('[AbsenManual.index] FATAL', [
                'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine(),
            ]);
            $errorMsg = 'Terjadi kesalahan saat memuat data. Silakan coba lagi.';
        }

        return view('admin.absen-manual.index', compact(
            'rekapPage', 'paginator', 'stats', 'kelas',
            'tanggalMulai', 'tanggalSelesai', 'kelasId', 'status',
            'search', 'hasFilter', 'errorMsg'
        ));
    }

    // ── Build rekap data (mirror RekapController) ──────────────────────────

    private function buatDataRekap(\Illuminate\Support\Collection $absenSiswa): \Illuminate\Support\Collection
    {
        $rekap      = collect();
        $sekolah    = \App\Models\Sekolah::aktif();
        $batasTepat = null;
        if ($sekolah?->batas_tepat_waktu) {
            $ts = strtotime((string) $sekolah->batas_tepat_waktu);
            if ($ts !== false) {
                $batasTepat = Carbon::today()->setTimeFromTimeString(date('H:i:s', $ts));
            }
        }

        foreach ($absenSiswa as $absen) {
            $statusMasuk  = $absen->status_masuk ?? $absen->status ?? 'alfa';
            $statusPulang = $absen->status_pulang;

            $jamMasuk = null;
            if (! empty($absen->jam_masuk)) {
                $jamMasuk = Carbon::parse($absen->tanggal->format('Y-m-d') . ' ' . $absen->jam_masuk);
            } elseif ($absen->waktu_absen) {
                $jamMasuk = Carbon::parse($absen->waktu_absen);
            }

            $jamPulang = null;
            if (! empty($absen->jam_pulang)) {
                $jamPulang = Carbon::parse($absen->tanggal->format('Y-m-d') . ' ' . $absen->jam_pulang);
            }

            $menitTerlambat = null;
            if ($jamMasuk && $batasTepat && in_array($statusMasuk, ['hadir', 'terlambat'])) {
                $batasHari = Carbon::instance($jamMasuk)->startOfDay()
                    ->setTimeFromTimeString($batasTepat->format('H:i:s'));
                if ($jamMasuk->gt($batasHari)) {
                    $menitTerlambat = (int) floor($jamMasuk->diffInSeconds($batasHari, false) * -1 / 60);
                }
            }

            $lokasiMasuk = null;
            if ($absen->latitude_masuk && $absen->longitude_masuk) {
                $lokasiMasuk = $absen->latitude_masuk . ', ' . $absen->longitude_masuk;
            } elseif ($absen->latitude && $absen->longitude) {
                $lokasiMasuk = $absen->latitude . ', ' . $absen->longitude;
            }

            $rekap->push([
                'id'               => $absen->id,
                'tanggal'          => $absen->tanggal,
                'waktu'            => $jamMasuk ?? Carbon::parse($absen->tanggal),
                'siswa'            => $absen->siswa,
                'kelas'            => $absen->siswa?->kelas,
                'jam_masuk'        => $jamMasuk,
                'status_masuk'     => $statusMasuk,
                'jam_pulang'       => $jamPulang,
                'status_pulang'    => $statusPulang,
                'menit_terlambat'  => $menitTerlambat,
                'catatan'          => $absen->catatan,
                'lokasi_masuk'     => $lokasiMasuk,
                'latitude_masuk'   => $absen->latitude_masuk  ?? $absen->latitude,
                'longitude_masuk'  => $absen->longitude_masuk ?? $absen->longitude,
                'foto_masuk'       => $absen->foto_selfie_masuk ?? $absen->foto_selfie,
                'lokasi_pulang'    => ($absen->latitude_pulang && $absen->longitude_pulang)
                    ? $absen->latitude_pulang . ', ' . $absen->longitude_pulang : null,
                'latitude_pulang'  => $absen->latitude_pulang,
                'longitude_pulang' => $absen->longitude_pulang,
                'foto_pulang'      => $absen->foto_selfie_pulang,
                'edit_url'         => route('admin.absen-manual.edit', $absen->id),
            ]);
        }

        return $rekap->sortByDesc('waktu')->values();
    }

    // ══════════════════════════════════════════════════════════════════════
    //  TAMBAH ABSENSI MANUAL
    // ══════════════════════════════════════════════════════════════════════

    public function create(Request $request): View
    {
        $kelas   = Kelas::orderBy('nama_kelas')->get(['id', 'nama_kelas']);
        $tanggal = $request->get('tanggal', now()->toDateString());

        return view('admin.absen-manual.create', compact('kelas', 'tanggal'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'siswa_id'     => 'required|exists:siswas,id',
            'tanggal'      => 'required|date',
            'jam_masuk'    => 'nullable|date_format:H:i',
            'jam_pulang'   => 'nullable|date_format:H:i',
            'status_masuk' => ['nullable', Rule::in(['hadir', 'terlambat', 'sakit', 'izin', 'alfa'])],
            'catatan'      => 'nullable|string|max:500',
        ], [
            'jam_masuk.date_format'  => 'Format jam masuk tidak valid (HH:MM)',
            'jam_pulang.date_format' => 'Format jam pulang tidak valid (HH:MM)',
        ]);

        // Validasi: jam pulang harus setelah jam masuk
        if (! empty($validated['jam_masuk']) && ! empty($validated['jam_pulang'])) {
            $m = Carbon::parse($validated['tanggal'] . ' ' . $validated['jam_masuk']);
            $p = Carbon::parse($validated['tanggal'] . ' ' . $validated['jam_pulang']);
            if ($p->lte($m)) {
                return back()->withInput()->withErrors(['jam_pulang' => 'Jam pulang harus setelah jam masuk.']);
            }
        }

        $absen = $this->absenAdminService->tambahManual($validated, auth()->id());

        return redirect()
            ->route('admin.absen-manual.index', ['tanggal' => $validated['tanggal']])
            ->with('success', "Absensi manual berhasil disimpan untuk {$absen->siswa->nama_lengkap} ({$validated['tanggal']}).");
    }

    // ══════════════════════════════════════════════════════════════════════
    //  EDIT ABSENSI MANUAL
    // ══════════════════════════════════════════════════════════════════════

    public function edit(AbsenSiswa $absen): View
    {
        $absen->load('siswa.kelas');

        // Cek apakah sudah ada izin aktif pada tanggal ini
        $izinAktif = PengajuanIzin::where('siswa_id', $absen->siswa_id)
            ->where('status', '!=', 'ditolak')
            ->where(function ($q) use ($absen) {
                $q->whereDate('tanggal_mulai', '<=', $absen->tanggal)
                  ->whereDate('tanggal_sampai', '>=', $absen->tanggal);
            })
            ->first();

        return view('admin.absen-manual.edit', compact('absen', 'izinAktif'));
    }

    public function update(Request $request, AbsenSiswa $absen): RedirectResponse
    {
        $validated = $request->validate([
            'jam_masuk'          => 'nullable|date_format:H:i',
            'jam_pulang'         => 'nullable|date_format:H:i',
            'status_masuk'       => ['nullable', Rule::in(['hadir', 'terlambat', 'sakit', 'izin', 'alfa', 'pkl'])],
            'catatan'            => 'nullable|string|max:500',
            'hapus_poin_alfa'    => 'nullable|in:0,1',
            // Field izin — wajib jika status_masuk = izin/sakit/pkl
            'izin_jenis'         => ['nullable', Rule::in(['izin_sakit', 'izin_terlambat', 'izin_pulang_cepat', 'izin_lainnya', 'pkl'])],
            'izin_tanggal_mulai' => 'nullable|date',
            'izin_tanggal_sampai'=> 'nullable|date|after_or_equal:' . $absen->tanggal->format('Y-m-d'),
            'izin_alasan'        => 'nullable|string|max:500',
            'izin_bukti'         => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120',
        ], [
            'jam_masuk.date_format'           => 'Format jam masuk tidak valid (HH:MM)',
            'jam_pulang.date_format'          => 'Format jam pulang tidak valid (HH:MM)',
            'izin_tanggal_sampai.after_or_equal' => 'Tanggal sampai tidak boleh sebelum tanggal absensi.',
            'izin_bukti.mimes'                => 'File bukti harus berformat JPG, PNG, atau PDF.',
            'izin_bukti.max'                  => 'Ukuran file bukti maksimal 5 MB.',
        ]);

        // Validasi tambahan: jika status izin/sakit/pkl, jenis harus diisi
        $statusBaru = $validated['status_masuk'] ?? null;
        if (in_array($statusBaru, ['izin', 'sakit', 'pkl'], true) && empty($validated['izin_jenis'])) {
            return back()->withInput()->withErrors([
                'izin_jenis' => 'Jenis izin wajib dipilih jika status diubah menjadi Izin, Sakit, atau PKL.',
            ]);
        }

        // Validasi: jam pulang harus setelah jam masuk
        $jamMasuk  = $validated['jam_masuk']  ?? ($absen->jam_masuk  ? substr($absen->jam_masuk, 0, 5)  : null);
        $jamPulang = $validated['jam_pulang'] ?? ($absen->jam_pulang ? substr($absen->jam_pulang, 0, 5) : null);

        if ($jamMasuk && $jamPulang) {
            $tgl = $absen->tanggal->format('Y-m-d');
            $m   = Carbon::parse($tgl . ' ' . $jamMasuk);
            $p   = Carbon::parse($tgl . ' ' . $jamPulang);
            if ($p->lte($m)) {
                return back()->withInput()->withErrors(['jam_pulang' => 'Jam pulang harus setelah jam masuk.']);
            }
        }

        // Upload bukti jika ada
        if ($request->hasFile('izin_bukti')) {
            $file = $request->file('izin_bukti');
            $dir  = 'izin/' . date('Y/m');
            \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory($dir);
            $filename = time() . '_' . $absen->siswa_id . '_' . \Illuminate\Support\Str::random(6)
                . '.' . $file->getClientOriginalExtension();
            $validated['izin_bukti'] = $file->storeAs($dir, $filename, 'public');
        } else {
            unset($validated['izin_bukti']);
        }

        $absen = $this->absenAdminService->editManual($absen, $validated, auth()->id());

        return redirect()
            ->route('admin.absen-manual.index', ['tanggal' => $absen->tanggal->format('Y-m-d')])
            ->with('success', "Absensi {$absen->siswa->nama_lengkap} berhasil diperbarui.");
    }

    // ══════════════════════════════════════════════════════════════════════
    //  BUAT IZIN OLEH ADMIN
    // ══════════════════════════════════════════════════════════════════════

    public function createIzin(Request $request): View
    {
        $kelas   = Kelas::orderBy('nama_kelas')->get(['id', 'nama_kelas']);
        $tanggal = $request->get('tanggal', now()->toDateString());

        return view('admin.absen-manual.create-izin', compact('kelas', 'tanggal'));
    }

    public function storeIzin(Request $request): RedirectResponse
    {
        // ── Mode massal dikirim via JSON fetch (bukan form submit biasa) ──
        // storeIzin hanya menangani TUNGGAL (is_massal = 0 atau tidak ada)
        // Mode massal ditangani oleh bulkIzin() via AJAX

        $validated = $request->validate([
            'siswa_id'       => 'required|exists:siswas,id',
            'jenis'          => ['required', Rule::in(['izin_sakit', 'izin_terlambat', 'izin_pulang_cepat', 'izin_lainnya', 'pkl'])],
            'tanggal_mulai'  => 'required|date',
            'tanggal_sampai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan'         => 'required|string|max:500',
            'bukti'          => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120',
        ], [
            'tanggal_sampai.after_or_equal' => 'Tanggal sampai tidak boleh sebelum tanggal mulai.',
            'bukti.mimes'                   => 'File bukti harus berformat JPG, PNG, atau PDF.',
            'bukti.max'                     => 'Ukuran file bukti maksimal 5 MB.',
        ]);

        $siswa = Siswa::findOrFail($validated['siswa_id']);

        // Cek overlap izin
        $overlap = PengajuanIzin::where('siswa_id', $siswa->id)
            ->where('status', '!=', 'ditolak')
            ->where(function ($q) use ($validated) {
                $q->whereBetween('tanggal_mulai', [$validated['tanggal_mulai'], $validated['tanggal_sampai']])
                  ->orWhereBetween('tanggal_sampai', [$validated['tanggal_mulai'], $validated['tanggal_sampai']])
                  ->orWhere(function ($q2) use ($validated) {
                      $q2->where('tanggal_mulai', '<=', $validated['tanggal_mulai'])
                         ->where('tanggal_sampai', '>=', $validated['tanggal_sampai']);
                  });
            })
            ->first();

        if ($overlap) {
            return back()->withInput()->withErrors([
                'tanggal_mulai' => "Sudah ada izin ({$overlap->jenis_label}) pada tanggal tersebut dengan status {$overlap->status_label}.",
            ]);
        }

        // Simpan file bukti jika ada
        $buktiPath = null;
        if ($request->hasFile('bukti')) {
            $file      = $request->file('bukti');
            $dir       = 'izin/' . date('Y/m');
            \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory($dir);
            $filename  = time() . '_' . $siswa->id . '_' . \Illuminate\Support\Str::random(6)
                       . '.' . $file->getClientOriginalExtension();
            $buktiPath = $file->storeAs($dir, $filename, 'public');
        }

        $izin = $this->absenAdminService->buatIzinAdmin(
            array_merge($validated, ['bukti' => $buktiPath]),
            auth()->id()
        );

        return redirect()
            ->route('admin.absen-manual.index', ['tanggal' => $validated['tanggal_mulai']])
            ->with('success', "Izin {$izin->jenis_label} untuk {$siswa->nama_lengkap} berhasil dibuat dan langsung disetujui.");
    }

    // ══════════════════════════════════════════════════════════════════════
    //  API: Cari Siswa (AJAX autocomplete)
    // ══════════════════════════════════════════════════════════════════════

    public function searchSiswa(Request $request)
    {
        $search  = $request->get('q', '');
        $kelasId = $request->get('kelas_id');

        $query = Siswa::with('kelas')
            ->where('status_aktif', true)
            ->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            })
            ->limit(100);

        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        }

        return response()->json(
            $query->get()->map(fn ($s) => [
                'id'    => $s->id,
                'text'  => $s->nama_lengkap . ' (' . $s->nis . ')',
                'nis'   => $s->nis,
                'kelas' => $s->kelas?->nama_kelas ?? '-',
            ])
        );
    }

    // ══════════════════════════════════════════════════════════════════════
    //  API: Cek status absen siswa pada tanggal (AJAX)
    // ══════════════════════════════════════════════════════════════════════

    public function statusSiswa(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'tanggal'  => 'required|date',
        ]);

        $absen = AbsenSiswa::where('siswa_id', $request->siswa_id)
            ->whereDate('tanggal', $request->tanggal)
            ->first();

        $izin = PengajuanIzin::where('siswa_id', $request->siswa_id)
            ->where('status', 'disetujui')
            ->whereDate('tanggal_mulai', '<=', $request->tanggal)
            ->whereDate('tanggal_sampai', '>=', $request->tanggal)
            ->first();

        return response()->json([
            'ada_record' => $absen !== null,
            'jam_masuk'  => $absen ? ($absen->jam_masuk ? substr($absen->jam_masuk, 0, 5) : null) : null,
            'jam_pulang' => $absen ? ($absen->jam_pulang ? substr($absen->jam_pulang, 0, 5) : null) : null,
            'status_masuk'  => $absen ? ($absen->status_masuk ?? $absen->status) : null,
            'status_pulang' => $absen?->status_pulang,
            'catatan'    => $absen?->catatan,
            'ada_izin'   => $izin !== null,
            'jenis_izin' => $izin?->jenis_label,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════
    //  BULK IZIN — Buat izin untuk beberapa siswa sekaligus
    // ══════════════════════════════════════════════════════════════════════

    public function bulkIzin(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'siswa_ids'      => 'required|array|min:1|max:100',
            'siswa_ids.*'    => 'required|exists:siswas,id',
            'jenis'          => ['required', Rule::in(['izin_sakit', 'izin_terlambat', 'izin_pulang_cepat', 'izin_lainnya', 'pkl'])],
            'tanggal_mulai'  => 'required|date',
            'tanggal_sampai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan'         => 'required|string|max:500',
            'hapus_poin'     => 'nullable|boolean',
        ], [
            'siswa_ids.required'       => 'Pilih minimal satu siswa.',
            'siswa_ids.min'            => 'Pilih minimal satu siswa.',
            'tanggal_sampai.after_or_equal' => 'Tanggal sampai tidak boleh sebelum tanggal mulai.',
        ]);

        $adminId       = auth()->id();
        $berhasil      = 0;
        $gagal         = 0;
        $skipped       = 0;
        $errors        = [];
        $poinDihapus   = 0;

        foreach ($validated['siswa_ids'] as $siswaId) {
            try {
                $siswa = Siswa::findOrFail($siswaId);

                // Cek overlap izin aktif
                $overlap = PengajuanIzin::where('siswa_id', $siswaId)
                    ->where('status', '!=', 'ditolak')
                    ->where(function ($q) use ($validated) {
                        $q->whereBetween('tanggal_mulai', [$validated['tanggal_mulai'], $validated['tanggal_sampai']])
                          ->orWhereBetween('tanggal_sampai', [$validated['tanggal_mulai'], $validated['tanggal_sampai']])
                          ->orWhere(function ($q2) use ($validated) {
                              $q2->where('tanggal_mulai', '<=', $validated['tanggal_mulai'])
                                 ->where('tanggal_sampai', '>=', $validated['tanggal_sampai']);
                          });
                    })
                    ->first();

                if ($overlap) {
                    $skipped++;
                    $errors[] = "{$siswa->nama_lengkap}: sudah ada izin ({$overlap->jenis_label}) pada tanggal tersebut.";
                    continue;
                }

                // Buat izin
                $this->absenAdminService->buatIzinAdmin([
                    'siswa_id'       => $siswaId,
                    'jenis'          => $validated['jenis'],
                    'tanggal_mulai'  => $validated['tanggal_mulai'],
                    'tanggal_sampai' => $validated['tanggal_sampai'],
                    'alasan'         => $validated['alasan'],
                    'bukti'          => null,
                ], $adminId);

                // Hapus poin alfa range jika diminta
                if (! empty($validated['hapus_poin'])) {
                    $mulai  = Carbon::parse($validated['tanggal_mulai']);
                    $sampai = Carbon::parse($validated['tanggal_sampai']);
                    $deviceIds = [];
                    $cur = $mulai->copy();
                    while ($cur->lte($sampai)) {
                        $deviceIds[] = 'auto-alfa-' . $cur->toDateString();
                        $cur->addDay();
                    }
                    $poinDihapus += \App\Models\Pelanggaran::where('siswa_id', $siswaId)
                        ->whereIn('deviceid', $deviceIds)
                        ->delete();
                }

                $berhasil++;
            } catch (\Throwable $e) {
                $gagal++;
                Log::channel('sis')->error('[BulkIzin] Gagal siswa #' . $siswaId . ': ' . $e->getMessage());
            }
        }

        Log::channel('sis')->info('[AbsenManual] Bulk izin selesai', [
            'admin_id'     => $adminId,
            'jenis'        => $validated['jenis'],
            'tanggal_mulai'=> $validated['tanggal_mulai'],
            'tanggal_sampai'=> $validated['tanggal_sampai'],
            'berhasil'     => $berhasil,
            'skipped'      => $skipped,
            'gagal'        => $gagal,
            'poin_dihapus' => $poinDihapus,
        ]);

        return response()->json([
            'success'      => $berhasil > 0,
            'berhasil'     => $berhasil,
            'skipped'      => $skipped,
            'gagal'        => $gagal,
            'poin_dihapus' => $poinDihapus,
            'errors'       => $errors,
        ]);
    }
    // ══════════════════════════════════════════════════════════════════════

    public function cekPoinAlfa(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'tanggal'  => 'required|date',
        ]);

        $tanggal  = Carbon::parse($request->tanggal)->toDateString();
        $deviceId = 'auto-alfa-' . $tanggal;

        $poin = \App\Models\Pelanggaran::where('siswa_id', $request->siswa_id)
            ->where('deviceid', $deviceId)
            ->first();

        return response()->json([
            'ada_poin'  => $poin !== null,
            'poin_id'   => $poin?->idpel,
            'poin_nilai' => $poin?->poin,
            'pasal'     => $poin?->isi,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════
    //  API: Cek poin alfa otomatis pada RANGE tanggal (AJAX)
    // ══════════════════════════════════════════════════════════════════════

    public function cekPoinAlfaRange(Request $request)
    {
        $request->validate([
            'siswa_id'       => 'required|exists:siswas,id',
            'tanggal_mulai'  => 'required|date',
            'tanggal_sampai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $mulai  = Carbon::parse($request->tanggal_mulai)->toDateString();
        $sampai = Carbon::parse($request->tanggal_sampai)->toDateString();

        // Generate deviceid pattern untuk tiap hari dalam range
        $current  = Carbon::parse($mulai);
        $end      = Carbon::parse($sampai);
        $deviceIds = [];
        while ($current->lte($end)) {
            $deviceIds[] = 'auto-alfa-' . $current->toDateString();
            $current->addDay();
        }

        $count = \App\Models\Pelanggaran::where('siswa_id', $request->siswa_id)
            ->whereIn('deviceid', $deviceIds)
            ->count();

        return response()->json([
            'ada_poin'   => $count > 0,
            'jumlah'     => $count,
            'tanggal_mulai'  => $mulai,
            'tanggal_sampai' => $sampai,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════
    //  API: Hapus poin alfa otomatis (AJAX — dipanggil setelah konfirmasi)
    // ══════════════════════════════════════════════════════════════════════

    public function hapusPoinAlfa(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswas,id',
            'tanggal'  => 'required|date',
        ]);

        $tanggal  = Carbon::parse($request->tanggal)->toDateString();
        $deviceId = 'auto-alfa-' . $tanggal;

        // Hanya hapus poin pelanggaran alfa OTOMATIS (deviceid auto-alfa-YYYY-MM-DD)
        // Bukan poin manual atau jenis lain
        $deleted = \App\Models\Pelanggaran::where('siswa_id', $request->siswa_id)
            ->where('deviceid', $deviceId)
            ->delete();

        Log::channel('sis')->info('[AbsenManual] Hapus poin alfa otomatis', [
            'siswa_id' => $request->siswa_id,
            'tanggal'  => $tanggal,
            'deviceid' => $deviceId,
            'deleted'  => $deleted,
            'admin_id' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'deleted' => $deleted,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════
    //  API: Hapus poin alfa otomatis pada RANGE tanggal (AJAX)
    // ══════════════════════════════════════════════════════════════════════

    public function hapusPoinAlfaRange(Request $request)
    {
        $request->validate([
            'siswa_id'       => 'required|exists:siswas,id',
            'tanggal_mulai'  => 'required|date',
            'tanggal_sampai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $mulai  = Carbon::parse($request->tanggal_mulai)->toDateString();
        $sampai = Carbon::parse($request->tanggal_sampai)->toDateString();

        // Generate deviceid pattern untuk tiap hari dalam range
        $current   = Carbon::parse($mulai);
        $end       = Carbon::parse($sampai);
        $deviceIds = [];
        while ($current->lte($end)) {
            $deviceIds[] = 'auto-alfa-' . $current->toDateString();
            $current->addDay();
        }

        $deleted = \App\Models\Pelanggaran::where('siswa_id', $request->siswa_id)
            ->whereIn('deviceid', $deviceIds)
            ->delete();

        Log::channel('sis')->info('[AbsenManual] Hapus poin alfa otomatis range', [
            'siswa_id'       => $request->siswa_id,
            'tanggal_mulai'  => $mulai,
            'tanggal_sampai' => $sampai,
            'jumlah_device'  => count($deviceIds),
            'deleted'        => $deleted,
            'admin_id'       => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'deleted' => $deleted,
        ]);
    }
}
