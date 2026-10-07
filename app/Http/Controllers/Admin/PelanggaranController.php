<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PelanggaranStoreRequest;
use App\Http\Requests\PelanggaranUpdateRequest;
use App\Models\Kelas;
use App\Models\Pelanggaran;
use App\Models\Siswa;
use App\Services\TatibPoinService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PelanggaranController extends Controller
{
    public function __construct(
        private readonly TatibPoinService $tatibPoin
    ) {}

    // public function index(Request $request): View
    // {
    //     $tahunAjaran = $request->get('tahun_ajaran', $this->tatibPoin->tahunAjaranAktif());
    //     $query = Pelanggaran::with(['siswa.kelas', 'creator'])
    //         ->when($tahunAjaran, fn ($q) => $q->where('tahun_ajaran', $tahunAjaran))
    //         ->when($request->filled('siswa_id'), fn ($q) => $q->where('siswa_id', $request->siswa_id))
    //         ->when($request->filled('kelas_id'), function ($q) use ($request) {
    //             $q->whereHas('siswa', fn ($sq) => $sq->where('kelas_id', $request->kelas_id));
    //         })
    //         ->when($request->filled('search'), function ($q) use ($request) {
    //             $search = $request->search;
    //             $q->where(function ($sq) use ($search) {
    //                 $sq->where('nama', 'like', "%{$search}%")
    //                     ->orWhere('noreg', 'like', "%{$search}%")
    //                     ->orWhere('isi', 'like', "%{$search}%")
    //                     ->orWhereHas('siswa', fn ($ssq) => $ssq
    //                         ->where('nama_lengkap', 'like', "%{$search}%")
    //                         ->orWhere('nis', 'like', "%{$search}%"));
    //             });
    //         })
    //         ->orderByDesc('tgl')
    //         ->orderByDesc('idpel');

    //     $stats = [
    //         'total' => (clone $query)->count(),
    //         'total_poin' => (clone $query)->sum('poin'),
    //     ];

    //     $pelanggaran = $query->paginate(20)->withQueryString();
    //     $kelas = Kelas::orderBy('nama_kelas')->get();
    //     $siswa = Siswa::with('kelas')->orderBy('nama_lengkap')->get();

    //     return view('admin.pelanggaran.index', compact('pelanggaran', 'kelas', 'siswa', 'tahunAjaran', 'stats'));
    // }
    public function index(Request $request): View
    {
        $tahunAjaran = $request->get('tahun_ajaran', $this->tatibPoin->tahunAjaranAktif());
        $query = Pelanggaran::with(['siswa.kelas', 'creator'])
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
            ->orderByDesc('tgl')
            ->orderByDesc('idpel');

        $stats = [
            'total' => (clone $query)->count(),
            // Ambil total poin dari tbltransaksi agar konsisten dengan rekap-poin
            'total_poin' => (int) \App\Models\TransaksiPoin::where('thajaran', $tahunAjaran)
                ->where('poinp', '>', 0)
                ->when($request->filled('siswa_id'), fn($q) => $q->where('siswa_id', $request->siswa_id))
                ->sum('poinp'),
        ];

        $pelanggaran = $query->paginate(20)->withQueryString();
        $kelas = Kelas::orderBy('nama_kelas')->get();
        $siswa = Siswa::with('kelas')->orderBy('nama_lengkap')->get();

        return view('admin.pelanggaran.index', compact('pelanggaran', 'kelas', 'siswa', 'tahunAjaran', 'stats'));
    }


    public function create(Request $request): View
    {
        $tahunAjaran = $request->get('tahun_ajaran', $this->tatibPoin->tahunAjaranAktif());
        $siswa = Siswa::with('kelas')->orderBy('nama_lengkap')->get();
        $subPasal = $this->tatibPoin->subPasalOptions('pelanggaran', $tahunAjaran);

        return view('admin.pelanggaran.create', compact('siswa', 'subPasal', 'tahunAjaran'));
    }

    public function store(PelanggaranStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Validasi tambahan khusus notifikasi WA manual (opsional, di luar FormRequest)
        // $request->validate([
        //     'kirim_wa' => 'nullable|boolean',
        //     'nomor_wa' => ['nullable', 'required_if:kirim_wa,1', 'regex:/^(08|628)[0-9]{8,12}$/'],
        // ], [
        //     'nomor_wa.required_if' => 'Nomor HP wajib diisi jika notifikasi WhatsApp diaktifkan.',
        //     'nomor_wa.regex'       => 'Format nomor HP tidak valid (contoh: 0812xxx atau 628xxx).',
        // ]);

        // ── Validasi duplikasi: cek apakah siswa sudah punya pelanggaran
        //    dengan pasal yang sama pada tanggal yang sama ──────────────────
        // Guard: hanya jalankan jika semua field yang diperlukan ada dan valid
        // (FormRequest sudah memvalidasi, tapi $validated hanya berisi field yang lulus —
        //  jika siswa_id/tanggal kosong/gagal exists, key tidak akan ada di array ini)
        if (
            ! blank($validated['siswa_id'] ?? null) &&
            ! blank($validated['tanggal'] ?? null) &&
            ! blank($validated['idpasal'] ?? null)
        ) {
            $tanggalInput = \Carbon\Carbon::parse($validated['tanggal'])->toDateString();
            $duplikat = Pelanggaran::where('siswa_id', $validated['siswa_id'])
                ->whereDate('tgl', $tanggalInput)
                ->where('idpasal', $validated['idpasal'])
                ->exists();

            if ($duplikat) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Pelanggaran dengan pasal yang sama untuk siswa ini pada tanggal tersebut sudah ada.');
            }
        }

        $result = DB::transaction(function () use ($validated) {
            $siswa = Siswa::with('kelas')->findOrFail($validated['siswa_id']);
            $tanggal = Carbon::parse($validated['tanggal']);
            $snapshot = $this->tatibPoin->snapshotSiswa($siswa);

            $pelanggaran = Pelanggaran::create([
                'siswa_id' => $siswa->id,
                'tgl' => $tanggal,
                'tahun_ajaran' => $validated['tahun_ajaran'],
                'deviceid' => $this->limit(auth()->user()?->device_id ?: 'web', 30),
                'noreg' => $snapshot['noreg'],
                'nama' => $snapshot['nama'],
                'kelas' => $snapshot['kelas'],
                'idpasal' => $validated['idpasal'] ?? null,
                'isi' => $this->limit($validated['isi'], 500),
                'poin' => (int) $validated['poin'],
                'pelapor' => $this->limit(auth()->user()?->name ?: 'system', 40),
                'created_by' => auth()->id(),
            ]);
            // PelanggaranObserver::created() otomatis sync ke tbltransaksi

            $notifCount = $this->tatibPoin->triggerAmbangNotifications($siswa, $validated['tahun_ajaran']);

            return compact('siswa', 'pelanggaran', 'notifCount', 'tanggal');
        });

        $message = 'Data pelanggaran berhasil ditambahkan.';
        if ($result['notifCount'] > 0) {
            $message .= " {$result['notifCount']} notifikasi ambang disiapkan.";
        }

        // Kirim notifikasi WA manual jika diaktifkan — dilakukan SETELAH transaksi DB selesai,
        // supaya koneksi DB tidak menggantung menunggu request HTTP ke gateway WA.
        if ($request->boolean('kirim_wa') && $request->filled('nomor_wa')) {
            $nomorHp = $request->input('nomor_wa');
            if (str_starts_with($nomorHp, '08')) {
                $nomorHp = '62' . substr($nomorHp, 1);
            }

            $pesan = \App\Services\WhatsappService::templateInfoPelanggaran(
                $result['siswa']->nama_lengkap,
                $result['siswa']->kelas?->nama_kelas ?? '-',
                $result['tanggal']->translatedFormat('d F Y'),
                $result['pelanggaran']->isi,
                (int) $result['pelanggaran']->poin
            );

            $sukses = app(\App\Services\WhatsappService::class)->send(
                nomorHp: $nomorHp,
                pesan: $pesan,
                jenis: 'pelanggaran_manual',
                referensiId: $result['pelanggaran']->getKey(),
            );

            $message .= $sukses
                ? " Notifikasi WhatsApp berhasil dikirim ke {$nomorHp}."
                : ' Namun notifikasi WhatsApp gagal dikirim, periksa log WA.';
        }

        return redirect()->route('admin.pelanggaran.index', ['tahun_ajaran' => $validated['tahun_ajaran']])
            ->with('success', $message);
    }

    public function edit(Pelanggaran $pelanggaran): View
    {
        $tahunAjaran = old('tahun_ajaran', $pelanggaran->tahun_ajaran ?: $this->tatibPoin->tahunAjaranAktif());
        $siswa = Siswa::with('kelas')->orderBy('nama_lengkap')->get();
        $subPasal = $this->tatibPoin->subPasalOptions('pelanggaran', $tahunAjaran, true);

        return view('admin.pelanggaran.edit', compact('pelanggaran', 'siswa', 'subPasal', 'tahunAjaran'));
    }

    public function update(PelanggaranUpdateRequest $request, Pelanggaran $pelanggaran): RedirectResponse
    {
        $validated = $request->validated();

        $notifCount = DB::transaction(function () use ($validated, $pelanggaran) {
            $siswa = Siswa::with('kelas')->findOrFail($validated['siswa_id']);
            $tanggalLama = $pelanggaran->tgl;
            $tanggal = Carbon::parse($validated['tanggal']);
            $snapshot = $this->tatibPoin->snapshotSiswa($siswa);

            // Hapus transaksi lama — observer::updated akan buat yang baru
            $this->tatibPoin->deleteTransaction('PN', $tanggalLama, $pelanggaran->getKey());

            $pelanggaran->update([
                'siswa_id' => $siswa->id,
                'tgl' => $tanggal,
                'tahun_ajaran' => $validated['tahun_ajaran'],
                'deviceid' => $this->limit(auth()->user()?->device_id ?: 'web', 30),
                'noreg' => $snapshot['noreg'],
                'nama' => $snapshot['nama'],
                'kelas' => $snapshot['kelas'],
                'idpasal' => $validated['idpasal'] ?? null,
                'isi' => $this->limit($validated['isi'], 500),
                'poin' => (int) $validated['poin'],
                'pelapor' => $this->limit(auth()->user()?->name ?: $pelanggaran->pelapor, 40),
                'created_by' => auth()->id() ?: $pelanggaran->created_by,
            ]);
            // Observer PelanggaranObserver::updated() otomatis membuat transaksi baru

            return $this->tatibPoin->triggerAmbangNotifications($siswa, $validated['tahun_ajaran']);
        });

        $message = 'Data pelanggaran berhasil diperbarui.';
        if ($notifCount > 0) {
            $message .= " {$notifCount} notifikasi ambang disiapkan.";
        }

        return redirect()->route('admin.pelanggaran.index', ['tahun_ajaran' => $validated['tahun_ajaran']])
            ->with('success', $message);
    }

    public function destroy(Pelanggaran $pelanggaran): RedirectResponse
    {
        DB::transaction(function () use ($pelanggaran) {
            $this->tatibPoin->deleteTransaction('PN', $pelanggaran->tgl, $pelanggaran->getKey());
            $pelanggaran->delete();
        });

        return redirect()->route('admin.pelanggaran.index')
            ->with('success', 'Data pelanggaran berhasil dihapus.');
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return redirect()->back()->with('error', 'Tidak ada data yang dipilih.');
        }

        $ids = array_map('intval', (array) $ids);
        $deleted = 0;

        DB::transaction(function () use ($ids, &$deleted) {
            Pelanggaran::whereIn('idpel', $ids)->each(function (Pelanggaran $pelanggaran) use (&$deleted) {
                $this->tatibPoin->deleteTransaction('PN', $pelanggaran->tgl, $pelanggaran->getKey());
                $pelanggaran->delete();
                $deleted++;
            });
        });

        return redirect()->back()->with('success', "{$deleted} data pelanggaran berhasil dihapus.");
    }

    private function limit(?string $value, int $limit): string
    {
        return Str::limit((string) $value, $limit, '');
    }
}
