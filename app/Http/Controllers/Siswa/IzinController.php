<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\PengajuanIzin;
use App\Services\IzinService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IzinController extends Controller
{
    public function __construct(protected IzinService $izinService)
    {
    }

    public function index(Request $request)
    {
        $siswa = auth()->user()->siswa()->firstOrFail();
        $izin = $siswa->pengajuanIzin()
            ->with('verifier:id,name')
            ->latest()
            ->paginate(10);

        $pendingCount = $izin->where('status', 'diajukan')->count();
        $approvedCount = $izin->where('status', 'disetujui')->count();
        $rejectedCount = $izin->where('status', 'ditolak')->count();
        $allCount = $izin->total();

        return view('siswa.izin.index', compact('izin', 'pendingCount', 'approvedCount', 'rejectedCount', 'allCount'));
    }


    public function create()
    {
        return view('siswa.izin.create');
    }

    public function store(Request $request)
    {
        $siswa = auth()->user()->siswa()->firstOrFail();
        $validated = $this->izinService->validateRequest($request);

        $this->izinService->ensureTidakBentrokDenganAbsensi($siswa->id, $validated);

        $bukti = null;
        if ($request->hasFile('bukti')) {
            $bukti = $this->izinService->storeOptimizedBukti($request->file('bukti'), $siswa->id);
        }

        $data = $this->izinService->buildPayload($validated, $siswa->id, $bukti);

        $izin = $siswa->pengajuanIzin()->create($data);

        Log::channel('sis')->info('[PengajuanIzin] Siswa ajukan izin', [
            'izin_id' => $izin->id,
            'siswa_id' => $siswa->id,
            'jenis' => $izin->jenis,
            ...$this->izinService->resolveTanggalData($validated),
        ]);


        return redirect()->route('siswa.izin.index')
            ->with('success', 'Pengajuan izin berhasil diajukan. Menunggu persetujuan admin.');
    }

    public function show(PengajuanIzin $izin)
    {
        if ($izin->siswa_id !== auth()->user()->siswa_id) {
            abort(403);
        }

        return view('siswa.izin.show', compact('izin'));
    }

    public function edit(PengajuanIzin $izin)
    {
        if ($izin->siswa_id !== auth()->user()->siswa_id || $izin->status !== 'diajukan') {
            abort(403, 'Hanya bisa edit izin yang belum disetujui');
        }

        return view('siswa.izin.edit', compact('izin'));
    }

    public function update(Request $request, PengajuanIzin $izin)
    {
        if ($izin->siswa_id !== auth()->user()->siswa_id || $izin->status !== 'diajukan') {
            abort(403, 'Hanya bisa edit izin yang belum disetujui');
        }
        $validated = $this->izinService->validateRequest($request, $izin);

        $this->izinService->ensureTidakBentrokDenganAbsensi($izin->siswa_id, $validated);

        $oldBukti = $izin->bukti;
        $buktiPath = $izin->bukti;

        if ($request->hasFile('bukti')) {
            $buktiPath = $this->izinService->storeOptimizedBukti($request->file('bukti'), $izin->siswa_id);
        }

        $data = $this->izinService->buildPayload($validated, $izin->siswa_id, $buktiPath);

        $izin->update($data);

        // Delete old bukti if new uploaded
        if ($request->hasFile('bukti') && $oldBukti) {
            $this->izinService->deleteBukti($oldBukti);
        }

        return redirect()->route('siswa.izin.show', $izin)
            ->with('success', 'Pengajuan izin berhasil diupdate.');
    }

    public function destroy(PengajuanIzin $izin)
    {
        if ($izin->siswa_id !== auth()->user()->siswa_id || $izin->status !== 'diajukan') {
            abort(403, 'Hanya bisa hapus izin yang belum disetujui');
        }

        $this->izinService->deleteBukti($izin->bukti);

        $izin->delete();

        return redirect()->route('siswa.izin.index')
            ->with('success', 'Pengajuan izin berhasil dihapus.');
    }
}
