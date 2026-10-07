<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Http\Requests\SiswaStoreRequest;
use App\Http\Requests\SiswaUpdateRequest;
use App\Models\AbsenEvent as AbsenEventModel;
use App\Models\AbsenSiswa;
use App\Models\Kelas;
use App\Models\Pelanggaran;
use App\Models\Penghargaan;
use App\Models\Siswa;
use App\Models\SuratPanggilan;
use App\Models\User;
use App\Services\TatibPoinService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SiswaController extends Controller
{
    public function index(Request $request): View
    {
        Log::channel('sis')->info('[Siswa] Index access', [
            'user_id' => $request->user()->id,
            'status_data' => $request->get('status_data', 'aktif'),
        ]);

        $statusData = $request->get('status_data', 'aktif');
        $statusData = in_array($statusData, ['aktif', 'arsip'], true) ? $statusData : 'aktif';

        $query = Siswa::query()
            ->with(['kelas', 'user'])
            ->when($statusData === 'arsip', fn(Builder $q) => $q->onlyTrashed());

        $this->applyIndexFilters($query, $request);

        $siswas = $query
            ->orderBy('nama_lengkap')
            ->paginate(20)
            ->withQueryString();

        $kelas = Kelas::orderBy('nama_kelas')->get();
        $aktifCount = Siswa::where('status_aktif', true)->count();
        $nonAktifCount = Siswa::where('status_aktif', false)->count();
        $arsipCount = Siswa::onlyTrashed()->count();

        return view('siswa.index', compact(
            'siswas',
            'kelas',
            'statusData',
            'aktifCount',
            'nonAktifCount',
            'arsipCount'
        ));
    }

    public function create(): View
    {
        $kelas = Kelas::orderBy('nama_kelas')->get();

        return view('siswa.create', compact('kelas'));
    }

    public function store(SiswaStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Log::channel('sis')->info('[Siswa] Create new siswa', [
            'nisn' => $validated['nisn'],
            'nama' => $validated['nama_lengkap'],
            'user_id' => $request->user()->id,
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('siswa', 'public');
        }

        $siswa = Siswa::create($validated);

        // Create user account for login
        $user = User::create([
            'name' => $validated['nama_lengkap'],
            'email' => $validated['nisn'] . '@smkn5madiun.sch.id',
            'password' => $siswa->getHashedDefaultPassword(), // Default password menggunakan NISN
            'phone' => $validated['no_hp_siswa'] ?? null,
            'role_utama' => 'siswa',
            'siswa_id' => $siswa->id,
        ]);

        // Assign role
        $user->assignRole('siswa');

        // Update siswa with user_id
        $siswa->update(['user_id' => $user->id]);

        return redirect()->route('siswa.index')->with('success', 'Siswa berhasil ditambahkan');
    }

    public function show(Siswa $siswa): View
    {
        $siswa->load(['kelas', 'user']);

        $absenPagination = $siswa->absenSiswa()
            ->latest()
            ->paginate(10, ['*'], 'absen_page');

        // Tahun ajaran aktif
        $tahunAjaranAktif = tahun_ajaran_aktif();

        // Tab aktif
        $activeTab = request('tab', 'pelanggaran');

        // ── Semua data riwayat dari tbltransaksi (sumber kebenaran tunggal) ──
        // Pelanggaran = poinp > 0 | Penghargaan = poinr > 0
        $tatibPoin = app(\App\Services\TatibPoinService::class);

        $transaksiBase = \App\Models\TransaksiPoin::query()
            ->forSiswa($siswa);

        // ── Riwayat Pelanggaran — dari tabel tblpelanggaran (untuk aksi hapus/edit) ──
        $pelanggaranRawBaseQ = Pelanggaran::with('subPasal')
            ->where('siswa_id', $siswa->id)
            ->orderByDesc('tgl')
            ->orderByDesc('idpel');

        $pelanggaranRawTahunIni = (clone $pelanggaranRawBaseQ)
            ->where('tahun_ajaran', $tahunAjaranAktif)
            ->paginate(12, ['*'], 'pel_page')
            ->withQueryString();

        $pelanggaranRawSemua = (clone $pelanggaranRawBaseQ)
            ->paginate(12, ['*'], 'pel_all_page')
            ->withQueryString();

        // ── Riwayat Penghargaan — dari tabel tblpenghargaan (untuk aksi hapus/ACC) ──
        $penghargaanRawBaseQ = Penghargaan::with('subPasal')
            ->where('siswa_id', $siswa->id)
            ->orderByDesc('tgl')
            ->orderByDesc('idpen');

        $penghargaanRawTahunIni = (clone $penghargaanRawBaseQ)
            ->where('tahun_ajaran', $tahunAjaranAktif)
            ->paginate(12, ['*'], 'pen_page')
            ->withQueryString();

        $penghargaanRawSemua = (clone $penghargaanRawBaseQ)
            ->paginate(12, ['*'], 'pen_all_page')
            ->withQueryString();

        // ── Surat Panggilan (tetap dari tabel asli) ──
        $suratPanggilanQuery = $siswa->suratPanggilan()->orderByDesc('tanggal_surat');
        $suratPanggilanTahunIni = (clone $suratPanggilanQuery)
            ->where('tahun_ajaran', $tahunAjaranAktif)
            ->paginate(15, ['*'], 'surat_page')
            ->withQueryString();
        $suratPanggilanSemua = $suratPanggilanQuery
            ->paginate(15, ['*'], 'surat_all_page')
            ->withQueryString();

        // ── Total poin — semua dari tbltransaksi ──
        $totalPoinPelanggaranTahunIni = $tatibPoin->totalPelanggaran($siswa, $tahunAjaranAktif);
        $totalPoinPenghargaanTahunIni = $tatibPoin->totalPenghargaan($siswa, $tahunAjaranAktif);

        // Total semua tahun — juga dari tbltransaksi
        $totalPoinPelanggaranSemua = (int) (clone $transaksiBase)->sum('poinp');
        $totalPoinPenghargaanSemua = (int) (clone $transaksiBase)->sum('poinr');

        return view('siswa.show', compact(
            'siswa',
            'absenPagination',
            'tahunAjaranAktif',
            'activeTab',
            'pelanggaranRawSemua',
            'pelanggaranRawTahunIni',
            'penghargaanRawSemua',
            'penghargaanRawTahunIni',
            'suratPanggilanSemua',
            'suratPanggilanTahunIni',
            'totalPoinPelanggaranSemua',
            'totalPoinPelanggaranTahunIni',
            'totalPoinPenghargaanSemua',
            'totalPoinPenghargaanTahunIni',
        ));
    }

    /* ══════════════════════════════════════════════════
     * AJAX: Absen Harian — data paginasi
     * ══════════════════════════════════════════════════ */
    public function absenHarian(Request $request, Siswa $siswa): JsonResponse
    {
        $perPage = 12;
        $paginator = $siswa->absenSiswa()
            ->orderByDesc('tanggal')
            ->paginate($perPage, ['*'], 'absen_page');

        $items = $paginator->getCollection()->map(function (AbsenSiswa $a) use ($siswa) {
            $status = $a->status_masuk ?? $a->status ?? 'alfa';
            $jamM   = $a->jam_masuk  ? substr((string) $a->jam_masuk, 0, 5)  : null;
            $jamP   = $a->jam_pulang ? substr((string) $a->jam_pulang, 0, 5) : null;
            return [
                'id'          => $a->id,
                'tanggal'     => $a->tanggal->translatedFormat('d F Y'),
                'tanggal_raw' => $a->tanggal->format('Y-m-d'),
                'jam_masuk'   => $jamM,
                'jam_pulang'  => $jamP,
                'status'      => $status,
                'catatan'     => $a->catatan,
                'edit_url'    => route('siswa.absen-harian.update', [$siswa, $a]),
            ];
        });

        return response()->json([
            'data'          => $items,
            'current_page'  => $paginator->currentPage(),
            'last_page'     => $paginator->lastPage(),
            'total'         => $paginator->total(),
        ]);
    }

    /* ══════════════════════════════════════════════════
     * AJAX: Update Absen Harian
     * ══════════════════════════════════════════════════ */
    public function updateAbsenHarian(Request $request, Siswa $siswa, AbsenSiswa $absen): JsonResponse
    {
        if ($absen->siswa_id !== $siswa->id) {
            return response()->json(['error' => 'Data tidak ditemukan.'], 404);
        }

        $validated = $request->validate([
            'status_masuk' => ['required', Rule::in(['hadir', 'terlambat', 'sakit', 'izin', 'alfa'])],
            'jam_masuk'    => 'nullable|date_format:H:i',
            'jam_pulang'   => 'nullable|date_format:H:i',
            'catatan'      => 'nullable|string|max:500',
        ]);

        $absen->update([
            'status_masuk' => $validated['status_masuk'],
            'status'       => $validated['status_masuk'], // legacy sync
            'jam_masuk'    => $validated['jam_masuk']  ?? $absen->jam_masuk,
            'jam_pulang'   => $validated['jam_pulang'] ?? $absen->jam_pulang,
            'catatan'      => $validated['catatan'] ?? $absen->catatan,
        ]);

        $absen->refresh();
        $status = $absen->status_masuk ?? $absen->status ?? 'alfa';

        return response()->json([
            'success' => true,
            'row' => [
                'id'          => $absen->id,
                'tanggal'     => $absen->tanggal->translatedFormat('d F Y'),
                'tanggal_raw' => $absen->tanggal->format('Y-m-d'),
                'jam_masuk'   => $absen->jam_masuk  ? substr((string) $absen->jam_masuk, 0, 5)  : null,
                'jam_pulang'  => $absen->jam_pulang ? substr((string) $absen->jam_pulang, 0, 5) : null,
                'status'      => $status,
                'catatan'     => $absen->catatan,
                'edit_url'    => route('siswa.absen-harian.update', [$siswa, $absen]),
            ],
        ]);
    }

    /* ══════════════════════════════════════════════════
     * AJAX: Absen Event — data paginasi
     * ══════════════════════════════════════════════════ */
    public function absenEvent(Request $request, Siswa $siswa): JsonResponse
    {
        $perPage   = 12;
        $page      = max(1, (int) $request->get('page', 1));

        $paginator = AbsenEventModel::with('event')
            ->where('siswa_id', $siswa->id)
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page');

        $items = $paginator->getCollection()->map(function (AbsenEventModel $a) use ($siswa) {
            $waktu   = $a->waktu_masuk ?? $a->waktu_scan;
            $hadir   = $waktu !== null;
            $status  = $hadir ? 'Hadir' : 'Tidak Hadir';
            $tgl     = $waktu ?? $a->created_at;
            return [
                'id'          => $a->id,
                'event_id'    => $a->event_id,
                'nama_event'  => $a->event?->nama_event ?? '-',
                'tanggal'     => $tgl ? $tgl->translatedFormat('d F Y') : '-',
                'waktu_masuk' => $a->waktu_masuk?->format('H:i'),
                'waktu_pulang'=> $a->waktu_pulang?->format('H:i'),
                'status'      => $status,
                'hadir'       => $hadir,
                'catatan'     => null,
                'edit_url'    => route('siswa.absen-event.update', [$siswa, $a]),
            ];
        });

        return response()->json([
            'data'         => $items,
            'current_page' => $paginator->currentPage(),
            'last_page'    => $paginator->lastPage(),
            'total'        => $paginator->total(),
        ]);
    }

    /* ══════════════════════════════════════════════════
     * AJAX: Update Absen Event
     * ══════════════════════════════════════════════════ */
    public function updateAbsenEvent(Request $request, Siswa $siswa, AbsenEventModel $absenEvent): JsonResponse
    {
        if ($absenEvent->siswa_id !== $siswa->id) {
            return response()->json(['error' => 'Data tidak ditemukan.'], 404);
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['hadir', 'tidak_hadir'])],
        ]);

        if ($validated['status'] === 'hadir') {
            if (! $absenEvent->waktu_masuk && ! $absenEvent->waktu_scan) {
                $absenEvent->update(['waktu_masuk' => now(), 'waktu_scan' => now(), 'jenis' => 'masuk']);
            }
        } else {
            $absenEvent->update(['waktu_masuk' => null, 'waktu_scan' => null, 'waktu_pulang' => null]);
        }

        $absenEvent->refresh();
        $waktu  = $absenEvent->waktu_masuk ?? $absenEvent->waktu_scan;
        $hadir  = $waktu !== null;

        return response()->json([
            'success' => true,
            'row' => [
                'id'          => $absenEvent->id,
                'nama_event'  => $absenEvent->event?->nama_event ?? '-',
                'tanggal'     => ($waktu ?? $absenEvent->created_at)?->translatedFormat('d F Y'),
                'waktu_masuk' => $absenEvent->waktu_masuk?->format('H:i'),
                'waktu_pulang'=> $absenEvent->waktu_pulang?->format('H:i'),
                'status'      => $hadir ? 'Hadir' : 'Tidak Hadir',
                'hadir'       => $hadir,
                'catatan'     => null,
                'edit_url'    => route('siswa.absen-event.update', [$siswa, $absenEvent]),
            ],
        ]);
    }

    /* ══════════════════════════════════════════════════
     * Delete Pelanggaran
     * ══════════════════════════════════════════════════ */
    public function deletePelanggaran(Request $request, Siswa $siswa, Pelanggaran $pelanggaran): JsonResponse
    {
        if ($pelanggaran->siswa_id !== $siswa->id) {
            return response()->json(['error' => 'Data tidak ditemukan.'], 404);
        }

        $tatibPoin = app(TatibPoinService::class);
        DB::transaction(function () use ($pelanggaran, $tatibPoin) {
            $tatibPoin->deleteTransaction('PN', $pelanggaran->tgl, $pelanggaran->getKey());
            $pelanggaran->delete();
        });

        return response()->json(['success' => true]);
    }

    /* ══════════════════════════════════════════════════
     * Bulk Delete Pelanggaran
     * ══════════════════════════════════════════════════ */
    public function bulkDeletePelanggaran(Request $request, Siswa $siswa): JsonResponse
    {
        $ids = array_map('intval', (array) $request->input('ids', []));
        if (empty($ids)) {
            return response()->json(['error' => 'Tidak ada data dipilih.'], 422);
        }

        $tatibPoin = app(TatibPoinService::class);
        $deleted   = 0;

        DB::transaction(function () use ($ids, $siswa, $tatibPoin, &$deleted) {
            Pelanggaran::whereIn('idpel', $ids)
                ->where('siswa_id', $siswa->id)
                ->each(function (Pelanggaran $p) use ($tatibPoin, &$deleted) {
                    $tatibPoin->deleteTransaction('PN', $p->tgl, $p->getKey());
                    $p->delete();
                    $deleted++;
                });
        });

        return response()->json(['success' => true, 'deleted' => $deleted]);
    }

    /* ══════════════════════════════════════════════════
     * Delete Penghargaan
     * ══════════════════════════════════════════════════ */
    public function deletePenghargaan(Request $request, Siswa $siswa, Penghargaan $penghargaan): JsonResponse
    {
        if ($penghargaan->siswa_id !== $siswa->id) {
            return response()->json(['error' => 'Data tidak ditemukan.'], 404);
        }

        $tatibPoin = app(TatibPoinService::class);
        DB::transaction(function () use ($penghargaan, $tatibPoin) {
            if ($penghargaan->acc === 'YA') {
                $tatibPoin->deleteTransaction('RW', $penghargaan->tgl, $penghargaan->getKey());
            }
            $penghargaan->delete();
        });

        return response()->json(['success' => true]);
    }

    /* ══════════════════════════════════════════════════
     * Bulk Delete Penghargaan
     * ══════════════════════════════════════════════════ */
    public function bulkDeletePenghargaan(Request $request, Siswa $siswa): JsonResponse
    {
        $ids = array_map('intval', (array) $request->input('ids', []));
        if (empty($ids)) {
            return response()->json(['error' => 'Tidak ada data dipilih.'], 422);
        }

        $tatibPoin = app(TatibPoinService::class);
        $deleted   = 0;

        DB::transaction(function () use ($ids, $siswa, $tatibPoin, &$deleted) {
            Penghargaan::whereIn('idpen', $ids)
                ->where('siswa_id', $siswa->id)
                ->each(function (Penghargaan $p) use ($tatibPoin, &$deleted) {
                    if ($p->acc === 'YA') {
                        $tatibPoin->deleteTransaction('RW', $p->tgl, $p->getKey());
                    }
                    $p->delete();
                    $deleted++;
                });
        });

        return response()->json(['success' => true, 'deleted' => $deleted]);
    }

    /* ══════════════════════════════════════════════════
     * Approve Penghargaan (single)
     * ══════════════════════════════════════════════════ */
    public function approvePenghargaan(Request $request, Siswa $siswa, Penghargaan $penghargaan): JsonResponse
    {
        if ($penghargaan->siswa_id !== $siswa->id) {
            return response()->json(['error' => 'Data tidak ditemukan.'], 404);
        }
        if ($penghargaan->acc === 'YA') {
            return response()->json(['error' => 'Penghargaan sudah di-ACC.'], 422);
        }

        $tatibPoin = app(TatibPoinService::class);
        DB::transaction(function () use ($penghargaan, $siswa, $tatibPoin) {
            $penghargaan->update([
                'acc'    => 'YA',
                'tglacc' => now(),
                'nmacc'  => auth()->user()->name ?? 'admin',
            ]);
            $tatibPoin->createPenghargaanTransaction($penghargaan, $siswa);
        });

        return response()->json(['success' => true]);
    }

    /* ══════════════════════════════════════════════════
     * Bulk Approve Penghargaan
     * ══════════════════════════════════════════════════ */
    public function bulkApprovePenghargaan(Request $request, Siswa $siswa): JsonResponse
    {
        $ids = array_map('intval', (array) $request->input('ids', []));
        if (empty($ids)) {
            return response()->json(['error' => 'Tidak ada data dipilih.'], 422);
        }

        $tatibPoin = app(TatibPoinService::class);
        $approved  = 0;

        DB::transaction(function () use ($ids, $siswa, $tatibPoin, &$approved) {
            Penghargaan::whereIn('idpen', $ids)
                ->where('siswa_id', $siswa->id)
                ->where(fn ($q) => $q->where('acc', '!=', 'YA')->orWhereNull('acc')->orWhere('acc', ''))
                ->each(function (Penghargaan $p) use ($siswa, $tatibPoin, &$approved) {
                    $p->update([
                        'acc'    => 'YA',
                        'tglacc' => now(),
                        'nmacc'  => auth()->user()->name ?? 'admin',
                    ]);
                    $tatibPoin->createPenghargaanTransaction($p, $siswa);
                    $approved++;
                });
        });

        return response()->json(['success' => true, 'approved' => $approved]);
    }

    public function edit(Siswa $siswa): View
    {
        $kelas = Kelas::orderBy('nama_kelas')->get();

        return view('siswa.edit', compact('siswa', 'kelas'));
    }

    public function update(SiswaUpdateRequest $request, Siswa $siswa): RedirectResponse
    {
        $validated = $request->validated();

        Log::channel('sis')->info('[Siswa] Update siswa', [
            'siswa_id' => $siswa->id,
            'nisn' => $validated['nisn'],
            'user_id' => $request->user()->id,
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('siswa', 'public');
        }

        $siswa->update($validated);

        // Update user account
        if ($siswa->user) {
            $siswa->user->update([
                'name' => $validated['nama_lengkap'],
                'email' => $validated['nisn'] . '@smkn5madiun.sch.id',
                'phone' => $validated['no_hp_siswa'] ?? null,
            ]);
        }

        return redirect()->route('siswa.show', $siswa)->with('success', 'Siswa berhasil diupdate');
    }

    public function resetPassword(Siswa $siswa): RedirectResponse
    {
        $user = $siswa->user;

        if (! $user) {
            return redirect()->route('siswa.show', $siswa)
                ->with('error', 'User akun untuk siswa ini tidak ditemukan.');
        }

        $user->forceFill([
            'password' => \Illuminate\Support\Facades\Hash::make('12345678'),
        ])->save();

        Log::channel('sis')->info('[Siswa] Reset password siswa', [
            'siswa_id' => $siswa->id,
            'nisn'     => $siswa->nisn,
            'user_id'  => $user->id,
            'by'       => request()->user()->id,
        ]);

        return redirect()->route('siswa.show', $siswa)
            ->with('success', 'Password siswa berhasil direset ke default (12345678).');
    }

    public function destroy(Siswa $siswa): RedirectResponse
    {
        Log::channel('sis')->info('[Siswa] Archive siswa', [
            'siswa_id' => $siswa->id,
            'nisn' => $siswa->nisn,
            'user_id' => request()->user()->id,
        ]);

        $siswa->delete();

        return redirect()->route('siswa.index')->with('success', 'Siswa berhasil diarsipkan dan dapat dipulihkan dari menu Arsip.');
    }

    public function bulkArchive(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mode' => ['required', Rule::in(['selected', 'filtered'])],
            'siswa_ids' => ['required_if:mode,selected', 'array'],
            'siswa_ids.*' => ['integer'],
            'search' => ['nullable', 'string'],
            'kelas_id' => ['nullable', 'integer'],
            'status_aktif' => ['nullable', Rule::in(['0', '1'])],
        ], [
            'siswa_ids.required_if' => 'Pilih minimal satu siswa untuk diarsipkan.',
        ]);

        $query = Siswa::query();
        if ($validated['mode'] === 'selected') {
            $query->whereKey($validated['siswa_ids'] ?? []);
        } else {
            $this->applyIndexFilters($query, $request);
        }

        $siswas = $query->get();
        if ($siswas->isEmpty()) {
            return back()->with('error', 'Tidak ada siswa aktif yang cocok untuk diarsipkan.');
        }

        DB::transaction(function () use ($siswas, $request) {
            foreach ($siswas as $siswa) {
                $siswa->delete();
            }

            Log::channel('sis')->info('[Siswa] Bulk archive success', [
                'count' => $siswas->count(),
                'mode' => $request->input('mode'),
                'user_id' => $request->user()->id,
            ]);
        });

        return redirect()
            ->route('siswa.index')
            ->with('success', $siswas->count() . ' siswa berhasil diarsipkan. Data tetap tersimpan dan bisa dipulihkan.');
    }

    public function restore(int $siswa): RedirectResponse
    {
        $siswaModel = Siswa::onlyTrashed()->findOrFail($siswa);

        Log::channel('sis')->info('[Siswa] Restore siswa', [
            'siswa_id' => $siswaModel->id,
            'nisn' => $siswaModel->nisn,
            'user_id' => request()->user()->id,
        ]);

        $siswaModel->restore();

        return redirect()
            ->route('siswa.index', ['status_data' => 'arsip'])
            ->with('success', 'Siswa ' . $siswaModel->nama_lengkap . ' berhasil dipulihkan.');
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mode' => ['required', Rule::in(['selected', 'filtered'])],
            'siswa_ids' => ['required_if:mode,selected', 'array'],
            'siswa_ids.*' => ['integer'],
            'search' => ['nullable', 'string'],
            'kelas_id' => ['nullable', 'integer'],
            'status_aktif' => ['nullable', Rule::in(['0', '1'])],
        ], [
            'siswa_ids.required_if' => 'Pilih minimal satu siswa untuk dipulihkan.',
        ]);

        $query = Siswa::onlyTrashed();
        if ($validated['mode'] === 'selected') {
            $query->whereKey($validated['siswa_ids'] ?? []);
        } else {
            $this->applyIndexFilters($query, $request);
        }

        $siswas = $query->get();
        if ($siswas->isEmpty()) {
            return back()->with('error', 'Tidak ada siswa arsip yang cocok untuk dipulihkan.');
        }

        DB::transaction(function () use ($siswas, $request) {
            foreach ($siswas as $siswa) {
                $siswa->restore();
            }

            Log::channel('sis')->info('[Siswa] Bulk restore success', [
                'count' => $siswas->count(),
                'mode' => $request->input('mode'),
                'user_id' => $request->user()->id,
            ]);
        });

        return redirect()
            ->route('siswa.index', ['status_data' => 'arsip'])
            ->with('success', $siswas->count() . ' siswa berhasil dipulihkan.');
    }

    public function showImportForm(Request $request): View
    {
        if ($request->isMethod('post')) {
            // Handle file upload and redirect to preview
            return $this->previewImport($request);
        }

        return view('siswa.import');
    }

    public function previewImport(Request $request): View
    {
        set_time_limit(500);

        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:2048',
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();

        $data = [];
        if ($extension === 'csv') {
            $csvData = array_map('str_getcsv', file($file->getRealPath()));
            $header = array_shift($csvData);
            foreach ($csvData as $row) {
                $data[] = array_combine($header, $row);
            }
        } else {
            return back()->with('error', 'Format Excel belum didukung. Gunakan CSV.');
        }

        $validData = [];
        $importErrors = [];
        $rowIndex = 2; // Mulai dari baris 2 (setelah header)

        foreach ($data as $row) {
            $rowErrors = [];

            // Validasi nisn
            if (empty($row['nisn'])) {
                $rowErrors[] = 'nisn wajib diisi';
            } elseif (! is_numeric($row['nisn']) || strlen($row['nisn']) != 10) {
                $rowErrors[] = 'nisn harus 10 digit angka';
            } elseif (Siswa::where('nisn', $row['nisn'])->exists()) {
                $rowErrors[] = 'nisn sudah terdaftar';
            }

            // Validasi nama_lengkap
            if (empty($row['nama_lengkap'])) {
                $rowErrors[] = 'nama_lengkap wajib diisi';
            }

            // Validasi kelas
            $kelas = Kelas::where('nama_kelas', $row['nama_kelas'] ?? null)->first();
            if (! $kelas) {
                $rowErrors[] = 'nama_kelas tidak ditemukan';
            }

            // Validasi jenis_kelamin
            $jk = strtoupper($row['jenis_kelamin'] ?? '');
            if (! in_array($jk, ['L', 'P'])) {
                $rowErrors[] = 'jenis_kelamin harus L atau P';
            }

            // Jika tidak ada error, tambah ke validData
            if (empty($rowErrors)) {
                // Di previewImport() — bagian $validData[]
                $validData[] = [
                    'nis'           => $row['nis'] ?? null,
                    'nisn'          => $row['nisn'],
                    'nik'           => $row['nik'] ?? null,          // ← tambah
                    'nokk'          => $row['nokk'] ?? null,         // ← tambah
                    'nama_lengkap'  => $row['nama_lengkap'],
                    'jenis_kelamin' => $jk,
                    'kelas_id'      => $kelas->id,
                    'angkatan'      => $row['angkatan'] ?? null,
                    'tempat_lahir'  => $row['tempat_lahir'] ?? null,
                    'tanggal_lahir' => $this->parseDate($row['tanggal_lahir'] ?? null), // ← pakai parseDate
                    'alamat'        => $row['alamat'] ?? null,
                    'agama'         => $row['agama'] ?? null,        // ← tambah
                    'asal'          => $row['asal'] ?? null,         // ← tambah
                    'desa'          => $row['desa'] ?? null,
                    'kelurahan'     => $row['kelurahan'] ?? null,
                    'kecamatan'     => $row['kecamatan'] ?? null,
                    'kabupaten'     => $row['kabupaten'] ?? null,
                    'kode_pos'      => $row['kode_pos'] ?? null,
                    'no_hp_siswa'   => $row['no_hp_siswa'] ?? null,
                    'email'         => $row['email'] ?? null,        // ← tambah
                    'no_hp_ortu1'   => $row['no_hp_ortu1'] ?? null,
                    'no_hp_ortu2'   => $row['no_hp_ortu2'] ?? null,
                    'nama_ortu1'    => $row['nama_ortu1'] ?? null,
                    'nama_ortu2'    => $row['nama_ortu2'] ?? null,
                    'nama_wali'     => $row['nama_wali'] ?? null,
                    'bb'            => $row['bb'] ?? null,           // ← tambah
                    'tb'            => $row['tb'] ?? null,           // ← tambah
                    'lk'            => $row['lk'] ?? null,           // ← tambah
                    'status_aktif'  => isset($row['status_aktif']) ? filter_var($row['status_aktif'], FILTER_VALIDATE_BOOLEAN) : true,
                    'noreg_legacy'  => $row['noreg'] ?? null,
                ];
            } else {
                $importErrors[] = [
                    'row' => $rowIndex,
                    'data' => $row,
                    'errors' => $rowErrors,
                ];
            }

            $rowIndex++;
        }

        // Simpan validData ke session untuk import nanti
        session(['import_siswa_data' => $validData]);

        return view('siswa.import_preview', compact('validData', 'importErrors'));
    }

    public function importProcess(Request $request): RedirectResponse
    {
        set_time_limit(300);

        $data = session('import_siswa_data');

        if (! $data || ! is_array($data)) {
            return redirect()->route('siswa.import.form')->with('error', 'Data import tidak ditemukan. Silakan upload ulang file.');
        }

        $successCount = 0;
        $errors = [];

        foreach ($data as $index => $row) {
            try {
                $validated = [
                    'nis' => $row['nis'] ?? null,
                    'nisn' => $row['nisn'] ?? null,
                    'nama_lengkap' => $row['nama_lengkap'] ?? null,
                    'jenis_kelamin' => strtoupper($row['jenis_kelamin'] ?? 'L') === 'L' ? 'L' : 'P',
                    'kelas_id' => $row['kelas_id'] ?? null,
                    'angkatan' => $row['angkatan'] ?? null,
                    'tempat_lahir' => $row['tempat_lahir'] ?? null,
                    'tanggal_lahir' => $row['tanggal_lahir'] ?? null,
                    'alamat' => $row['alamat'] ?? null,
                    'desa' => $row['desa'] ?? null,
                    'kelurahan' => $row['kelurahan'] ?? null,
                    'kecamatan' => $row['kecamatan'] ?? null,
                    'kabupaten' => $row['kabupaten'] ?? null,
                    'kode_pos' => $row['kode_pos'] ?? null,
                    'no_hp_siswa' => $row['no_hp_siswa'] ?? null,
                    'no_hp_ortu1' => $row['no_hp_ortu1'] ?? null,
                    'no_hp_ortu2' => $row['no_hp_ortu2'] ?? null,
                    'nama_ortu1' => $row['nama_ortu1'] ?? null,
                    'nama_ortu2' => $row['nama_ortu2'] ?? null,
                    'nama_wali' => $row['nama_wali'] ?? null,
                    'status_aktif' => isset($row['status_aktif']) ? filter_var($row['status_aktif'], FILTER_VALIDATE_BOOLEAN) : true,
                    'noreg_legacy' => $row['noreg'] ?? null,
                ];

                // Basic validation
                if (empty($validated['nisn']) || empty($validated['nama_lengkap'])) {
                    throw new \Exception('nisn dan nama_lengkap wajib diisi');
                }

                if (Siswa::where('nisn', $validated['nisn'])->exists()) {
                    throw new \Exception('nisn sudah ada');
                }

                $siswa = Siswa::create($validated);

                // Create user account for login
                $email = $validated['nisn'] . '@smkn5madiun.sch.id';
                $user = User::where('email', $email)->first();
                if (! $user) {
                    $user = User::create([
                        'name' => $validated['nama_lengkap'],
                        'email' => $email,
                        'password' => $siswa->getHashedDefaultPassword(), // Default password menggunakan NISN
                        'phone' => $validated['no_hp_siswa'] ?? null,
                        'role_utama' => 'siswa',
                        'siswa_id' => $siswa->id,
                    ]);

                    // Assign role
                    $user->assignRole('siswa');
                }

                // Update siswa with user_id
                $siswa->update(['user_id' => $user->id]);

                Log::channel('sis')->info('[Siswa Import] User baru dibuat', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'nisn' => $validated['nisn'],
                ]);

                $successCount++;
            } catch (\Exception $e) {
                $errors[] = 'Baris ' . ($index + 2) . ': ' . $e->getMessage();
            }
        }

        $message = $successCount . ' data berhasil diimport.';
        if (! empty($errors)) {
            $message .= ' Error: ' . implode('; ', array_slice($errors, 0, 5));
        }

        // Clear session data
        session()->forget('import_siswa_data');

        return redirect()->route('siswa.index')->with('success', $message);
    }

    public function exportCV(Siswa $siswa)
    {
        Log::channel('sis')->info('[Siswa] Export CV PDF', [
            'siswa_id' => $siswa->id,
            'user_id' => request()->user()->id,
        ]);

        $siswa->load(['kelas', 'absenSiswa' => fn($q) => $q->whereYear('tanggal', date('Y'))]);

        $pdf = Pdf::loadView('pdf.cv_siswa', compact('siswa'));

        return $pdf->download('CV_' . $siswa->nama_lengkap . '.pdf');
    }

    public function downloadTemplate()
    {
        Log::channel('sis')->info('[Siswa] Download template CSV', [
            'user_id' => request()->user()->id,
        ]);

        $filename = 'template_import_siswa.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            // Header
            fputcsv($file, [
                'nisn',
                'nama_lengkap',
                'jenis_kelamin',
                'nama_kelas',
                'nis',
                'angkatan',
                'tempat_lahir',
                'tanggal_lahir',
                'alamat',
                'desa',
                'kelurahan',
                'kecamatan',
                'kabupaten',
                'kode_pos',
                'no_hp_siswa',
                'no_hp_ortu1',
                'no_hp_ortu2',
                'nama_ortu1',
                'nama_ortu2',
                'nama_wali',
                'noreg',
                'status_aktif',
            ]);

            // Contoh data siswa 1
            fputcsv($file, [
                '1234567890',
                'Ahmad Surya Pratama',
                'L',
                'X RPL 1',
                '2021001',
                '2021',
                'Jakarta',
                '2005-01-15',
                'Jl. Sudirman No. 123',
                'Desa Sukamaju',
                'Kelurahan Jakarta Pusat',
                'Kecamatan Tanah Abang',
                'Kabupaten Jakarta Pusat',
                '10160',
                '081234567890',
                '081234567891',
                '',
                'Budi Santoso',
                'Siti Aminah',
                '',
                'REG001',
                '1',
            ]);

            // Contoh data siswa 2
            fputcsv($file, [
                '1234567891',
                'Siti Nurhaliza',
                'P',
                'X RPL 1',
                '2021002',
                '2021',
                'Bandung',
                '2005-03-20',
                'Jl. Asia Afrika No. 45',
                'Desa Cibaduyut',
                'Kelurahan Bandung Wetan',
                'Kecamatan Bandung Kidul',
                'Kabupaten Bandung',
                '40111',
                '081234567892',
                '081234567893',
                '081234567894',
                'Ahmad Rahman',
                'Fatimah Zahra',
                'Umar bin Khattab',
                'REG002',
                '1',
            ]);

            // Contoh data siswa 3
            fputcsv($file, [
                '1234567892',
                'Budi Santoso Putra',
                'L',
                'X TKJ 1',
                '2021003',
                '2021',
                'Surabaya',
                '2005-07-10',
                'Jl. Tunjungan No. 78',
                'Desa Wonocolo',
                'Kelurahan Surabaya Barat',
                'Kecamatan Bubutan',
                'Kabupaten Surabaya',
                '60271',
                '081234567895',
                '081234567896',
                '',
                'Joko Widodo',
                'Iriana Jokowi',
                '',
                'REG003',
                '1',
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function export()
    {
        Log::channel('sis')->info('[Siswa] Export CSV', [
            'user_id' => request()->user()->id,
        ]);

        $filename = 'export_siswa_' . date('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            // Header
            fputcsv($file, [
                'nisn',
                'nama_lengkap',
                'jenis_kelamin',
                'nama_kelas',
                'nis',
                'angkatan',
                'tempat_lahir',
                'tanggal_lahir',
                'alamat',
                'desa',
                'kelurahan',
                'kecamatan',
                'kabupaten',
                'kode_pos',
                'no_hp_siswa',
                'no_hp_ortu1',
                'no_hp_ortu2',
                'nama_ortu1',
                'nama_ortu2',
                'nama_wali',
                'noreg',
                'status_aktif',
            ]);

            // Data siswa
            $siswas = Siswa::with('kelas')->get();
            foreach ($siswas as $siswa) {
                fputcsv($file, [
                    $siswa->nisn,
                    $siswa->nama_lengkap,
                    $siswa->jenis_kelamin,
                    $siswa->kelas?->nama_kelas,
                    $siswa->nis,
                    $siswa->angkatan,
                    $siswa->tempat_lahir,
                    $siswa->tanggal_lahir,
                    $siswa->alamat,
                    $siswa->desa,
                    $siswa->kelurahan,
                    $siswa->kecamatan,
                    $siswa->kabupaten,
                    $siswa->kode_pos,
                    $siswa->no_hp_siswa,
                    $siswa->no_hp_ortu1,
                    $siswa->no_hp_ortu2,
                    $siswa->nama_ortu1,
                    $siswa->nama_ortu2,
                    $siswa->nama_wali,
                    $siswa->noreg_legacy,
                    $siswa->status_aktif ? '1' : '0',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // Tambahkan sebagai private method di SiswaController
    private function parseDate($value): ?string
    {
        if (empty($value)) return null;

        $formats = ['Y-m-d', 'd-m-Y', 'd/m/Y', 'Y/m/d'];
        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, trim($value));
            if ($date !== false) {
                return $date->format('Y-m-d');
            }
        }

        $timestamp = strtotime($value);
        return $timestamp !== false ? date('Y-m-d', $timestamp) : null;
    }

    private function applyIndexFilters(Builder $query, Request $request): void
    {
        $query
            ->when($request->filled('search'), function (Builder $q) use ($request) {
                $search = $request->string('search')->toString();

                $q->where(function (Builder $sq) use ($search) {
                    $sq->where('nama_lengkap', 'like', '%' . $search . '%')
                        ->orWhere('nis', 'like', '%' . $search . '%')
                        ->orWhere('nisn', 'like', '%' . $search . '%');
                });
            })
            ->when($request->filled('kelas_id'), fn(Builder $q) => $q->where('kelas_id', $request->kelas_id))
            ->when($request->filled('status_aktif'), fn(Builder $q) => $q->where('status_aktif', $request->boolean('status_aktif')));
    }

    // =========================================================
    // UPDATE NOMOR HP (import dari Excel: nama_lengkap, kelas, no_hp_siswa, no_hp_ortu1)
    // =========================================================

    /**
     * Tampilkan form upload file Excel untuk update nomor HP.
     */
    public function showUpdateHpForm(): View
    {
        return view('siswa.update-hp');
    }

    /**
     * Proses file Excel, cocokkan nama, tampilkan halaman review.
     */
    // public function previewUpdateHp(Request $request): View|\Illuminate\Http\RedirectResponse
    // {
    //     $request->validate([
    //         'file' => 'required|file|mimes:xlsx,xls|max:5120',
    //     ]);

    //     $file      = $request->file('file');
    //     $allKelas  = Kelas::orderBy('nama_kelas')->get()->keyBy(fn($k) => $this->normalizeName($k->nama_kelas));
    //     $allSiswa  = Siswa::with('kelas')->get();

    //     // Baca Excel menggunakan PhpSpreadsheet
    //     $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
    //     $sheet       = $spreadsheet->getActiveSheet();
    //     $rows        = $sheet->toArray(null, true, true, false);

    //     if (empty($rows)) {
    //         return back()->with('error', 'File Excel kosong.');
    //     }

    //     // Deteksi header (baris pertama)
    //     $header = array_map(fn($h) => strtolower(trim((string) $h)), $rows[0]);
    //     array_shift($rows);

    //     // Cari indeks kolom
    //     $colNama   = array_search('nama_lengkap', $header);
    //     $colHpSiswa = array_search('no_hp_siswa',  $header);
    //     $colHpOrtu = array_search('no_hp_ortu1',  $header);
    //     $colKelas  = array_search('kelas',         $header);

    //     if ($colNama === false) {
    //         return back()->with('error', 'Kolom nama_lengkap tidak ditemukan pada file Excel.');
    //     }

    //     $previewRows    = [];
    //     $notFoundRows   = [];
    //     $ambiguousRows  = [];
    //     $totalExcel     = count($rows);

    //     // Bangun index kelas dari Excel → objek Kelas (untuk tiebreaker)
    //     $kelasByNorm = $allKelas; // sudah di-keyBy normalizeName

    //     foreach ($rows as $i => $row) {
    //         $namaExcel   = trim((string) ($row[$colNama]    ?? ''));
    //         $hpSiswaNew  = trim((string) ($row[$colHpSiswa] ?? ''));
    //         $hpOrtuNew   = trim((string) ($row[$colHpOrtu]  ?? ''));
    //         $kelasExcel  = trim((string) ($row[$colKelas]   ?? ''));

    //         if ($namaExcel === '') {
    //             continue;
    //         }

    //         // Kelas dari Excel (jika ada)
    //         $kelasFromExcel = $kelasExcel !== ''
    //             ? $kelasByNorm->get($this->normalizeName($kelasExcel))
    //             : null;

    //         // Cocokkan nama ke database dengan kelas sebagai tiebreaker
    //         $matchResult = $this->matchSiswaByNameWithClass(
    //             $allSiswa,
    //             $namaExcel,
    //             $kelasFromExcel?->id
    //         );

    //         // $matchResult = ['status' => 'matched'|'ambiguous'|'not_found', 'siswa' => ..., 'candidates' => [...]]
    //         if ($matchResult['status'] === 'not_found') {
    //             $notFoundRows[] = [
    //                 'row'      => $i + 2,
    //                 'nama'     => $namaExcel,
    //                 'alasan'   => 'Nama tidak ditemukan di database',
    //                 'kelas'    => $kelasExcel,
    //                 'hp_siswa' => $hpSiswaNew,
    //                 'hp_ortu'  => $hpOrtuNew,
    //             ];
    //             continue;
    //         }

    //         if ($matchResult['status'] === 'ambiguous') {
    //             $ambiguousRows[] = [
    //                 'row'        => $i + 2,
    //                 'nama'       => $namaExcel,
    //                 'kelas'      => $kelasExcel,
    //                 'hp_siswa'   => $hpSiswaNew,
    //                 'hp_ortu'    => $hpOrtuNew,
    //                 'alasan'     => $matchResult['alasan'],
    //                 'candidates' => array_map(fn($c) => [
    //                     'id'         => $c['siswa']->id,
    //                     'nama'       => $c['siswa']->nama_lengkap,
    //                     'kelas'      => $c['siswa']->kelas?->nama_kelas ?? '-',
    //                     'similarity' => round($c['pct']),
    //                 ], $matchResult['candidates']),
    //             ];
    //             continue;
    //         }

    //         // Status = matched
    //         $matched = $matchResult['siswa'];

    //         // Tentukan kelas_id baru
    //         $kelasId  = $matched->kelas_id;
    //         $kelasNew = null;
    //         if ($kelasFromExcel) {
    //             $kelasNew = $kelasFromExcel;
    //             $kelasId  = $kelasFromExcel->id;
    //         }

    //         $changed =
    //             ($kelasId    !== $matched->kelas_id)          ||
    //             ($hpSiswaNew !== ($matched->no_hp_siswa ?? '')) ||
    //             ($hpOrtuNew  !== ($matched->no_hp_ortu1 ?? ''));

    //         $previewRows[] = [
    //             'siswa_id'      => $matched->id,
    //             'nama_db'       => $matched->nama_lengkap,
    //             'nama_excel'    => $namaExcel,
    //             'kelas_lama'    => $matched->kelas?->nama_kelas ?? '-',
    //             'kelas_baru'    => $kelasNew ? $kelasNew->nama_kelas : ($matched->kelas?->nama_kelas ?? '-'),
    //             'kelas_id_baru' => $kelasId,
    //             'hp_siswa_lama' => $matched->no_hp_siswa ?? '',
    //             'hp_siswa_baru' => $hpSiswaNew,
    //             'hp_ortu_lama'  => $matched->no_hp_ortu1 ?? '',
    //             'hp_ortu_baru'  => $hpOrtuNew,
    //             'match_type'    => $matchResult['match_type'] ?? 'exact', // exact|normalized|fuzzy
    //             'status'        => $changed ? 'update' : 'sama',
    //         ];
    //     }

    //     $toUpdateCount = collect($previewRows)->where('status', 'update')->count();

    //     // Simpan ke session untuk proses update nanti
    //     session(['update_hp_data' => $previewRows]);

    //     return view('siswa.update-hp-review', compact(
    //         'previewRows',
    //         'notFoundRows',
    //         'ambiguousRows',
    //         'totalExcel',
    //         'toUpdateCount',
    //     ));
    // }

    /**
     * Jalankan update ke database setelah konfirmasi review.
     */
    public function previewUpdateHp(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:5120',
        ]);

        $file = $request->file('file');

        // ✅ Gunakan /tmp/ — sudah di-whitelist open_basedir di shared hosting
        $tmpDir  = rtrim(sys_get_temp_dir(), '/');
        $tmpPath = $tmpDir . '/' . uniqid('sis_hp_') . '.' . $file->getClientOriginalExtension();

        // Pindahkan file upload ke /tmp/
        $file->move($tmpDir, basename($tmpPath));

        try {
            $rows = \Maatwebsite\Excel\Facades\Excel::toArray(
                new class implements \Maatwebsite\Excel\Concerns\ToArray {
                    public function array(array $array): array
                    {
                        return $array;
                    }
                },
                $tmpPath,
                null,
                \Maatwebsite\Excel\Excel::XLSX
            )[0] ?? [];
        } catch (\Exception $e) {
            @unlink($tmpPath);
            return back()->with('error', 'Gagal membaca file Excel: ' . $e->getMessage());
        }

        @unlink($tmpPath);

        if (empty($rows)) {
            return back()->with('error', 'File Excel kosong.');
        }

        $allKelas = Kelas::orderBy('nama_kelas')->get()->keyBy(fn($k) => $this->normalizeName($k->nama_kelas));
        $allSiswa = Siswa::with('kelas')->get();

        // Deteksi header (baris pertama)
        $header = array_map(fn($h) => strtolower(trim((string) $h)), $rows[0]);
        array_shift($rows);

        // Cari indeks kolom
        $colNama    = array_search('nama_lengkap', $header);
        $colHpSiswa = array_search('no_hp_siswa',  $header);
        $colHpOrtu  = array_search('no_hp_ortu1',  $header);
        $colKelas   = array_search('kelas',         $header);

        if ($colNama === false) {
            return back()->with('error', 'Kolom nama_lengkap tidak ditemukan pada file Excel.');
        }

        $previewRows   = [];
        $notFoundRows  = [];
        $ambiguousRows = [];
        $totalExcel    = count($rows);

        foreach ($rows as $i => $row) {
            $namaExcel  = trim((string) ($row[$colNama]    ?? ''));
            $hpSiswaNew = trim((string) ($row[$colHpSiswa] ?? ''));
            $hpOrtuNew  = trim((string) ($row[$colHpOrtu]  ?? ''));
            $kelasExcel = trim((string) ($row[$colKelas]   ?? ''));

            if ($namaExcel === '') {
                continue;
            }

            $kelasFromExcel = $kelasExcel !== ''
                ? $allKelas->get($this->normalizeName($kelasExcel))
                : null;

            $matchResult = $this->matchSiswaByNameWithClass(
                $allSiswa,
                $namaExcel,
                $kelasFromExcel?->id
            );

            if ($matchResult['status'] === 'not_found') {
                $notFoundRows[] = [
                    'row'      => $i + 2,
                    'nama'     => $namaExcel,
                    'alasan'   => 'Nama tidak ditemukan di database',
                    'kelas'    => $kelasExcel,
                    'hp_siswa' => $hpSiswaNew,
                    'hp_ortu'  => $hpOrtuNew,
                ];
                continue;
            }

            if ($matchResult['status'] === 'ambiguous') {
                $ambiguousRows[] = [
                    'row'        => $i + 2,
                    'nama'       => $namaExcel,
                    'kelas'      => $kelasExcel,
                    'hp_siswa'   => $hpSiswaNew,
                    'hp_ortu'    => $hpOrtuNew,
                    'alasan'     => $matchResult['alasan'],
                    'candidates' => array_map(fn($c) => [
                        'id'         => $c['siswa']->id,
                        'nama'       => $c['siswa']->nama_lengkap,
                        'kelas'      => $c['siswa']->kelas?->nama_kelas ?? '-',
                        'similarity' => round($c['pct']),
                    ], $matchResult['candidates']),
                ];
                continue;
            }

            // Status = matched
            $matched = $matchResult['siswa'];

            $kelasId  = $matched->kelas_id;
            $kelasNew = null;
            if ($kelasFromExcel) {
                $kelasNew = $kelasFromExcel;
                $kelasId  = $kelasFromExcel->id;
            }

            $changed =
                ($kelasId    !== $matched->kelas_id)            ||
                ($hpSiswaNew !== ($matched->no_hp_siswa ?? '')) ||
                ($hpOrtuNew  !== ($matched->no_hp_ortu1 ?? ''));

            $previewRows[] = [
                'siswa_id'      => $matched->id,
                'nama_db'       => $matched->nama_lengkap,
                'nama_excel'    => $namaExcel,
                'kelas_lama'    => $matched->kelas?->nama_kelas ?? '-',
                'kelas_baru'    => $kelasNew ? $kelasNew->nama_kelas : ($matched->kelas?->nama_kelas ?? '-'),
                'kelas_id_baru' => $kelasId,
                'hp_siswa_lama' => $matched->no_hp_siswa ?? '',
                'hp_siswa_baru' => $hpSiswaNew,
                'hp_ortu_lama'  => $matched->no_hp_ortu1 ?? '',
                'hp_ortu_baru'  => $hpOrtuNew,
                'match_type'    => $matchResult['match_type'] ?? 'exact',
                'status'        => $changed ? 'update' : 'sama',
            ];
        }

        $toUpdateCount = collect($previewRows)->where('status', 'update')->count();

        session(['update_hp_data' => $previewRows]);

        return view('siswa.update-hp-review', compact(
            'previewRows',
            'notFoundRows',
            'ambiguousRows',
            'totalExcel',
            'toUpdateCount',
        ));
    }

    /**
     * Jalankan update HP ke database berdasarkan data preview dari session.
     */
    public function processUpdateHp(Request $request): RedirectResponse
    {
        $previewRows = session('update_hp_data');

        if (empty($previewRows)) {
            return redirect()->route('siswa.update-hp.form')
                ->with('error', 'Tidak ada data untuk diproses. Silakan upload ulang file Excel.');
        }

        $toUpdate = collect($previewRows)->where('status', 'update');

        if ($toUpdate->isEmpty()) {
            session()->forget('update_hp_data');
            return redirect()->route('siswa.update-hp.form')
                ->with('info', 'Tidak ada data yang perlu diupdate.');
        }

        $updatedCount = 0;
        $errors       = [];

        foreach ($toUpdate as $row) {
            try {
                $siswa = Siswa::find($row['siswa_id']);
                if (!$siswa) {
                    $errors[] = "Siswa ID {$row['siswa_id']} ({$row['nama_db']}) tidak ditemukan.";
                    continue;
                }

                $siswa->no_hp_siswa = $row['hp_siswa_baru'] !== '' ? $row['hp_siswa_baru'] : null;
                $siswa->no_hp_ortu1 = $row['hp_ortu_baru']  !== '' ? $row['hp_ortu_baru']  : null;

                if (!empty($row['kelas_id_baru'])) {
                    $siswa->kelas_id = $row['kelas_id_baru'];
                }

                $siswa->save();
                $updatedCount++;
            } catch (\Exception $e) {
                $errors[] = "Gagal update {$row['nama_db']}: " . $e->getMessage();
            }
        }

        session()->forget('update_hp_data');

        Log::channel('sis')->info('[Siswa] Bulk update HP dari Excel', [
            'updated' => $updatedCount,
            'errors'  => count($errors),
            'user'    => auth()->id(),
        ]);

        $message = "Berhasil mengupdate {$updatedCount} data siswa.";
        if (!empty($errors)) {
            $message .= ' ' . count($errors) . ' data gagal: ' . implode('; ', array_slice($errors, 0, 3));
        }

        return redirect()->route('siswa.index')
            ->with(empty($errors) ? 'success' : 'warning', $message);
    }

    /**
     * Normalisasi string untuk perbandingan: lowercase, trim, hilangkan spasi berlebih.
     */
    private function normalizeName(string $name): string
    {
        $name = mb_strtolower(trim($name));
        $name = preg_replace('/\s+/', ' ', $name);
        // Hapus karakter non-alphanumeric kecuali spasi
        $name = preg_replace('/[^\p{L}\p{N} ]/u', '', $name);
        return $name;
    }

    /**
     * Cocokkan nama siswa Excel ke koleksi siswa database dengan kelas sebagai tiebreaker.
     *
     * Alur:
     * 1. Exact match (case-insensitive) → langsung cocok
     * 2. Normalized match               → langsung cocok
     * 3. Kandidat similarity ≥ 82%:
     *    a. Hanya 1 kandidat            → cocok
     *    b. >1 kandidat + ada kelas di Excel:
     *       - 1 kandidat kelasnya cocok → cocok dengan tiebreaker kelas
     *       - 0 atau >1 kelas cocok     → ambiguous
     *    c. >1 kandidat, tidak ada kelas → ambiguous
     * 4. Tidak ada kandidat ≥ 82%       → not_found
     *
     * Return array:
     *   ['status' => 'matched',   'siswa' => Siswa, 'match_type' => 'exact'|'normalized'|'fuzzy'|'fuzzy+class']
     *   ['status' => 'ambiguous', 'alasan' => string, 'candidates' => [...]]
     *   ['status' => 'not_found']
     */
    private function matchSiswaByNameWithClass(
        \Illuminate\Support\Collection $siswas,
        string $namaExcel,
        ?int $kelasIdFromExcel
    ): array {
        $normalized = $this->normalizeName($namaExcel);

        // 1. Exact match
        $exact = $siswas->first(
            fn($s) => mb_strtolower(trim($s->nama_lengkap)) === mb_strtolower(trim($namaExcel))
        );
        if ($exact) {
            return ['status' => 'matched', 'siswa' => $exact, 'match_type' => 'exact'];
        }

        // 2. Normalized match
        $normMatch = $siswas->first(
            fn($s) => $this->normalizeName($s->nama_lengkap) === $normalized
        );
        if ($normMatch) {
            return ['status' => 'matched', 'siswa' => $normMatch, 'match_type' => 'normalized'];
        }

        // 3. Similarity-based (threshold 82%)
        $candidates = [];
        foreach ($siswas as $s) {
            similar_text($normalized, $this->normalizeName($s->nama_lengkap), $pct);
            if ($pct >= 82) {
                $candidates[] = ['siswa' => $s, 'pct' => $pct];
            }
        }

        if (empty($candidates)) {
            return ['status' => 'not_found'];
        }

        // Sort descending by similarity
        usort($candidates, fn($a, $b) => $b['pct'] <=> $a['pct']);

        // Hanya 1 kandidat fuzzy → langsung cocok
        if (count($candidates) === 1) {
            return ['status' => 'matched', 'siswa' => $candidates[0]['siswa'], 'match_type' => 'fuzzy'];
        }

        // >1 kandidat → coba tiebreaker kelas
        if ($kelasIdFromExcel !== null) {
            $byClass = array_filter(
                $candidates,
                fn($c) => $c['siswa']->kelas_id === $kelasIdFromExcel
            );
            $byClass = array_values($byClass);

            if (count($byClass) === 1) {
                // Kelas mempersempit ke 1 kandidat → cocok
                return ['status' => 'matched', 'siswa' => $byClass[0]['siswa'], 'match_type' => 'fuzzy+class'];
            }

            if (count($byClass) === 0) {
                // Tidak ada kandidat yang kelasnya cocok → ambigu (nama mirip tapi kelas tidak sesuai semua)
                return [
                    'status'     => 'ambiguous',
                    'alasan'     => 'Nama mirip dengan ' . count($candidates) . ' siswa, kelas di Excel (' .
                        ($candidates[0]['siswa']->kelas?->nama_kelas ?? '-') .
                        ') tidak cocok dengan satupun kandidat',
                    'candidates' => $candidates,
                ];
            }

            // >1 yang kelasnya cocok → masih ambigu
        }

        // Ambigu: >1 kandidat, tidak bisa diselesaikan
        $topTwo = array_slice($candidates, 0, 2);
        return [
            'status'     => 'ambiguous',
            'alasan'     => count($candidates) . ' kandidat ditemukan (similarity ' .
                round($topTwo[0]['pct']) . '% & ' . round($topTwo[1]['pct']) . '%)',
            'candidates' => $candidates,
        ];
    }

    /**
     * @deprecated Gunakan matchSiswaByNameWithClass
     */
    private function matchSiswaByName(\Illuminate\Support\Collection $siswas, string $namaExcel): ?Siswa
    {
        $result = $this->matchSiswaByNameWithClass($siswas, $namaExcel, null);
        return $result['status'] === 'matched' ? $result['siswa'] : null;
    }
}
