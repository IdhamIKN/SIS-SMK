<?php

namespace App\Http\Controllers\Guru;

use App\Exports\JurnalMengajarExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\JurnalMengajarStoreRequest;
use App\Http\Requests\JurnalMengajarUpdateRequest;
use App\Models\GTK;
use App\Models\JadwalKBM;
use App\Models\Siswa;
use App\Models\TblJurnalPembelajaran;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Intervention\Image\ImageManager;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class JurnalMengajarController extends Controller
{
    public function index(Request $request): View
    {
        $lihatSemua = $this->canViewSemuaJurnal($request);
        $gtk = $lihatSemua ? null : $this->currentGtk($request);

        $tanggal = $request->get('tanggal');
        $kelas = $request->get('kelas');
        $pelajaran = $request->get('pelajaran');
        $gtkId = $request->get('gtk_id');

        $query = TblJurnalPembelajaran::query()
            ->orderByDesc('tanggal')
            ->orderByDesc('time');

        if (! $lihatSemua) {
            $query->where('kdguru', $gtk->kd_guru);
        }

        if ($lihatSemua && $gtkId) {
            $filterGtk = GTK::find($gtkId);

            if ($filterGtk?->kd_guru) {
                $query->where('kdguru', $filterGtk->kd_guru);
            }
        }

        if ($tanggal) {
            $query->whereDate('tanggal', $tanggal);
        }

        if ($kelas) {
            $query->where('kelas', $kelas);
        }

        if ($pelajaran) {
            $query->where('pelajaran', $pelajaran);
        }

        $jurnals = $query->paginate(15)->withQueryString();

        $filterOptions = $this->filterOptions($gtk, $lihatSemua);
        $userGtk = $request->user()->gtk;

        return view('guru.jurnal.index', [
            'jurnals' => $jurnals,
            'tanggal' => $tanggal,
            'kelasFilter' => $kelas,
            'pelajaranFilter' => $pelajaran,
            'gtkFilter' => $gtkId,
            'kelasOptions' => $filterOptions['kelas'],
            'pelajaranOptions' => $filterOptions['pelajaran'],
            'gtkList' => $filterOptions['gtkList'],
            'lihatSemuaJurnal' => $lihatSemua,
            'canCreateJurnal' => $request->user()->hasAnyRole(['gtk', 'superadmin']),
            'canManageAllJurnal' => $this->canManageSemuaJurnal($request),
            'currentKdGuru' => $userGtk?->kd_guru,
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->hasAnyRole(['gtk', 'superadmin']), 403);

        $gtk = $this->gtkUntukForm($request);
        $tanggal = $request->get('tanggal', now()->toDateString());
        $jadwal = $gtk ? $this->jadwalUntukTanggal($gtk, $tanggal) : collect();
        $sudahTerisi = $gtk ? $this->jurnalTanggalByKey($gtk, $tanggal) : collect();

        return view('guru.jurnal.create', [
            'gtk' => $gtk,
            'tanggal' => $tanggal,
            'canSelectGtk' => $this->canManageSemuaJurnal($request),
            'gtkList' => $this->gtkList(),
            'selectedGtkId' => $gtk?->id,
            'jadwalOptions' => $this->jadwalPayload($jadwal, $sudahTerisi),
        ]);
    }

    public function store(JurnalMengajarStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $gtk = $this->gtkUntukSimpan($request, $validated);
        $jadwal = $this->validJadwal($gtk, $validated['tanggal'], (int) $validated['jadwal_kbm_id']);

        $this->ensureBelumAdaDuplikat($gtk, $jadwal, $validated['tanggal']);

        $siswaTidakHadir = $this->siswaTidakHadir($validated['namasiswa'] ?? [], $jadwal->kelas_id);
        $file = $this->uploadedBukti($request);
        $buktiPath = $file ? $this->storeBukti($file, $gtk) : null;

        TblJurnalPembelajaran::create($this->payloadJurnal(
            $gtk,
            $jadwal,
            $validated['tanggal'],
            $validated['deskripsi'],
            $siswaTidakHadir,
            $buktiPath
        ));

        return redirect()
            ->route('guru.jurnal-mengajar.index', ['tanggal' => $validated['tanggal']])
            ->with('success', 'Jurnal mengajar berhasil disimpan.');
    }

    public function edit(Request $request, TblJurnalPembelajaran $jurnal): View
    {
        abort_unless($request->user()->hasAnyRole(['gtk', 'superadmin']), 403);

        $gtk = $this->canManageSemuaJurnal($request)
            ? $this->gtkUntukJurnal($jurnal)
            : $this->currentGtk($request);

        $this->authorizeJurnal($request, $gtk, $jurnal);

        $tanggal = old('tanggal', $request->get('tanggal', optional($jurnal->tanggal)->toDateString() ?? now()->toDateString()));
        if ($this->canManageSemuaJurnal($request) && $request->filled('gtk_id')) {
            $gtk = GTK::findOrFail($request->integer('gtk_id'));
        }

        $jadwal = $this->jadwalUntukTanggal($gtk, $tanggal);
        $sudahTerisi = $this->jurnalTanggalByKey($gtk, $tanggal, $jurnal->id);
        $selectedSiswaIds = $this->selectedSiswaIds($jurnal);

        return view('guru.jurnal.edit', [
            'gtk' => $gtk,
            'jurnal' => $jurnal,
            'tanggal' => $tanggal,
            'canSelectGtk' => $this->canManageSemuaJurnal($request),
            'gtkList' => $this->gtkList(),
            'selectedGtkId' => old('gtk_id', $gtk->id),
            'selectedJadwalId' => old('jadwal_kbm_id', $jurnal->jadwal_kbm_id ?? $this->guessJadwalId($jadwal, $jurnal)),
            'selectedSiswaIds' => old('namasiswa', $selectedSiswaIds),
            'jadwalOptions' => $this->jadwalPayload($jadwal, $sudahTerisi),
        ]);
    }

    public function update(JurnalMengajarUpdateRequest $request, TblJurnalPembelajaran $jurnal): RedirectResponse
    {
        abort_unless($request->user()->hasAnyRole(['gtk', 'superadmin']), 403);

        $validated = $request->validated();
        $gtk = $this->gtkUntukSimpan($request, $validated);
        $this->authorizeJurnal($request, $gtk, $jurnal);

        $jadwal = $this->validJadwal($gtk, $validated['tanggal'], (int) $validated['jadwal_kbm_id']);

        $this->ensureBelumAdaDuplikat($gtk, $jadwal, $validated['tanggal'], $jurnal->id);

        $siswaTidakHadir = $this->siswaTidakHadir($validated['namasiswa'] ?? [], $jadwal->kelas_id);
        $buktiPath = $jurnal->bukti;

        if ($file = $this->uploadedBukti($request)) {
            $buktiPath = $this->storeBukti($file, $gtk);
            $this->deleteBukti($jurnal->bukti);
        }

        $jurnal->update($this->payloadJurnal(
            $gtk,
            $jadwal,
            $validated['tanggal'],
            $validated['deskripsi'],
            $siswaTidakHadir,
            $buktiPath
        ));

        return redirect()
            ->route('guru.jurnal-mengajar.index', ['tanggal' => $validated['tanggal']])
            ->with('success', 'Jurnal mengajar berhasil diperbarui.');
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        abort_unless($request->user()->hasAnyRole(['gtk', 'superadmin', 'waka', 'kepala_sekolah']), 403);

        $request->validate([
            'tanggal_mulai'  => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'kelas'          => 'nullable|string|max:100',
            'pelajaran'      => 'nullable|string|max:150',
            'gtk_id'         => 'nullable|integer|exists:gtks,id',
        ]);

        $lihatSemua = $this->canViewSemuaJurnal($request);
        $kdGuru     = $lihatSemua ? null : $this->currentGtk($request)->kd_guru;

        $fileName = 'jurnal-mengajar_'
            . $request->tanggal_mulai . '_sd_' . $request->tanggal_selesai
            . '.xlsx';

        return Excel::download(
            new JurnalMengajarExport(
                tanggalMulai: $request->tanggal_mulai,
                tanggalSelesai: $request->tanggal_selesai,
                kelas: $request->kelas ?: null,
                pelajaran: $request->pelajaran ?: null,
                gtkId: $request->integer('gtk_id') ?: null,
                kdGuru: $kdGuru,
            ),
            $fileName
        );
    }

    public function exportPdf(Request $request): View|Response
    {
        abort_unless($request->user()->hasAnyRole(['gtk', 'superadmin', 'waka', 'kepala_sekolah']), 403);

        $request->validate([
            'tanggal_mulai'  => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'kelas'          => 'nullable|string|max:100',
            'pelajaran'      => 'nullable|string|max:150',
            'gtk_id'         => 'nullable|integer|exists:gtks,id',
        ]);

        $lihatSemua = $this->canViewSemuaJurnal($request);
        $kdGuru     = $lihatSemua ? null : $this->currentGtk($request)->kd_guru;

        $query = TblJurnalPembelajaran::query()
            ->whereBetween('tanggal', [$request->tanggal_mulai, $request->tanggal_selesai])
            ->orderBy('tanggal')
            ->orderBy('kelas')
            ->orderBy('jamke');

        if ($kdGuru) {
            $query->where('kdguru', $kdGuru);
        } elseif ($request->filled('gtk_id')) {
            $gtk = GTK::find($request->integer('gtk_id'));
            if ($gtk?->kd_guru) {
                $query->where('kdguru', $gtk->kd_guru);
            }
        }

        if ($request->filled('kelas')) {
            $query->where('kelas', $request->kelas);
        }

        if ($request->filled('pelajaran')) {
            $query->where('pelajaran', $request->pelajaran);
        }

        $jurnals = $query->get();

        // Label filter untuk judul
        $filterGuru  = null;
        if ($lihatSemua && $request->filled('gtk_id')) {
            $filterGuru = GTK::find($request->integer('gtk_id'))?->nama_lengkap;
        } elseif (! $lihatSemua) {
            $filterGuru = $this->currentGtk($request)->nama_lengkap;
        }

        // Logo
        $logoSmkPath   = public_path('images/logo/smk.png');
        $logoJatimPath = public_path('images/logo/jatim.png');
        if (! file_exists($logoSmkPath))   $logoSmkPath   = resource_path('views/pdf/logo/smk.png');
        if (! file_exists($logoJatimPath)) $logoJatimPath = resource_path('views/pdf/logo/jatim.png');
        $logoSmk   = file_exists($logoSmkPath)   ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoSmkPath))   : null;
        $logoJatim = file_exists($logoJatimPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoJatimPath)) : null;

        $data = [
            'jurnals'        => $jurnals,
            'tanggalMulai'   => $request->tanggal_mulai,
            'tanggalSelesai' => $request->tanggal_selesai,
            'filterGuru'     => $filterGuru,
            'filterKelas'    => $request->kelas ?: null,
            'filterMapel'    => $request->pelajaran ?: null,
            'cetakUser'      => $request->user()->name,
            'logoSmk'        => $logoSmk,
            'logoJatim'      => $logoJatim,
            'printFallback'  => true,
        ];

        return view('guru.jurnal.cetak-pdf', $data);
    }

    public function destroy(Request $request, TblJurnalPembelajaran $jurnal): RedirectResponse
    {
        abort_unless($request->user()->hasAnyRole(['gtk', 'superadmin']), 403);

        $gtk = $this->canManageSemuaJurnal($request)
            ? $this->gtkUntukJurnal($jurnal)
            : $this->currentGtk($request);

        $this->authorizeJurnal($request, $gtk, $jurnal);

        $tanggal = optional($jurnal->tanggal)->toDateString();

        $this->deleteBukti($jurnal->bukti);
        $jurnal->delete();

        return redirect()
            ->route('guru.jurnal-mengajar.index', array_filter(['tanggal' => $tanggal]))
            ->with('success', 'Jurnal mengajar berhasil dihapus.');
    }

    private function currentGtk(Request $request): GTK
    {
        $gtk = $request->user()->gtk;

        abort_unless($gtk && $gtk->kd_guru, 403, 'Data GTK untuk akun ini belum lengkap.');

        return $gtk;
    }

    private function jadwalUntukTanggal(GTK $gtk, string $tanggal): Collection
    {
        return JadwalKBM::query()
            ->with(['kelas', 'mataPelajaran'])
            ->where('gtk_id', $gtk->id)
            ->where('hari', $this->namaHari($tanggal))
            ->orderBy('jam_mulai')
            ->orderBy('jam_ke')
            ->get();
    }

    private function validJadwal(GTK $gtk, string $tanggal, int $jadwalId): JadwalKBM
    {
        $jadwal = JadwalKBM::query()
            ->with(['kelas', 'mataPelajaran'])
            ->where('id', $jadwalId)
            ->where('gtk_id', $gtk->id)
            ->where('hari', $this->namaHari($tanggal))
            ->first();

        if (! $jadwal) {
            throw ValidationException::withMessages([
                'jadwal_kbm_id' => 'Jadwal yang dipilih tidak sesuai dengan guru atau tanggal jurnal.',
            ]);
        }

        return $jadwal;
    }

    private function ensureBelumAdaDuplikat(GTK $gtk, JadwalKBM $jadwal, string $tanggal, ?int $ignoreId = null): void
    {
        $kelas = $jadwal->kelas?->nama_kelas;
        $pelajaran = $this->namaPelajaran($jadwal);

        $exists = TblJurnalPembelajaran::query()
            ->where('kdguru', $gtk->kd_guru)
            ->whereDate('tanggal', $tanggal)
            ->where('kelas', $kelas)
            ->where('pelajaran', $pelajaran)
            ->when($ignoreId, fn($query) => $query->whereKeyNot($ignoreId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'jadwal_kbm_id' => 'Jurnal untuk tanggal, kelas, dan mata pelajaran ini sudah ada.',
            ]);
        }
    }

    private function payloadJurnal(
        GTK $gtk,
        JadwalKBM $jadwal,
        string $tanggal,
        string $deskripsi,
        Collection $siswaTidakHadir,
        ?string $buktiPath
    ): array {
        $totalSiswa = $this->totalSiswaKelas($jadwal->kelas_id);
        $jumlahTidakHadir = $siswaTidakHadir->count();

        $payload = [
            'tipe' => 'Luring',
            'tanggal' => $tanggal,
            'kdguru' => $gtk->kd_guru,
            'kelas' => $jadwal->kelas?->nama_kelas,
            'pelajaran' => $this->namaPelajaran($jadwal),
            'jamke' => (string) $jadwal->jam_ke,
            'deskripsi' => $deskripsi,
            'guru' => $gtk->nama_lengkap,
            'siswahadir' => max($totalSiswa - $jumlahTidakHadir, 0),
            'siswatdkhadir' => $jumlahTidakHadir,
            'namasiswa' => $siswaTidakHadir->pluck('nama_lengkap')->implode(', '),
            'bukti' => $buktiPath,
        ];

        if (Schema::hasColumn('tbljurnalpembelajaran', 'jadwal_kbm_id')) {
            $payload['jadwal_kbm_id'] = $jadwal->id;
        }

        if (Schema::hasColumn('tbljurnalpembelajaran', 'jam_mulai')) {
            $payload['jam_mulai'] = $this->formatJam($jadwal->jam_mulai, true);
        }

        if (Schema::hasColumn('tbljurnalpembelajaran', 'jam_selesai')) {
            $payload['jam_selesai'] = $this->formatJam($jadwal->jam_selesai, true);
        }

        return $payload;
    }

    private function siswaTidakHadir(array $siswaIds, int $kelasId): Collection
    {
        $ids = collect($siswaIds)->filter()->map(fn($id) => (int) $id)->unique()->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        $siswas = Siswa::query()
            ->where('kelas_id', $kelasId)
            ->whereIn('id', $ids)
            ->orderBy('nama_lengkap')
            ->get(['id', 'nama_lengkap']);

        if ($siswas->count() !== $ids->count()) {
            throw ValidationException::withMessages([
                'namasiswa' => 'Siswa tidak hadir harus berasal dari kelas pada jadwal yang dipilih.',
            ]);
        }

        return $siswas;
    }

    private function totalSiswaKelas(int $kelasId): int
    {
        return Siswa::query()
            ->where('kelas_id', $kelasId)
            ->where(function ($query) {
                $query->whereNull('academic_status')
                    ->orWhere('academic_status', 'active');
            })
            ->count();
    }

    private function uploadedBukti(Request $request)
    {
        return $request->file('bukti_kamera') ?: $request->file('bukti_file');
    }

    private function storeBukti($file, GTK $gtk): string
    {
        $dir = 'jurnal-mengajar/' . date('Y/m');
        Storage::disk('public')->makeDirectory($dir);

        $baseName = time() . '_' . $gtk->kd_guru . '_' . Str::random(8);

        if ($file->getMimeType() === 'application/pdf') {
            $path = $dir . '/' . $baseName . '.pdf';
            Storage::disk('public')->putFileAs($dir, $file, basename($path));

            return $path;
        }

        $image = ImageManager::gd()->read($file->getRealPath());
        $encoded = $image->scale(width: 1600)->toWebp(75);
        $path = $dir . '/' . $baseName . '.webp';

        Storage::disk('public')->put($path, (string) $encoded);

        return $path;
    }

    private function deleteBukti(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    private function authorizeJurnal(Request $request, GTK $gtk, TblJurnalPembelajaran $jurnal): void
    {
        if ($this->canManageSemuaJurnal($request)) {
            return;
        }

        abort_unless($jurnal->kdguru === $gtk->kd_guru, 403, 'Anda tidak memiliki akses ke jurnal ini.');
    }

    private function jadwalPayload(Collection $jadwal, Collection $sudahTerisi): array
    {
        return $jadwal->map(function (JadwalKBM $item) use ($sudahTerisi) {
            $key = $this->keyJurnal($item->kelas?->nama_kelas, $this->namaPelajaran($item));
            $siswa = $this->siswaUntukKelas($item->kelas_id);

            return [
                'id' => $item->id,
                'kelas' => $item->kelas?->nama_kelas ?? '-',
                'pelajaran' => $this->namaPelajaran($item),
                'jam_ke' => $item->jam_ke,
                'jam_mulai' => $this->formatJam($item->jam_mulai),
                'jam_selesai' => $this->formatJam($item->jam_selesai),
                'total_siswa' => $siswa->count(),
                'jurnal_id' => $sudahTerisi->get($key)?->id,
                'siswa' => $siswa->map(fn(Siswa $siswa) => [
                    'id' => $siswa->id,
                    'nama' => $siswa->nama_lengkap,
                ])->values()->all(),
            ];
        })->values()->all();
    }

    private function jurnalTanggalByKey(GTK $gtk, string $tanggal, ?int $ignoreId = null): Collection
    {
        return TblJurnalPembelajaran::query()
            ->where('kdguru', $gtk->kd_guru)
            ->whereDate('tanggal', $tanggal)
            ->when($ignoreId, fn($query) => $query->whereKeyNot($ignoreId))
            ->get()
            ->keyBy(fn(TblJurnalPembelajaran $jurnal) => $this->keyJurnal($jurnal->kelas, $jurnal->pelajaran));
    }

    private function selectedSiswaIds(TblJurnalPembelajaran $jurnal): array
    {
        $names = collect(explode(',', (string) $jurnal->namasiswa))
            ->map(fn($nama) => trim($nama))
            ->filter()
            ->values();

        if ($names->isEmpty()) {
            return [];
        }

        return Siswa::query()
            ->whereIn('nama_lengkap', $names)
            ->pluck('id')
            ->map(fn($id) => (string) $id)
            ->all();
    }

    private function guessJadwalId(Collection $jadwal, TblJurnalPembelajaran $jurnal): ?int
    {
        return $jadwal
            ->first(fn(JadwalKBM $item) => $this->keyJurnal($item->kelas?->nama_kelas, $this->namaPelajaran($item)) === $this->keyJurnal($jurnal->kelas, $jurnal->pelajaran))
            ?->id;
    }

    private function filterOptions(?GTK $gtk, bool $lihatSemua = false): array
    {
        if ($lihatSemua) {
            return [
                'kelas' => TblJurnalPembelajaran::query()->whereNotNull('kelas')->distinct()->orderBy('kelas')->pluck('kelas'),
                'pelajaran' => TblJurnalPembelajaran::query()->whereNotNull('pelajaran')->distinct()->orderBy('pelajaran')->pluck('pelajaran'),
                'gtkList' => $this->gtkList(),
            ];
        }

        $jadwal = JadwalKBM::query()
            ->with(['kelas', 'mataPelajaran'])
            ->where('gtk_id', $gtk->id)
            ->get();

        return [
            'kelas' => $jadwal->pluck('kelas.nama_kelas')->filter()->unique()->sort()->values(),
            'pelajaran' => $jadwal->map(fn(JadwalKBM $item) => $this->namaPelajaran($item))->filter()->unique()->sort()->values(),
            'gtkList' => collect(),
        ];
    }

    private function gtkUntukForm(Request $request): ?GTK
    {
        if ($this->canManageSemuaJurnal($request)) {
            return $request->filled('gtk_id')
                ? GTK::findOrFail($request->integer('gtk_id'))
                : null;
        }

        return $this->currentGtk($request);
    }

    private function gtkUntukSimpan(Request $request, array $validated): GTK
    {
        if ($this->canManageSemuaJurnal($request)) {
            $gtkId = (int) ($validated['gtk_id'] ?? 0);

            if (! $gtkId) {
                throw ValidationException::withMessages([
                    'gtk_id' => 'Guru wajib dipilih.',
                ]);
            }

            $gtk = GTK::find($gtkId);

            if (! $gtk?->kd_guru) {
                throw ValidationException::withMessages([
                    'gtk_id' => 'Data kode guru untuk guru yang dipilih belum lengkap.',
                ]);
            }

            return $gtk;
        }

        return $this->currentGtk($request);
    }

    private function gtkUntukJurnal(TblJurnalPembelajaran $jurnal): GTK
    {
        $gtk = GTK::where('kd_guru', $jurnal->kdguru)->first();

        abort_unless($gtk, 404, 'Data GTK pemilik jurnal tidak ditemukan.');

        return $gtk;
    }

    private function gtkList(): Collection
    {
        return GTK::query()
            ->whereNotNull('kd_guru')
            ->orderBy('nama_lengkap')
            ->get(['id', 'kd_guru', 'nama_lengkap']);
    }

    private function siswaUntukKelas(?int $kelasId): Collection
    {
        if (! $kelasId) {
            return collect();
        }

        return Siswa::query()
            ->where('kelas_id', $kelasId)
            ->where(function ($query) {
                $query->whereNull('academic_status')
                    ->orWhere('academic_status', 'active');
            })
            ->orderBy('nama_lengkap')
            ->get(['id', 'nama_lengkap']);
    }

    private function canViewSemuaJurnal(Request $request): bool
    {
        return $request->user()->hasAnyRole(['superadmin', 'waka', 'kepala_sekolah']);
    }

    private function canManageSemuaJurnal(Request $request): bool
    {
        return $request->user()->hasRole('superadmin');
    }

    private function namaPelajaran(JadwalKBM $jadwal): string
    {
        return $jadwal->mataPelajaran?->nama_mapel ?: $jadwal->mata_pelajaran;
    }

    private function keyJurnal(?string $kelas, ?string $pelajaran): string
    {
        return Str::lower(trim((string) $kelas)) . '|' . Str::lower(trim((string) $pelajaran));
    }

    private function namaHari(string $tanggal): string
    {
        $hari = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];

        return $hari[Carbon::parse($tanggal)->dayOfWeek];
    }

    private function formatJam($value, bool $withSeconds = false): ?string
    {
        if (! $value) {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value->format($withSeconds ? 'H:i:s' : 'H:i');
        }

        return Carbon::parse($value)->format($withSeconds ? 'H:i:s' : 'H:i');
    }
}
