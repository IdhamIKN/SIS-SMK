<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\PasalTatibImport;
use App\Models\TblKategori;
use App\Models\TblPasal;
use App\Models\TblSubPasal;
use App\Services\TatibPoinService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class PasalController extends Controller
{
    public function __construct(
        private readonly TatibPoinService $tatibPoin
    ) {}

    public function index(Request $request): View
    {
        $jenis = $this->normalizeJenis($request->get('jenis', 'pelanggaran'));
        $idGroup = $this->idGroup($jenis);
        $tahunAjaran = $request->get('tahun_ajaran', $this->tatibPoin->tahunAjaranAktif());
        $supportsStatus = $this->supportsStatus();
        $search = trim((string) $request->get('search', ''));

        $query = TblPasal::query()
            ->with('kategori')
            ->whereHas('kategori', fn ($q) => $q->where('idgroup', $idGroup))
            ->when($request->filled('kategori'), fn ($q) => $q->where('idkategori', $request->kategori))
            ->when($supportsStatus && $request->filled('status'), function ($q) use ($request) {
                $q->where('status_aktif', $request->status === 'aktif');
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sq) use ($search) {
                    $sq->where('idpasal', 'like', "%{$search}%")
                        ->orWhereHas('kategori', fn ($kq) => $kq->where('kategori', 'like', "%{$search}%"))
                        ->orWhereHas('subPasal', fn ($spq) => $spq->where('pasal', 'like', "%{$search}%"));
                });
            })
            ->orderBy('idkategori')
            ->orderBy('urut')
            ->orderBy('idpasal');

        $statsBase = TblPasal::query();

        $stats = [
            'total' => (clone $statsBase)->count(),
            'aktif' => $supportsStatus ? (clone $statsBase)->where('status_aktif', true)->count() : (clone $statsBase)->count(),
            'nonaktif' => $supportsStatus ? (clone $statsBase)->where('status_aktif', false)->count() : 0,
        ];

        $pasal = $query->paginate(20)->withQueryString();
        $this->attachDetailTahun($pasal->getCollection(), $tahunAjaran);

        $kategori = $this->kategoriOptions($jenis);

        return view('admin.pasal.index', compact(
            'pasal',
            'kategori',
            'jenis',
            'tahunAjaran',
            'stats',
            'supportsStatus'
        ));
    }

    public function create(Request $request): View
    {
        $jenis = $this->normalizeJenis($request->get('jenis', 'pelanggaran'));
        $tahunAjaran = $request->get('tahun_ajaran', $this->tatibPoin->tahunAjaranAktif());
        $kategori = $this->kategoriOptions();

        return view('admin.pasal.create', compact('jenis', 'tahunAjaran', 'kategori'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedPasal($request, true);

        DB::transaction(function () use ($validated) {
            TblPasal::query()->create($this->pasalPayload($validated, true));
            $this->replaceSubPasal($validated);
        });

        return redirect()
            ->route('admin.pasal.index', [
                'jenis' => $validated['jenis'],
                'tahun_ajaran' => $validated['tahun_ajaran'],
            ])
            ->with('success', 'Pasal berhasil ditambahkan.');
    }

    public function edit(Request $request, TblPasal $pasal): View
    {
        $pasal->load('kategori');
        $jenis = $pasal->kategori?->idgroup === 'R' ? 'penghargaan' : 'pelanggaran';
        $tahunAjaran = $request->get('tahun_ajaran', $this->tatibPoin->tahunAjaranAktif());
        $detail = $this->detailForPasal($pasal->idpasal, $tahunAjaran);
        $kategori = $this->kategoriOptions();

        return view('admin.pasal.edit', compact('pasal', 'detail', 'jenis', 'tahunAjaran', 'kategori'));
    }

    public function update(Request $request, TblPasal $pasal): RedirectResponse
    {
        $validated = $this->validatedPasal($request, false);
        $validated['idpasal'] = $pasal->idpasal;

        DB::transaction(function () use ($pasal, $validated) {
            $pasal->update($this->pasalPayload($validated, false));
            $this->replaceSubPasal($validated);
        });

        return redirect()
            ->route('admin.pasal.index', [
                'jenis' => $validated['jenis'],
                'tahun_ajaran' => $validated['tahun_ajaran'],
            ])
            ->with('success', 'Pasal berhasil diperbarui.');
    }

    public function destroy(TblPasal $pasal): RedirectResponse
    {
        if ($this->isPasalUsed($pasal->idpasal)) {
            return back()->with('error', 'Pasal sudah pernah dipakai di transaksi. Nonaktifkan pasal jika tidak ingin digunakan lagi.');
        }

        DB::transaction(function () use ($pasal) {
            DB::table('tblsubpasal')->where('idpasal', $pasal->idpasal)->delete();
            $pasal->delete();
        });

        return redirect()->route('admin.pasal.index')
            ->with('success', 'Pasal berhasil dihapus.');
    }

    public function toggle(TblPasal $pasal): RedirectResponse
    {
        if (! $this->supportsStatus()) {
            return back()->with('error', 'Kolom status_aktif belum tersedia. Jalankan php artisan migrate terlebih dahulu.');
        }

        $pasal->update([
            'status_aktif' => ! (bool) ($pasal->status_aktif ?? true),
        ]);

        $status = $pasal->status_aktif ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Pasal {$pasal->idpasal} berhasil {$status}.");
    }

    public function import(Request $request): View
    {
        $jenis = $this->normalizeJenis($request->get('jenis', 'pelanggaran'));
        $tahunAjaran = $request->get('tahun_ajaran', $this->tatibPoin->tahunAjaranAktif());

        return view('admin.pasal.import', compact('jenis', 'tahunAjaran'));
    }

    public function importProcess(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'jenis' => ['required', Rule::in(['pelanggaran', 'penghargaan'])],
            'tahun_ajaran' => ['required', 'string', 'max:9'],
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:5120'],
        ]);

        $import = new PasalTatibImport(
            $validated['jenis'],
            $validated['tahun_ajaran'],
            $this->supportsStatus()
        );

        try {
            Excel::import($import, $request->file('file'));
        } catch (Throwable $exception) {
            return back()
                ->withInput()
                ->with('error', 'Import gagal: '.$exception->getMessage());
        }

        $summary = $import->summary();
        $message = "Import selesai. Baru: {$summary['created']}, diperbarui: {$summary['updated']}, dilewati: {$summary['skipped']}.";

        return redirect()
            ->route('admin.pasal.index', [
                'jenis' => $validated['jenis'],
                'tahun_ajaran' => $validated['tahun_ajaran'],
            ])
            ->with('success', $message)
            ->with('import_errors', $import->errors());
    }

    public function template(): StreamedResponse
    {
        $filename = 'template_import_pasal_tatib.csv';

        return response()->streamDownload(function () {
            $output = fopen('php://output', 'w');

            fputcsv($output, ['jenis', 'idkategori', 'idpasal', 'pasal', 'skormin', 'skormax', 'urut', 'tahun_ajaran', 'status_aktif']);
            fputcsv($output, ['pelanggaran', 'P1', 'J99', 'Contoh pasal pelanggaran', 10, 10, 99, $this->tatibPoin->tahunAjaranAktif(), 'aktif']);
            fputcsv($output, ['penghargaan', 'R1', 'A99', 'Contoh pasal penghargaan', 10, 10, 99, $this->tatibPoin->tahunAjaranAktif(), 'aktif']);

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function validatedPasal(Request $request, bool $isCreate): array
    {
        $rules = [
            'jenis' => ['required', Rule::in(['pelanggaran', 'penghargaan'])],
            'idkategori' => ['required', 'string', 'max:5', 'exists:tblkategori,idkategori'],
            'pasal' => ['required', 'string', 'max:500'],
            'skormin' => ['required', 'integer', 'min:0', 'max:999'],
            'skormax' => ['required', 'integer', 'min:0', 'max:999', 'gte:skormin'],
            'tahun_ajaran' => ['required', 'string', 'max:9'],
            'urut' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'status_aktif' => ['nullable', 'boolean'],
        ];

        if ($isCreate) {
            $rules['idpasal'] = ['required', 'string', 'max:5', 'regex:/^[A-Za-z0-9]+$/', Rule::unique('tblpasal', 'idpasal')];
        }

        $validated = $request->validate($rules, [
            'idpasal.regex' => 'Kode pasal hanya boleh berisi huruf dan angka.',
            'idpasal.unique' => 'Kode pasal sudah digunakan.',
            'skormax.gte' => 'Skor maksimum tidak boleh lebih kecil dari skor minimum.',
        ]);

        $validated['jenis'] = $this->normalizeJenis($validated['jenis']);
        $validated['idkategori'] = strtoupper(trim($validated['idkategori']));
        $validated['idpasal'] = $isCreate ? strtoupper(trim($validated['idpasal'])) : null;
        $validated['status_aktif'] = $request->boolean('status_aktif');
        $validated['urut'] = filled($validated['urut'] ?? null)
            ? (int) $validated['urut']
            : $this->nextUrut($validated['idkategori']);

        $this->assertKategoriMatchesJenis($validated['idkategori'], $validated['jenis']);

        return $validated;
    }

    private function pasalPayload(array $validated, bool $includeId): array
    {
        $payload = [
            'idkategori' => $validated['idkategori'],
            'urut' => $validated['urut'],
        ];

        if ($includeId) {
            $payload['idpasal'] = $validated['idpasal'];
        }

        if ($this->supportsStatus()) {
            $payload['status_aktif'] = $validated['status_aktif'];
        }

        return $payload;
    }

    private function replaceSubPasal(array $validated): void
    {
        DB::table('tblsubpasal')
            ->where('idpasal', $validated['idpasal'])
            ->where('thnajaran', $validated['tahun_ajaran'])
            ->delete();

        DB::table('tblsubpasal')->insert([
            'idpasal' => $validated['idpasal'],
            'pasal' => trim($validated['pasal']),
            'skormin' => (int) $validated['skormin'],
            'skormax' => (int) $validated['skormax'],
            'thnajaran' => $validated['tahun_ajaran'],
        ]);
    }

    private function detailForPasal(string $idPasal, string $tahunAjaran): ?TblSubPasal
    {
        return TblSubPasal::query()
            ->where('idpasal', $idPasal)
            ->where('thnajaran', $tahunAjaran)
            ->first()
            ?? TblSubPasal::query()
                ->where('idpasal', $idPasal)
                ->orderByDesc('thnajaran')
                ->first();
    }

    private function attachDetailTahun(Collection $items, string $tahunAjaran): void
    {
        $ids = $items->pluck('idpasal')->filter()->values();

        if ($ids->isEmpty()) {
            return;
        }

        $current = TblSubPasal::query()
            ->whereIn('idpasal', $ids)
            ->where('thnajaran', $tahunAjaran)
            ->get()
            ->groupBy('idpasal')
            ->map(fn (Collection $group) => $group->first());

        $missingIds = $ids->reject(fn ($id) => $current->has($id))->values();
        $fallback = collect();

        if ($missingIds->isNotEmpty()) {
            $fallback = TblSubPasal::query()
                ->whereIn('idpasal', $missingIds)
                ->orderByDesc('thnajaran')
                ->get()
                ->groupBy('idpasal')
                ->map(fn (Collection $group) => $group->first());
        }

        $items->each(function (TblPasal $item) use ($current, $fallback) {
            $item->setRelation('detailTahun', $current->get($item->idpasal) ?? $fallback->get($item->idpasal));
        });
    }

    private function kategoriOptions(?string $jenis = null): Collection
    {
        return TblKategori::query()
            ->when($jenis, fn ($q) => $q->where('idgroup', $this->idGroup($jenis)))
            ->orderBy('idgroup')
            ->orderBy('idkategori')
            ->get();
    }

    private function assertKategoriMatchesJenis(string $idKategori, string $jenis): void
    {
        $kategori = TblKategori::query()->where('idkategori', $idKategori)->first();

        if (! $kategori || $kategori->idgroup !== $this->idGroup($jenis)) {
            throw ValidationException::withMessages([
                'idkategori' => 'Kategori tidak sesuai dengan jenis pasal yang dipilih.',
            ]);
        }
    }

    private function normalizeJenis(?string $jenis): string
    {
        return $jenis === 'penghargaan' || strtoupper((string) $jenis) === 'R'
            ? 'penghargaan'
            : 'pelanggaran';
    }

    private function idGroup(string $jenis): string
    {
        return $jenis === 'penghargaan' ? 'R' : 'P';
    }

    private function nextUrut(string $idKategori): int
    {
        return ((int) TblPasal::query()->where('idkategori', $idKategori)->max('urut')) + 1;
    }

    private function supportsStatus(): bool
    {
        return Schema::hasColumn('tblpasal', 'status_aktif');
    }

    private function isPasalUsed(string $idPasal): bool
    {
        foreach ([['tblpelanggaran', 'idpasal'], ['tblpenghargaan', 'idpasal'], ['tbltransaksi', 'idpasal']] as [$table, $column]) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                if (DB::table($table)->where($column, $idPasal)->exists()) {
                    return true;
                }
            }
        }

        return false;
    }
}
