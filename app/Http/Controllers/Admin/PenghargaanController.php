<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PenghargaanStoreRequest;
use App\Http\Requests\PenghargaanUpdateRequest;
use App\Models\Kelas;
use App\Models\Penghargaan;
use App\Models\Siswa;
use App\Services\TatibPoinService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PenghargaanController extends Controller
{
    public function __construct(
        private readonly TatibPoinService $tatibPoin
    ) {}

    public function index(Request $request): View
    {
        $tahunAjaran = $request->get('tahun_ajaran', $this->tatibPoin->tahunAjaranAktif());
        $tab         = $request->get('tab', 'semua'); // semua | pending | acc

        $query = Penghargaan::with(['siswa.kelas', 'creator'])
            ->when($tahunAjaran, fn($q) => $q->where('tahun_ajaran', $tahunAjaran))
            ->when($request->filled('siswa_id'), fn($q) => $q->where('siswa_id', $request->siswa_id))
            ->when($request->filled('kelas_id'), function ($q) use ($request) {
                $q->whereHas('siswa', fn($sq) => $sq->where('kelas_id', $request->kelas_id));
            })
            ->when($request->filled('dari_tanggal'), fn($q) => $q->whereDate('tgl', '>=', $request->dari_tanggal))
            ->when($request->filled('sampai_tanggal'), fn($q) => $q->whereDate('tgl', '<=', $request->sampai_tanggal))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($sq) use ($search) {
                    $sq->where('nama', 'like', "%{$search}%")
                        ->orWhere('noreg', 'like', "%{$search}%")
                        ->orWhere('isi', 'like', "%{$search}%")
                        ->orWhereHas('siswa', fn($ssq) => $ssq
                            ->where('nama_lengkap', 'like', "%{$search}%")
                            ->orWhere('nis', 'like', "%{$search}%"));
                });
            })
            // Filter tab
            ->when($tab === 'pending', fn($q) => $q->where(fn($q2) =>
                $q2->where('acc', '!=', 'YA')->orWhereNull('acc')->orWhere('acc', '')
            ))
            ->when($tab === 'acc', fn($q) => $q->where('acc', 'YA'))
            // Urutan: pending (belum ACC) tampil LEBIH DULU, lalu sudah ACC
            // Di dalam masing-masing grup: terbaru di atas
            ->orderByRaw("CASE WHEN acc = 'YA' THEN 1 ELSE 0 END ASC")
            ->orderByDesc('tgl')
            ->orderByDesc('idpen');

        // Stats untuk tab counter
        // total_poin hanya dari transaksi yang sudah ACC (poinr di tbltransaksi)
        $baseQuery = Penghargaan::when($tahunAjaran, fn($q) => $q->where('tahun_ajaran', $tahunAjaran));
        $stats = [
            'total'       => (clone $query)->count(),
            'total_poin'  => (int) \App\Models\TransaksiPoin::where('thajaran', $tahunAjaran)
                                ->where('poinr', '>', 0)
                                ->sum('poinr'),
            'pending'     => (clone $baseQuery)
                                ->where(fn($q) => $q->where('acc', '!=', 'YA')->orWhereNull('acc')->orWhere('acc', ''))
                                ->count(),
            'acc'         => (clone $baseQuery)->where('acc', 'YA')->count(),
        ];

        $penghargaan = $query->paginate(20)->withQueryString();
        $kelas       = Kelas::orderBy('nama_kelas')->get();
        $siswa       = Siswa::with('kelas')->orderBy('nama_lengkap')->get();

        return view('admin.penghargaan.index', compact(
            'penghargaan', 'kelas', 'siswa', 'tahunAjaran', 'stats', 'tab'
        ));
    }

    public function create(Request $request): View
    {
        $tahunAjaran = $request->get('tahun_ajaran', $this->tatibPoin->tahunAjaranAktif());
        $subPasal = $this->tatibPoin->subPasalOptions('penghargaan', $tahunAjaran);

        return view('admin.penghargaan.create', compact('subPasal', 'tahunAjaran'));
    }

    public function siswaSearch(Request $request): \Illuminate\Http\JsonResponse
    {
        $search = trim($request->get('q', ''));
        $jenis = $request->get('jenis');

        $idGroup = match ($jenis) {
            'pelanggaran' => 'P',
            'penghargaan' => 'R',
            default => null,
        };

        $siswaQuery = Siswa::with('kelas');

        // NOTE: Untuk kebutuhan saat ini, autocomplete siswa memang tetap memfilter dari input teks.
        // Filter kategori P/R hanya mempengaruhi dropdown "Jenis" (idpasal), bukan list pencarian siswa.


        $siswa = $siswaQuery
            ->where(function ($q) use ($search) {

                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%");
            })
            ->orderBy('nama_lengkap')
            ->limit(20)
            ->get()
            ->map(function ($s) {
                $kelasNama = ($s->kelas && isset($s->kelas->nama_kelas)) ? $s->kelas->nama_kelas : '-';

                return [
                    'id' => $s->id,
                    'text' => "{$s->nama_lengkap} — {$s->nis} — {$kelasNama}",
                ];
            });

        return response()->json(['results' => $siswa]);
    }


    // public function store(PenghargaanStoreRequest $request): RedirectResponse
    // {
    //     $validated = $request->validated();

    //     DB::transaction(function () use ($validated) {
    //         $siswa = Siswa::with('kelas')->findOrFail($validated['siswa_id']);
    //         $tanggal = Carbon::parse($validated['tanggal']);
    //         $snapshot = $this->tatibPoin->snapshotSiswa($siswa);

    //         Penghargaan::create([
    //             'siswa_id' => $siswa->id,
    //             'tgl' => $tanggal,
    //             'tahun_ajaran' => $validated['tahun_ajaran'],
    //             'deviceid' => $this->limit(auth()->user()?->device_id ?: 'web', 30),
    //             'noreg' => $snapshot['noreg'],
    //             'nama' => $snapshot['nama'],
    //             'kelas' => $snapshot['kelas'],
    //             'idpasal' => $validated['idpasal'] ?? '',
    //             'isi' => $this->limit($validated['isi'], 500),
    //             'poin' => (int) $validated['poin'],
    //             'pelapor' => $this->limit(auth()->user()?->name ?: 'system', 40),
    //             'ket' => $this->limit($validated['ket'] ?? '-', 80),
    //             'tglacc' => null,
    //             'nmacc' => '-',
    //             'acc' => '',
    //             'created_by' => auth()->id(),
    //         ]);
    //     });

    //     return redirect()->route('admin.penghargaan.index', ['tahun_ajaran' => $validated['tahun_ajaran']])
    //         ->with('success', 'Data penghargaan berhasil ditambahkan. Menunggu persetujuan admin.');
    // }
    public function store(PenghargaanStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Validasi tambahan khusus notifikasi WA (opsional, di luar FormRequest)
        $request->validate([
            'kirim_wa' => 'nullable|boolean',
            'nomor_wa' => ['nullable', 'required_if:kirim_wa,1', 'regex:/^(08|628)[0-9]{8,12}$/'],
        ], [
            'nomor_wa.required_if' => 'Nomor HP wajib diisi jika notifikasi WhatsApp diaktifkan.',
            'nomor_wa.regex'       => 'Format nomor HP tidak valid (contoh: 0812xxx atau 628xxx).',
        ]);

        // ── Validasi duplikasi: cek apakah siswa sudah punya penghargaan
        //    dengan pasal yang sama pada tanggal yang sama ──────────────────
        // Guard: hanya jalankan jika semua field yang diperlukan ada dan valid
        // (FormRequest sudah memvalidasi, tapi $validated hanya berisi field yang lulus —
        //  jika siswa_id/tanggal kosong/gagal exists, key tidak akan ada di array ini)
        if (
            ! blank($validated['siswa_id'] ?? null) &&
            ! blank($validated['tanggal'] ?? null) &&
            ! blank($validated['idpasal'] ?? null)
        ) {
            $tanggalInput = Carbon::parse($validated['tanggal'])->toDateString();
            $duplikat = Penghargaan::where('siswa_id', $validated['siswa_id'])
                ->whereDate('tgl', $tanggalInput)
                ->where('idpasal', $validated['idpasal'])
                ->exists();

            if ($duplikat) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Penghargaan dengan pasal yang sama untuk siswa ini pada tanggal tersebut sudah ada.');
            }
        }

        $kirimWa = $request->boolean('kirim_wa');
        $nomorWa = null;

        if ($kirimWa && $request->filled('nomor_wa')) {
            $nomorWa = $request->input('nomor_wa');
            if (str_starts_with($nomorWa, '08')) {
                $nomorWa = '62' . substr($nomorWa, 1);
            }
        }

        DB::transaction(function () use ($validated, $kirimWa, $nomorWa) {
            $siswa = Siswa::with('kelas')->findOrFail($validated['siswa_id']);
            $tanggal = Carbon::parse($validated['tanggal']);
            $snapshot = $this->tatibPoin->snapshotSiswa($siswa);

            Penghargaan::create([
                'siswa_id' => $siswa->id,
                'tgl' => $tanggal,
                'tahun_ajaran' => $validated['tahun_ajaran'],
                'deviceid' => $this->limit(auth()->user()?->device_id ?: 'web', 30),
                'noreg' => $snapshot['noreg'],
                'nama' => $snapshot['nama'],
                'kelas' => $snapshot['kelas'],
                'idpasal' => $validated['idpasal'] ?? '',
                'isi' => $this->limit($validated['isi'], 500),
                'poin' => (int) $validated['poin'],
                'pelapor' => $this->limit(auth()->user()?->name ?: 'system', 40),
                'ket' => $this->limit($validated['ket'] ?? '-', 80),
                'tglacc' => null,
                'nmacc' => '-',
                'acc' => '',
                'created_by' => auth()->id(),
                // Disimpan dulu, BELUM dikirim — baru dieksekusi saat approve()
                'kirim_wa' => $kirimWa,
                'nomor_wa' => $nomorWa,
            ]);
        });

        $message = 'Data penghargaan berhasil ditambahkan. Menunggu persetujuan admin.';
        if ($kirimWa) {
            $message .= ' Notifikasi WhatsApp akan otomatis terkirim setelah penghargaan disetujui.';
        }

        return redirect()->route('admin.penghargaan.index', ['tahun_ajaran' => $validated['tahun_ajaran']])
            ->with('success', $message);
    }

    public function edit(Penghargaan $penghargaan): View
    {
        $tahunAjaran = old('tahun_ajaran', $penghargaan->tahun_ajaran ?: $this->tatibPoin->tahunAjaranAktif());
        // $siswa = Siswa::with('kelas')->orderBy('nama_lengkap')->get();
        $subPasal = $this->tatibPoin->subPasalOptions('penghargaan', $tahunAjaran, true);

        // Di dalam edit()
        $selectedSiswaText = ($penghargaan->siswa)
            ? (
                "{$penghargaan->siswa->nama_lengkap} — {$penghargaan->siswa->nis} — " . (
                    ($penghargaan->siswa->kelas && isset($penghargaan->siswa->kelas->nama_kelas))
                    ? $penghargaan->siswa->kelas->nama_kelas
                    : '-'
                )
            )
            : $penghargaan->nama; // fallback ke snapshot



        return view('admin.penghargaan.edit', compact(
            'penghargaan',
            'subPasal',
            'tahunAjaran',
            'selectedSiswaText'
        ));
    }

    public function update(PenghargaanUpdateRequest $request, Penghargaan $penghargaan): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $penghargaan) {
            $siswa = Siswa::with('kelas')->findOrFail($validated['siswa_id']);
            $tanggalLama = $penghargaan->tgl;
            $tanggal = Carbon::parse($validated['tanggal']);
            $snapshot = $this->tatibPoin->snapshotSiswa($siswa);

            // Jika sudah disetujui, hapus transaction lama — observer::updated akan buat yang baru
            if ($penghargaan->acc === 'YA') {
                $this->tatibPoin->deleteTransaction('RW', $tanggalLama, $penghargaan->getKey());
            }

            $penghargaan->update([
                'siswa_id' => $siswa->id,
                'tgl' => $tanggal,
                'tahun_ajaran' => $validated['tahun_ajaran'],
                'deviceid' => $this->limit(auth()->user()?->device_id ?: 'web', 30),
                'noreg' => $snapshot['noreg'],
                'nama' => $snapshot['nama'],
                'kelas' => $snapshot['kelas'],
                'idpasal' => $validated['idpasal'] ?? '',
                'isi' => $this->limit($validated['isi'], 500),
                'poin' => (int) $validated['poin'],
                'pelapor' => $this->limit(auth()->user()?->name ?: $penghargaan->pelapor, 40),
                'ket' => $this->limit($validated['ket'] ?? '-', 80),
                'created_by' => auth()->id() ?: $penghargaan->created_by,
            ]);
            // Observer PenghargaanObserver::updated() otomatis membuat transaksi baru jika acc=YA
        });

        return redirect()->route('admin.penghargaan.index', ['tahun_ajaran' => $validated['tahun_ajaran']])
            ->with('success', 'Data penghargaan berhasil diperbarui.');
    }

    public function approve(Penghargaan $penghargaan): RedirectResponse
    {
        if ($penghargaan->acc === 'YA') {
            return redirect()->route('admin.penghargaan.index')
                ->with('info', 'Penghargaan ini sudah disetujui sebelumnya.');
        }

        $waSukses = null;
        $waNomor = null;

        DB::transaction(function () use ($penghargaan) {
            $namaUser = $this->limit(auth()->user()?->name ?: 'system', 40);

            $penghargaan->update([
                'acc' => 'YA',
                'tglacc' => now(),
                'nmacc' => $namaUser,
            ]);
            // PenghargaanObserver::updated() mendeteksi 'acc' berubah → otomatis sync ke tbltransaksi
        });

        // Kirim notifikasi WA HANYA jika diaktifkan saat input, nomor tersedia,
        // dan belum pernah terkirim sebelumnya (mencegah double-send).
        if ($penghargaan->kirim_wa && $penghargaan->nomor_wa && ! $penghargaan->wa_sent_at) {
            $siswa = Siswa::with('kelas')->find($penghargaan->siswa_id);

            $pesan = \App\Services\WhatsappService::templateInfoPenghargaan(
                $siswa?->nama_lengkap ?? $penghargaan->nama,
                $siswa?->kelas?->nama_kelas ?? '-',
                $penghargaan->tgl?->translatedFormat('d F Y') ?? '-',
                $penghargaan->isi,
                (int) $penghargaan->poin
            );

            $waSukses = app(\App\Services\WhatsappService::class)->send(
                nomorHp: $penghargaan->nomor_wa,
                pesan: $pesan,
                jenis: 'penghargaan_approve',
                referensiId: $penghargaan->getKey(),
            );

            $waNomor = $penghargaan->nomor_wa;

            if ($waSukses) {
                $penghargaan->update([
                    'wa_sent_at' => now(),
                    'wa_nomor_tujuan' => $waNomor,
                ]);
            }
        }

        $message = 'Penghargaan berhasil disetujui dan masuk ke laporan poin.';
        if ($waSukses === true) {
            $message .= " Notifikasi WhatsApp berhasil dikirim ke {$waNomor}.";
        } elseif ($waSukses === false) {
            $message .= ' Namun notifikasi WhatsApp gagal dikirim, periksa log WA.';
        }

        return redirect()->route('admin.penghargaan.index')
            ->with('success', $message);
    }

    public function reject(Penghargaan $penghargaan): RedirectResponse
    {
        if ($penghargaan->acc === 'YA') {
            return redirect()->route('admin.penghargaan.index')
                ->with('error', 'Tidak dapat menolak penghargaan yang sudah disetujui.');
        }

        $tahunAjaran = $penghargaan->tahun_ajaran;
        $penghargaan->delete();

        return redirect()->route('admin.penghargaan.index', ['tahun_ajaran' => $tahunAjaran])
            ->with('success', 'Penghargaan berhasil ditolak dan dihapus.');
    }

    public function revoke(Penghargaan $penghargaan): RedirectResponse
    {
        if ($penghargaan->acc !== 'YA') {
            return redirect()->route('admin.penghargaan.index')
                ->with('error', 'Hanya penghargaan yang sudah disetujui yang dapat dibatalkan ACC.');
        }

        DB::transaction(function () use ($penghargaan) {
            // Hapus transaction dari tbltransaksi menggunakan noreff
            $this->tatibPoin->deleteTransaction('RW', $penghargaan->tgl, $penghargaan->getKey());

            // Kembalikan ke status pending
            $penghargaan->update([
                'acc' => '',
                'tglacc' => null,
                'nmacc' => '-',
            ]);
        });

        return redirect()->route('admin.penghargaan.index')
            ->with('success', 'ACC penghargaan berhasil dibatalkan. Penghargaan kembali ke status menunggu persetujuan.');
    }

    public function destroy(Penghargaan $penghargaan): RedirectResponse
    {
        DB::transaction(function () use ($penghargaan) {
            if ($penghargaan->acc === 'YA') {
                $this->tatibPoin->deleteTransaction('RW', $penghargaan->tgl, $penghargaan->getKey());
            }
            $penghargaan->delete();
        });

        return redirect()->route('admin.penghargaan.index')
            ->with('success', 'Data penghargaan berhasil dihapus.');
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $ids = array_map('intval', (array) $request->input('ids', []));

        if (empty($ids)) {
            return redirect()->back()->with('error', 'Tidak ada data yang dipilih.');
        }

        $deleted = 0;

        DB::transaction(function () use ($ids, &$deleted) {
            Penghargaan::whereIn('idpen', $ids)->each(function (Penghargaan $pgh) use (&$deleted) {
                if ($pgh->acc === 'YA') {
                    $this->tatibPoin->deleteTransaction('RW', $pgh->tgl, $pgh->getKey());
                }
                $pgh->delete();
                $deleted++;
            });
        });

        return redirect()->back()->with('success', "{$deleted} data penghargaan berhasil dihapus.");
    }

    public function bulkApprove(Request $request): RedirectResponse
    {
        $ids = array_map('intval', (array) $request->input('ids', []));

        if (empty($ids)) {
            return redirect()->back()->with('error', 'Tidak ada data yang dipilih.');
        }

        $approved = 0;
        $skipped  = 0;

        DB::transaction(function () use ($ids, &$approved, &$skipped) {
            Penghargaan::whereIn('idpen', $ids)
                ->where('acc', '!=', 'YA')
                ->each(function (Penghargaan $pgh) use (&$approved) {
                    $siswa = Siswa::find($pgh->siswa_id);
                    if (! $siswa) return;

                    $pgh->update([
                        'acc'    => 'YA',
                        'tglacc' => now(),
                        'nmacc'  => $this->limit(auth()->user()?->name ?: 'system', 40),
                    ]);
                    $this->tatibPoin->createPenghargaanTransaction($pgh, $siswa);
                    $approved++;
                });

            // Hitung yang di-skip (sudah ACC)
            $skipped = count($ids) - $approved;
        });

        $msg = "{$approved} penghargaan berhasil di-ACC.";
        if ($skipped > 0) $msg .= " {$skipped} dilewati (sudah di-ACC).";

        return redirect()->back()->with('success', $msg);
    }

    private function limit(?string $value, int $limit): string
    {
        return Str::limit((string) $value, $limit, '');
    }
}
