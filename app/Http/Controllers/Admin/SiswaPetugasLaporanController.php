<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\SiswaPetugasLaporan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SiswaPetugasLaporanController extends Controller
{
    private const JUMLAH_PETUGAS_PER_KELAS = 3;

    public function index(Request $request): View
    {
        $kelas = Kelas::with(['jurusan', 'siswaPetugasLaporan.siswa'])
            ->withCount('siswa')
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        $kelasDipilih = $kelas->firstWhere('id', (int) $request->get('kelas_id'))
            ?? $kelas->first();

        $siswaKelas = $kelasDipilih
            ? $kelasDipilih->siswa()->orderBy('nama_lengkap')->get()
            : collect();

        $petugasTerpilihIds = $kelasDipilih
            ? $kelasDipilih->siswaPetugasLaporan
                ->filter(fn (SiswaPetugasLaporan $petugas) => $petugas->siswa &&
                    (int) $petugas->siswa->kelas_id === (int) $kelasDipilih->id)
                ->pluck('siswa_id')
                ->map(fn ($id) => (int) $id)
                ->all()
            : [];

        $jumlahKelasLengkap = $kelas->filter(function (Kelas $item) {
            return $item->siswaPetugasLaporan
                ->filter(fn (SiswaPetugasLaporan $petugas) => $petugas->siswa &&
                    (int) $petugas->siswa->kelas_id === (int) $item->id)
                ->count() === self::JUMLAH_PETUGAS_PER_KELAS;
        })->count();

        return view('admin.siswa_petugas_laporan.index', [
            'kelas' => $kelas,
            'kelasDipilih' => $kelasDipilih,
            'siswaKelas' => $siswaKelas,
            'petugasTerpilihIds' => $petugasTerpilihIds,
            'jumlahKelasLengkap' => $jumlahKelasLengkap,
            'jumlahPetugasPerKelas' => self::JUMLAH_PETUGAS_PER_KELAS,
        ]);
    }

    public function update(Request $request, Kelas $kelas): RedirectResponse
    {
        $validated = $request->validate([
            'siswa_ids' => ['required', 'array', 'size:'.self::JUMLAH_PETUGAS_PER_KELAS],
            'siswa_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('siswas', 'id')
                    ->where('kelas_id', $kelas->id)
                    ->whereNull('deleted_at'),
            ],
        ], [
            'siswa_ids.required' => 'Pilih 3 siswa petugas laporan.',
            'siswa_ids.size' => 'Setiap kelas harus memiliki tepat 3 siswa petugas laporan.',
            'siswa_ids.*.exists' => 'Siswa yang dipilih harus berasal dari kelas ini.',
            'siswa_ids.*.distinct' => 'Siswa petugas laporan tidak boleh dipilih lebih dari sekali.',
        ]);

        DB::transaction(function () use ($kelas, $validated, $request) {
            Kelas::whereKey($kelas->id)->lockForUpdate()->firstOrFail();

            SiswaPetugasLaporan::where('kelas_id', $kelas->id)
                ->whereNotIn('siswa_id', $validated['siswa_ids'])
                ->delete();

            foreach ($validated['siswa_ids'] as $siswaId) {
                SiswaPetugasLaporan::updateOrCreate(
                    [
                        'kelas_id' => $kelas->id,
                        'siswa_id' => $siswaId,
                    ],
                    [
                        'created_by' => $request->user()->id,
                    ]
                );
            }
        });

        return redirect()
            ->route('admin.petugas-laporan-guru.index', ['kelas_id' => $kelas->id])
            ->with('success', 'Petugas laporan guru untuk kelas '.$kelas->nama_kelas.' berhasil diperbarui.');
    }
}
