<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\IzinUpdateStatusRequest;
use App\Models\PengajuanIzin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IzinController extends Controller
{
    public function index(Request $request)
    {
        $query = PengajuanIzin::with(['siswa.kelas', 'verifier'])
            ->diajukan()
            ->latest();

        $izin = $query->paginate(20);

        return view('admin.izin.index', compact('izin'));
    }

    public function updateStatus(IzinUpdateStatusRequest $request, PengajuanIzin $izin)
    {
        $validated = $request->validated();

        $oldStatus = $izin->status;
        $izin->update([
            'status' => $validated['status'],
            'diverifikasi_oleh' => auth()->id(),
            'waktu_verifikasi' => now(),
        ]);

        Log::channel('sis')->info('[PengajuanIzin] Admin verifikasi', [
            'izin_id' => $izin->id,
            'siswa_id' => $izin->siswa_id,
            'status_lama' => $oldStatus,
            'status_baru' => $request->status,
            'admin_id' => auth()->id(),
            'catatan' => $request->catatan,
        ]);

        return redirect()->route('admin.izin.index')
            ->with('success', 'Status izin berhasil diupdate.');
    }
}
