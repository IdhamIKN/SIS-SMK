<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GTK;
use App\Models\Siswa;
use App\Models\SuratPanggilan;
use App\Models\TransaksiPoin;
use App\Services\TatibPoinService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Support\Facades\URL;
use Barryvdh\DomPDF\Facade\Pdf;

class SuratPanggilanController extends Controller
{
    public function __construct(
        private readonly TatibPoinService $tatibPoin
    ) {}

    public function index(Request $request): View
    {
        $tahunAjaran = $request->get('tahun_ajaran', $this->tatibPoin->tahunAjaranAktif());

        $query = SuratPanggilan::with(['siswa.kelas', 'gtk', 'pembuat'])
            ->when($tahunAjaran, fn($q) => $q->where('tahun_ajaran', $tahunAjaran))
            ->when($request->filled('siswa_id'), fn($q) => $q->where('siswa_id', $request->siswa_id))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->whereHas('siswa', fn($sq) => $sq
                    ->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%"));
            })
            ->latest();

        $suratPanggilan = $query->paginate(20)->withQueryString();
        $tahunList = SuratPanggilan::select('tahun_ajaran')
            ->distinct()
            ->orderByDesc('tahun_ajaran')
            ->pluck('tahun_ajaran');

        return view('admin.surat_panggilan.index', compact(
            'suratPanggilan',
            'tahunAjaran',
            'tahunList'
        ));
    }

    // public function create(Request $request): View
    // {
    //     $tahunAjaran = $request->get('tahun_ajaran', $this->tatibPoin->tahunAjaranAktif());
    //     $siswa = null;

    //     // Jika siswa sudah dipilih sebelumnya (dari rekap poin)
    //     if ($request->filled('siswa_id')) {
    //         $siswa = Siswa::with('kelas')->find($request->siswa_id);
    //     }

    //     $gtk = GTK::where('status_aktif', true)
    //         ->orderBy('nama_lengkap')
    //         ->get();

    //     return view('admin.surat_panggilan.create', compact('tahunAjaran', 'gtk', 'siswa'));
    // }
    public function create(Request $request): View
    {
        $tahunAjaran = $request->get('tahun_ajaran', $this->tatibPoin->tahunAjaranAktif());
        $siswa = null;

        if ($request->filled('siswa_id')) {
            $siswa = Siswa::with('kelas')->find($request->siswa_id);
        }

        $gtk = GTK::where('status_aktif', true)->orderBy('nama_lengkap')->get();

        return view('admin.surat_panggilan.create', compact('tahunAjaran', 'gtk', 'siswa'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'siswa_id'       => 'required|exists:siswas,id',
            'gtk_id'         => 'required|exists:gtks,id',
            'nomor_surat'    => 'nullable|string|max:100',
            'panggilan_ke'   => 'required|integer|min:1|max:10',
            'hari'           => 'required|string|max:20',
            'tanggal_acara'  => 'required|date',
            'waktu'          => 'required|string|max:40',
            'lokasi'         => 'required|string|max:150',
            'menemui'        => 'nullable|string|max:100',
            'keperluan'      => 'nullable|string|max:1000',
            'dengan_materai' => 'boolean',
            'tanggal_surat'  => 'required|date',
            'tahun_ajaran'   => 'required|string|max:10',
        ]);

        $validated['dengan_materai'] = $request->boolean('dengan_materai');
        $validated['dibuat_oleh'] = auth()->id();

        $surat = SuratPanggilan::create($validated);

        return redirect()->route('admin.surat-panggilan.preview', $surat)
            ->with('success', 'Surat panggilan berhasil dibuat.');
    }

    public function preview(Request $request, SuratPanggilan $suratPanggilan): View
    {
        $surat = $suratPanggilan->load(['siswa.kelas', 'gtk']);
        $siswa = $surat->siswa;
        $tahunAjaran = $surat->tahun_ajaran;

        // Ambil histori poin pelanggaran
        $pelanggaran = TransaksiPoin::query()
            ->where('siswa_id', $siswa->id)
            ->where('thajaran', $tahunAjaran)
            ->where('poinp', '>', 0)
            ->orderBy('tanggal')
            ->get();

        // Ambil histori poin penghargaan
        $penghargaan = TransaksiPoin::query()
            ->where('siswa_id', $siswa->id)
            ->where('thajaran', $tahunAjaran)
            ->where('poinr', '>', 0)
            ->orderBy('tanggal')
            ->get();

        $totalPelanggaran = (int) $pelanggaran->sum('poinp');
        $totalPenghargaan = (int) $penghargaan->sum('poinr');

        // Path logo untuk base64 agar bisa dirender di PDF/print
        $logoSmkPath  = public_path('images/logo/smk.png');
        $logoJatimPath = public_path('images/logo/jatim.png');

        // Fallback ke resources/views/pdf/logo
        if (! file_exists($logoSmkPath)) {
            $logoSmkPath = resource_path('views/pdf/logo/smk.png');
        }
        if (! file_exists($logoJatimPath)) {
            $logoJatimPath = resource_path('views/pdf/logo/jatim.png');
        }

        $logoSmk   = file_exists($logoSmkPath)   ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoSmkPath))   : null;
        $logoJatim = file_exists($logoJatimPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoJatimPath)) : null;

        // Data sekolah dinamis (sama seperti pola cetak event)
        $sekolah         = sekolah_data();
        $namaSekolah     = $sekolah['nama']      ?? config('sekolah.nama',    'SMK NEGERI 5 MADIUN');
        $alamatSekolah   = $sekolah['alamat']    ?? config('sekolah.alamat',  'Jl. Merak No. 5 Madiun');
        $telpSekolah     = $sekolah['telp']      ?? config('sekolah.telepon', '-');
        $emailSekolah    = $sekolah['email']     ?? config('sekolah.email',   '-');
        $kabupaten       = $sekolah['kabupaten'] ?? 'Madiun';

        $jenis = 'pelanggaran';


        $dariTanggal = $pelanggaran->min('tanggal')
            ? Carbon::parse($pelanggaran->min('tanggal'))
            : now();

        $sampaiTanggal = $pelanggaran->max('tanggal')
            ? Carbon::parse($pelanggaran->max('tanggal'))
            : now();
        $cetakUser = auth()->user()->name ?? auth()->user()->username ?? '-';
        $tanggalCetak = now();
        $dataPerKelas = collect();

        return view('pdf.sp.surat', compact(
            'surat',
            'siswa',
            'tahunAjaran',
            'pelanggaran',
            'penghargaan',
            'totalPelanggaran',
            'totalPenghargaan',
            'logoSmk',
            'logoJatim',
            'namaSekolah',
            'alamatSekolah',
            'telpSekolah',
            'emailSekolah',
            'kabupaten',
            'jenis',
            'dariTanggal',
            'sampaiTanggal',
            'cetakUser',
            'tanggalCetak',
            'dataPerKelas'
        ));
    }

    /**
     * API: Ambil ringkasan pelanggaran & penghargaan siswa untuk preview modal.
     */
    public function previewPoin(Request $request)
    {
        $request->validate([
            'siswa_id'     => 'required|exists:siswas,id',
            'tahun_ajaran' => 'required|string',
        ]);

        $siswa       = Siswa::with('kelas')->findOrFail($request->siswa_id);
        $tahunAjaran = $request->tahun_ajaran;

        $pelanggaran = TransaksiPoin::with('subPasal')
            ->where('siswa_id', $siswa->id)
            ->where('thajaran', $tahunAjaran)
            ->where('poinp', '>', 0)
            ->orderBy('tanggal')
            ->get()
            ->map(fn($item) => [
                'tanggal' => $item->tanggal?->format('d/m/Y'),
                'uraian'  => $item->subPasal?->pasal ?? $item->ket ?? '-',
                'ket'     => $item->ket ?? '-',
                'poin'    => (int) $item->poinp,
            ]);

        $penghargaan = TransaksiPoin::with('subPasal')
            ->where('siswa_id', $siswa->id)
            ->where('thajaran', $tahunAjaran)
            ->where('poinr', '>', 0)
            ->orderBy('tanggal')
            ->get()
            ->map(fn($item) => [
                'tanggal' => $item->tanggal?->format('d/m/Y'),
                'uraian'  => $item->subPasal?->pasal ?? $item->ket ?? '-',
                'ket'     => $item->ket ?? '-',
                'poin'    => (int) $item->poinr,
            ]);

        return response()->json([
            'siswa' => [
                'nama'  => $siswa->nama_lengkap,
                'nis'   => $siswa->nis ?? '-',
                'kelas' => $siswa->kelas?->nama_kelas ?? '-',
            ],
            'tahun_ajaran'      => $tahunAjaran,
            'pelanggaran'       => $pelanggaran,
            'penghargaan'       => $penghargaan,
            'total_pelanggaran' => (int) $pelanggaran->sum('poin'),
            'total_penghargaan' => (int) $penghargaan->sum('poin'),
        ]);
    }

    /**
     * API: Hitung panggilan ke-berapa untuk siswa di tahun ajaran aktif.
     * Hanya menghitung surat yang dibuat di tahun ajaran yang sama.
     */
    public function panggilanKe(Request $request)
    {
        $request->validate([
            'siswa_id'     => 'required|exists:siswas,id',
            'tahun_ajaran' => 'required|string',
        ]);

        $jumlah = SuratPanggilan::where('siswa_id', $request->siswa_id)
            ->where('tahun_ajaran', $request->tahun_ajaran)
            ->count();

        return response()->json([
            'panggilan_ke' => $jumlah + 1,
        ]);
    }

    public function destroy(SuratPanggilan $suratPanggilan): RedirectResponse
    {
        $suratPanggilan->delete();

        return redirect()->route('admin.surat-panggilan.index')
            ->with('success', 'Surat panggilan berhasil dihapus.');
    }
    /**
     * API: Ambil data nomor HP ortu dari siswa (untuk pre-fill modal WA).
     */
    public function nomorOrtu(Request $request)
    {
        $request->validate(['siswa_id' => 'required|exists:siswas,id']);

        $siswa = Siswa::findOrFail($request->siswa_id);

        return response()->json([
            'no_hp_ortu1' => $siswa->no_hp_ortu1,
            'no_hp_ortu2' => $siswa->no_hp_ortu2,
            'nama_ortu1'  => $siswa->nama_ortu1,
            'nama_ortu2'  => $siswa->nama_ortu2,
            'nama_wali'   => $siswa->nama_wali,
        ]);
    }

    // --------------------------------------------------------
    // Kirim WA Manual
    // --------------------------------------------------------
    public function kirimWa(Request $request, SuratPanggilan $suratPanggilan): RedirectResponse
    {
        $validated = $request->validate([
            'nomor_hp' => ['required', 'string', 'regex:/^(08|628)[0-9]{8,12}$/'],
        ], [
            'nomor_hp.required' => 'Nomor HP wajib diisi.',
            'nomor_hp.regex'    => 'Format nomor HP tidak valid (contoh: 0812xxx atau 628xxx).',
        ]);

        $surat = $suratPanggilan->load(['siswa.kelas', 'gtk']);
        $siswa = $surat->siswa;

        // Normalisasi: 08xxx → 628xxx
        $nomorHp = $validated['nomor_hp'];
        if (str_starts_with($nomorHp, '08')) {
            $nomorHp = '62' . substr($nomorHp, 1);
        }

        // $linkPdf = URL::signedRoute('surat-panggilan.cetak-publik', [
        //     'suratPanggilan' => $surat->id,
        // ], now()->addDays(7));
        $linkPdf = URL::signedRoute('admin.surat-panggilan.cetak-publik', [
            'suratPanggilan' => $surat->id,
        ], now()->addDays(7));

        $pesan = $this->buildPesanWa($surat, $siswa, $linkPdf);

        $sukses = app(\App\Services\WhatsappService::class)->send(
            nomorHp: $nomorHp,
            pesan: $pesan,
            jenis: 'surat_panggilan',
            referensiId: $surat->id,
        );

        if ($sukses) {
            $surat->update([
                'wa_sent_at'      => now(),
                'wa_nomor_tujuan' => $nomorHp,
            ]);
            return back()->with('success', "Notifikasi WhatsApp berhasil dikirim ke {$nomorHp}.");
        }

        return back()->with('error', 'Gagal mengirim WhatsApp. Periksa log WA untuk detail.');
    }

    // --------------------------------------------------------
    // Preview Publik via Signed URL (tanpa auth)
    // --------------------------------------------------------
    // public function cetakPublik(SuratPanggilan $suratPanggilan): View
    // {
    //     // Delegate ke method preview yang sudah ada
    //     // Buat request dummy agar method preview bisa dipanggil
    //     return $this->preview(request(), $suratPanggilan);
    // }

    public function cetakPublik(SuratPanggilan $suratPanggilan)
    {
        // Ambil data sama persis seperti method preview()
        $surat = $suratPanggilan->load(['siswa.kelas', 'gtk']);
        $siswa = $surat->siswa;
        $tahunAjaran = $surat->tahun_ajaran;

        $pelanggaran = TransaksiPoin::query()
            ->where('siswa_id', $siswa->id)
            ->where('thajaran', $tahunAjaran)
            ->where('poinp', '>', 0)
            ->orderBy('tanggal')
            ->get();

        $penghargaan = TransaksiPoin::query()
            ->where('siswa_id', $siswa->id)
            ->where('thajaran', $tahunAjaran)
            ->where('poinr', '>', 0)
            ->orderBy('tanggal')
            ->get();

        $totalPelanggaran = (int) $pelanggaran->sum('poinp');
        $totalPenghargaan = (int) $penghargaan->sum('poinr');

        $logoSmkPath  = public_path('images/logo/smk.png');
        $logoJatimPath = public_path('images/logo/jatim.png');
        if (! file_exists($logoSmkPath))  $logoSmkPath  = resource_path('views/pdf/logo/smk.png');
        if (! file_exists($logoJatimPath)) $logoJatimPath = resource_path('views/pdf/logo/jatim.png');

        $logoSmk   = file_exists($logoSmkPath)   ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoSmkPath))   : null;
        $logoJatim = file_exists($logoJatimPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoJatimPath)) : null;

        $sekolah       = sekolah_data();
        $namaSekolah   = $sekolah['nama']      ?? config('sekolah.nama',    'SMK NEGERI 5 MADIUN');
        $alamatSekolah = $sekolah['alamat']    ?? config('sekolah.alamat',  'Jl. Merak No. 5 Madiun');
        $telpSekolah   = $sekolah['telp']      ?? config('sekolah.telepon', '-');
        $emailSekolah  = $sekolah['email']     ?? config('sekolah.email',   '-');
        $kabupaten     = $sekolah['kabupaten'] ?? 'Madiun';

        $pdf = Pdf::loadView('pdf.sp.surat_pdf', compact(
            'surat', 'siswa', 'tahunAjaran',
            'pelanggaran', 'penghargaan',
            'totalPelanggaran', 'totalPenghargaan',
            'logoSmk', 'logoJatim',
            'namaSekolah', 'alamatSekolah', 'telpSekolah', 'emailSekolah', 'kabupaten'
        ))->setPaper('a4', 'portrait');

        $namaFile = 'Surat-Panggilan-' . str_replace(' ', '-', $siswa->nama_lengkap) . '.pdf';

        return $pdf->download($namaFile);
        // atau ->stream($namaFile) kalau mau tampil di browser dulu, bukan langsung force-download
    }

    // --------------------------------------------------------
    // Helper: Build pesan WA
    // --------------------------------------------------------
    private function buildPesanWa(SuratPanggilan $surat, $siswa, string $linkPdf): string
    {
        $sekolah      = config('sekolah.nama', 'SMKN 5 Madiun');
        $namaKelas    = $siswa?->kelas?->nama_kelas ?? '-';
        $tanggalAcara = $surat->tanggal_acara?->translatedFormat('d F Y') ?? '-';
        $waktu        = $surat->waktu ?? '-';
        $lokasi       = $surat->lokasi ?? '-';
        $penanda      = $surat->gtk?->nama_lengkap ?? '-';

        return "Yth. Bapak/Ibu Orang Tua/Wali Siswa,\n\n"
            . "*SURAT PANGGILAN ORANG TUA/WALI*\n"
            . "{$sekolah}\n\n"
            . "Kepada Yth.\nOrang Tua/Wali dari:\n\n"
            . "📋 *Nama  :* {$siswa?->nama_lengkap}\n"
            . "🏫 *Kelas :* {$namaKelas}\n"
            . ($surat->panggilan_ke ? "📌 *Panggilan ke-:* {$surat->panggilan_ke}\n" : '')
            . "\nBersama surat ini kami mengundang Bapak/Ibu untuk hadir pada:\n\n"
            . "📅 *Hari/Tanggal :* {$surat->hari}, {$tanggalAcara}\n"
            . "⏰ *Waktu         :* {$waktu}\n"
            . "📍 *Tempat        :* {$lokasi}\n"
            . ($surat->keperluan ? "\n*Keperluan:*\n{$surat->keperluan}\n" : '')
            . "\nUntuk melihat surat panggilan lengkap, silakan klik tautan berikut:\n"
            . "🔗 {$linkPdf}\n"
            . "\n_(Tautan berlaku 7 hari)_\n\n"
            . "Atas perhatian dan kehadiran Bapak/Ibu, kami ucapkan terima kasih.\n\n"
            . "Hormat kami,\n{$penanda}\n"
            . "{$sekolah}\n\n"
            . "Hormat Kami,\n"
            . "SMKN 5 Madiun";
    }
}
