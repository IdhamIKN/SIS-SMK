<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\PengajuanIzin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class IzinController extends Controller
{
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
        $request->validate([
            'jenis' => 'required|in:izin_sakit,izin_pulang_cepat,izin_terlambat,izin_lainnya',
            'tanggal_izin' => 'required_if:jenis,izin_pulang_cepat,izin_terlambat|nullable|date|after_or_equal:today',
            'tanggal_mulai' => 'required_if:jenis,izin_sakit,izin_lainnya|nullable|date|after_or_equal:today',
            'tanggal_sampai' => 'required_if:jenis,izin_sakit,izin_lainnya|nullable|date|after_or_equal:tanggal_mulai',
            'alasan' => 'required|string|max:500',
            'bukti' => 'nullable|image|mimes:jpeg,jpg,png|max:10240',
        ]);

        $buktiPath = null;
        if ($request->hasFile('bukti')) {
            $file = $request->file('bukti');
            $image = \Intervention\Image\Facades\Image::make($file);

            // Resize & quality 70%
            $image->resize(1200, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })->encode('jpg', 70);

            $dir = 'izin/' . date('Y/m');
            Storage::disk('public')->makeDirectory($dir, 0755, true, false);
            $filename = time() . '_' . $siswa->id . '_bukti.jpg';
            $path = $dir . '/' . $filename;

            Storage::disk('public')->put($path, $image->stream()->__toString());
            $buktiPath = $path;
        }

        $siswa = auth()->user()->siswa()->firstOrFail();
        $data = [
            'siswa_id' => $siswa->id,
            'jenis' => $request->jenis,
            'alasan' => $request->alasan,
            'bukti' => $buktiPath,
        ];

        if ($request->filled('tanggal_izin')) {
            $data['tanggal_izin'] = $request->tanggal_izin;
        }
        if ($request->filled('tanggal_mulai')) {
            $data['tanggal_mulai'] = $request->tanggal_mulai;
        }
        if ($request->filled('tanggal_sampai')) {
            $data['tanggal_sampai'] = $request->tanggal_sampai;
        }

        $izin = $siswa->pengajuanIzin()->create($data);



        Log::channel('sis')->info('[PengajuanIzin] Siswa ajukan izin', [
            'izin_id' => $izin->id,
            'siswa_id' => $siswa->id,
            'jenis' => $izin->jenis,
            'tanggal_izin' => $izin->tanggal_izin,
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

        $request->validate([
            'jenis' => 'required|in:izin_sakit,izin_pulang_cepat,izin_terlambat,izin_lainnya',
            'tanggal_izin' => 'required_if:jenis,izin_pulang_cepat,izin_terlambat|nullable|date|after_or_equal:today',
            'tanggal_mulai' => 'required_if:jenis,izin_sakit,izin_lainnya|nullable|date|after_or_equal:today',
            'tanggal_sampai' => 'required_if:jenis,izin_sakit,izin_lainnya|nullable|date|after_or_equal:tanggal_mulai',
            'alasan' => 'required|string|max:500',
            'bukti' => 'nullable|image|mimes:jpeg,jpg,png|max:10240',
        ]);

        $oldBukti = $izin->bukti;
        $buktiPath = $izin->bukti;

        if ($request->hasFile('bukti')) {
            $file = $request->file('bukti');
            $image = \Intervention\Image\Facades\Image::make($file);

            $image->resize(1200, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })->encode('jpg', 70);

            $dir = 'izin/' . date('Y/m');
            Storage::disk('public')->makeDirectory($dir, 0755, true, false);
            $filename = time() . '_' . $izin->siswa_id . '_bukti.jpg';
            $path = $dir . '/' . $filename;

            Storage::disk('public')->put($path, $image->stream()->__toString());
            $buktiPath = $path;
        }

        $data = [
            'jenis' => $request->jenis,
            'alasan' => $request->alasan,
            'bukti' => $buktiPath,
        ];

        if ($request->filled('tanggal_izin')) {
            $data['tanggal_izin'] = $request->tanggal_izin;
        }
        if ($request->filled('tanggal_mulai')) {
            $data['tanggal_mulai'] = $request->tanggal_mulai;
        }
        if ($request->filled('tanggal_sampai')) {
            $data['tanggal_sampai'] = $request->tanggal_sampai;
        }

        $izin->update($data);

        // Delete old bukti if new uploaded
        if ($request->hasFile('bukti') && $oldBukti) {
            Storage::disk('public')->delete($oldBukti);
        }

        return redirect()->route('siswa.izin.index')
            ->with('success', 'Pengajuan izin berhasil diupdate.');
    }

    public function destroy(PengajuanIzin $izin)
    {
        if ($izin->siswa_id !== auth()->user()->siswa_id || $izin->status !== 'diajukan') {
            abort(403, 'Hanya bisa hapus izin yang belum disetujui');
        }

        if ($izin->bukti) {
            Storage::disk('public')->delete($izin->bukti);
        }

        $izin->delete();

        return redirect()->route('siswa.izin.index')
            ->with('success', 'Pengajuan izin berhasil dihapus.');
    }
}
