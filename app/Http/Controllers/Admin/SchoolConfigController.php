<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SchoolConfigUpdateRequest;
use App\Models\AutoPelanggaranRule;
use App\Models\AutoPenghargaanRule;
use App\Models\Sekolah;
use App\Services\TatibPoinService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SchoolConfigController extends Controller
{
    public function __construct(protected TatibPoinService $tatibPoinService) {}

    public function index()
    {
        $sekolah = Sekolah::aktif();

        $pasalPelanggaran = $this->tatibPoinService->subPasalOptions(
            'pelanggaran',
            $this->tatibPoinService->tahunAjaranAktif()
        );

        $pasalPenghargaan = $this->tatibPoinService->subPasalOptions(
            'penghargaan',
            $this->tatibPoinService->tahunAjaranAktif()
        );

        $autoPelanggaranRules = AutoPelanggaranRule::with('pasal')
            ->orderBy('urutan')->orderBy('id')->get();

        $autoPenghargaanRules = AutoPenghargaanRule::with('pasal')
            ->orderBy('urutan')->orderBy('id')->get();

        return view('admin.school-config.index', compact(
            'sekolah',
            'pasalPelanggaran',
            'pasalPenghargaan',
            'autoPelanggaranRules',
            'autoPenghargaanRules'
        ));
    }

    public function update(SchoolConfigUpdateRequest $request)
    {
        $validated = $request->validated();

        $sekolah = Sekolah::aktif();

        $updateData = [];

        $plainFields = [
            'sekolah', 'alsekolah', 'telp', 'email', 'kab', 'alias',
            'nama_ks', 'nip_ks', 'nama_waka', 'nip_waka',
            'nama_ketua', 'nip_ketua', 'site_url', 'site_logo', 'wasekolah',
            'jam_masuk', 'jam_pulang', 'jam_masuk_khusus', 'jam_pulang_khusus',
            'jam_mulai_absensi', 'batas_tepat_waktu', 'batas_absen_masuk',
            'latitude', 'longitude', 'radius_meter', 'system_name',
            // Auto Alfa
            'jam_eksekusi_auto_alfa', 'pasal_alfa_id',
            // Auto Poin Hadir & Terlambat
            'pasal_hadir_id', 'pasal_terlambat_id',
        ];

        foreach ($plainFields as $field) {
            if (array_key_exists($field, $validated)) {
                $updateData[$field] = $validated[$field];
            }
        }

        // Boolean toggles — checkbox tidak terkirim saat unchecked, maka default false
        $booleanFields = [
            'wa_notif_masuk_enabled',
            'wa_notif_pulang_enabled',
            'wa_notif_event_enabled',
            'wa_notif_tatib_enabled',
            'wa_notif_laporan_guru_enabled',
            'auto_alfa_enabled',
            'auto_point_alfa_enabled',
            'wa_notif_alfa_enabled',
            'auto_poin_hadir_enabled',
            'auto_poin_terlambat_enabled',
            'libur_mode',
        ];

        foreach ($booleanFields as $field) {
            $updateData[$field] = $request->boolean($field);
        }

        // Tanggal libur panjang — kosongkan jika mode dimatikan
        if ($updateData['libur_mode']) {
            $updateData['libur_dari']   = $validated['libur_dari']   ?? null;
            $updateData['libur_sampai'] = $validated['libur_sampai'] ?? null;
        } else {
            // Tetap simpan tanggal meskipun mode off, supaya tidak hilang
            // saat admin mengaktifkan lagi nanti
            if (array_key_exists('libur_dari', $validated)) {
                $updateData['libur_dari'] = $validated['libur_dari'] ?: null;
            }
            if (array_key_exists('libur_sampai', $validated)) {
                $updateData['libur_sampai'] = $validated['libur_sampai'] ?: null;
            }
        }

        // Nomor penerima laporan guru — parse textarea ke JSON array
        $nomorRaw = $validated['wa_notif_laporan_guru_nomor'] ?? '';
        $nomorArr = array_values(array_filter(
            array_map('trim', preg_split('/[\n,]+/', (string) $nomorRaw)),
            fn ($n) => $n !== ''
        ));
        $updateData['wa_notif_laporan_guru_nomor'] = count($nomorArr) > 0 ? $nomorArr : null;

        if (array_key_exists('hari_efektif', $validated)) {
            $updateData['hari_efektif'] = json_encode($validated['hari_efektif']);
        }

        $updateData['hari_khusus'] = json_encode($validated['hari_khusus'] ?? []);

        // Kosongkan pasal_alfa_id jika auto_point_alfa dimatikan
        if (! $updateData['auto_point_alfa_enabled']) {
            $updateData['pasal_alfa_id'] = null;
        }

        // Kosongkan pasal jika toggle hadir/terlambat dimatikan
        if (! $updateData['auto_poin_hadir_enabled']) {
            $updateData['pasal_hadir_id'] = null;
        }
        if (! $updateData['auto_poin_terlambat_enabled']) {
            $updateData['pasal_terlambat_id'] = null;
        }

        $sekolah->update($updateData);

        // Clear cache
        Cache::forget('sekolah_data');
        Cache::forget('sekolah_runtime_config');
        Cache::forget('config');
        Cache::forget('jam_shift_config');

        return redirect()->back()->with('success', 'Konfigurasi sekolah berhasil diperbarui.');
    }
}
