<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\IzinUpdateStatusRequest;
use App\Models\Kelas;
use App\Models\PengajuanIzin;
use App\Services\AttendanceSyncService;
use App\Services\WhatsappService;
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

        $totalPending = PengajuanIzin::diajukan()->count();
        $kelasList = Kelas::orderBy('nama_kelas')->get();

        return view('admin.izin.index', compact('izin', 'totalPending', 'kelasList'));
    }

    public function updateStatus(IzinUpdateStatusRequest $request, PengajuanIzin $izin, AttendanceSyncService $attendanceSync, WhatsappService $whatsapp)
    {
        $validated = $request->validated();

        $oldStatus = $izin->status;
        $izin->update([
            'status'           => $validated['status'],
            'diverifikasi_oleh' => auth()->id(),
            'waktu_verifikasi' => now(),
        ]);

        $syncResult = null;
        if ($validated['status'] === 'disetujui') {
            $syncResult = $attendanceSync->syncApprovedIzinForDate();
        }

        // Kirim notifikasi WA saat izin ditolak
        $waSent = false;
        if ($validated['status'] === 'ditolak') {
            $waSent = $this->kirimNotifikasiDitolak($izin, $validated['catatan'] ?? null, $whatsapp);
        }

        Log::channel('sis')->info('[PengajuanIzin] Admin verifikasi', [
            'izin_id'          => $izin->id,
            'siswa_id'         => $izin->siswa_id,
            'status_lama'      => $oldStatus,
            'status_baru'      => $validated['status'],
            'admin_id'         => auth()->id(),
            'catatan'          => $validated['catatan'] ?? null,
            'attendance_sync'  => $syncResult,
            'wa_notif_sent'    => $waSent,
        ]);

        $message = $validated['status'] === 'disetujui'
            ? 'Izin berhasil disetujui.'
            : 'Izin berhasil ditolak.';

        if ($syncResult && ($syncResult['updated'] || $syncResult['created'])) {
            $message .= " Absensi ikut disinkronkan ({$syncResult['updated']} update, {$syncResult['created']} dibuat).";
        }

        return redirect()->route('admin.izin.index')->with('success', $message);
    }

    /**
     * Kirim notifikasi WA ke siswa & orang tua saat izin ditolak.
     */
    private function kirimNotifikasiDitolak(PengajuanIzin $izin, ?string $catatan, WhatsappService $whatsapp): bool
    {
        $siswa = $izin->siswa;
        if (! $siswa) {
            return false;
        }

        $pesan = WhatsappService::templateIzinDitolak(
            namaSiswa: $siswa->nama_lengkap,
            kelas: $siswa->kelas?->nama_kelas ?? '-',
            jenisIzin: $izin->jenis_label,
            tanggal: $izin->tanggal_mulai?->translatedFormat('d F Y')
                     .($izin->isRangeJenis() && $izin->tanggal_sampai
                         ? ' – '.$izin->tanggal_sampai->translatedFormat('d F Y')
                         : ''),
            alasan: $izin->alasan,
            catatan: $catatan,
            admin: auth()->user()?->name ?? 'Admin',
        );

        $sent = false;

        // Kirim ke nomor HP siswa (jika ada)
        if (! empty($siswa->no_hp_siswa)) {
            $sent = $whatsapp->send($siswa->no_hp_siswa, $pesan, 'izin_ditolak', $izin->id);
        }

        // Kirim juga ke orang tua (jika ada)
        if (! empty($siswa->no_hp_ortu1)) {
            $whatsapp->send($siswa->no_hp_ortu1, $pesan, 'izin_ditolak_ortu', $izin->id);
        }

        return $sent;
    }
}
