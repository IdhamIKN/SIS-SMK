<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Http\Requests\SiswaStoreRequest;
use App\Http\Requests\SiswaUpdateRequest;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SiswaController extends Controller
{
    public function index(Request $request): View
    {
        Log::channel('sis')->info('[Siswa] Index access', [
            'user_id' => $request->user()->id,
        ]);

        $siswas = Siswa::query()
            ->with(['kelas', 'user'])
            ->when($request->search, fn ($q) => $q->where('nama_lengkap', 'like', '%'.$request->search.'%')
                ->orWhere('nis', 'like', '%'.$request->search.'%')
                ->orWhere('nisn', 'like', '%'.$request->search.'%'))
            ->when($request->kelas_id, fn ($q) => $q->where('kelas_id', $request->kelas_id))
            ->when($request->status_aktif !== null, fn ($q) => $q->where('status_aktif', $request->boolean('status_aktif')))
            ->paginate(20);

        $kelas = Kelas::orderBy('nama_kelas')->get();

        return view('siswa.index', compact('siswas', 'kelas'));
    }

    public function create(): View
    {
        $kelas = Kelas::orderBy('nama_kelas')->get();

        return view('siswa.create', compact('kelas'));
    }

    public function store(SiswaStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Log::channel('sis')->info('[Siswa] Create new siswa', [
            'nisn' => $validated['nisn'],
            'nama' => $validated['nama_lengkap'],
            'user_id' => $request->user()->id,
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('siswa', 'public');
        }

        $siswa = Siswa::create($validated);

        // Create user account for login
        $user = User::create([
            'name' => $validated['nama_lengkap'],
            'email' => $validated['nisn'].'@smkn5madiun.sch.id',
            'password' => bcrypt('password123'), // Default password
            'phone' => $validated['no_hp_siswa'] ?? null,
            'role_utama' => 'siswa',
            'siswa_id' => $siswa->id,
        ]);

        // Assign role
        $user->assignRole('siswa');

        // Update siswa with user_id
        $siswa->update(['user_id' => $user->id]);

        return redirect()->route('siswa.index')->with('success', 'Siswa berhasil ditambahkan');
    }

    public function show(Siswa $siswa): View
    {
        $siswa->load(['kelas', 'user', 'absenSiswa' => fn ($q) => $q->latest()->limit(10)]);

        return view('siswa.show', compact('siswa'));
    }

    public function edit(Siswa $siswa): View
    {
        $kelas = Kelas::orderBy('nama_kelas')->get();

        return view('siswa.edit', compact('siswa', 'kelas'));
    }

    public function update(SiswaUpdateRequest $request, Siswa $siswa): RedirectResponse
    {
        $validated = $request->validated();

        Log::channel('sis')->info('[Siswa] Update siswa', [
            'siswa_id' => $siswa->id,
            'nisn' => $validated['nisn'],
            'user_id' => $request->user()->id,
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('siswa', 'public');
        }

        $siswa->update($validated);

        // Update user account
        if ($siswa->user) {
            $siswa->user->update([
                'name' => $validated['nama_lengkap'],
                'email' => $validated['nisn'].'@smkn5madiun.sch.id',
                'phone' => $validated['no_hp_siswa'] ?? null,
            ]);
        }

        return redirect()->route('siswa.show', $siswa)->with('success', 'Siswa berhasil diupdate');
    }

    public function destroy(Siswa $siswa): RedirectResponse
    {
        Log::channel('sis')->info('[Siswa] Delete siswa', [
            'siswa_id' => $siswa->id,
            'nisn' => $siswa->nisn,
            'user_id' => request()->user()->id,
        ]);

        $siswa->delete();

        return redirect()->route('siswa.index')->with('success', 'Siswa berhasil dihapus');
    }

    public function showImportForm(Request $request): View
    {
        if ($request->isMethod('post')) {
            // Handle file upload and redirect to preview
            return $this->previewImport($request);
        }

        return view('siswa.import');
    }

    public function previewImport(Request $request): View
    {
        set_time_limit(300);

        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:2048',
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();

        $data = [];
        if ($extension === 'csv') {
            $csvData = array_map('str_getcsv', file($file->getRealPath()));
            $header = array_shift($csvData);
            foreach ($csvData as $row) {
                $data[] = array_combine($header, $row);
            }
        } else {
            return back()->with('error', 'Format Excel belum didukung. Gunakan CSV.');
        }

        $validData = [];
        $importErrors = [];
        $rowIndex = 2; // Mulai dari baris 2 (setelah header)

        foreach ($data as $row) {
            $rowErrors = [];

            // Validasi nisn
            if (empty($row['nisn'])) {
                $rowErrors[] = 'nisn wajib diisi';
            } elseif (! is_numeric($row['nisn']) || strlen($row['nisn']) != 10) {
                $rowErrors[] = 'nisn harus 10 digit angka';
            } elseif (Siswa::where('nisn', $row['nisn'])->exists()) {
                $rowErrors[] = 'nisn sudah terdaftar';
            }

            // Validasi nama_lengkap
            if (empty($row['nama_lengkap'])) {
                $rowErrors[] = 'nama_lengkap wajib diisi';
            }

            // Validasi kelas
            $kelas = Kelas::where('nama_kelas', $row['nama_kelas'] ?? null)->first();
            if (! $kelas) {
                $rowErrors[] = 'nama_kelas tidak ditemukan';
            }

            // Validasi jenis_kelamin
            $jk = strtoupper($row['jenis_kelamin'] ?? '');
            if (! in_array($jk, ['L', 'P'])) {
                $rowErrors[] = 'jenis_kelamin harus L atau P';
            }

            // Jika tidak ada error, tambah ke validData
            if (empty($rowErrors)) {
                $validData[] = [
                    'nis' => $row['nis'] ?? null,
                    'nisn' => $row['nisn'],
                    'nama_lengkap' => $row['nama_lengkap'],
                    'jenis_kelamin' => $jk === 'L' ? 'L' : 'P',
                    'kelas_id' => $kelas->id,
                    'angkatan' => $row['angkatan'] ?? null,
                    'tempat_lahir' => $row['tempat_lahir'] ?? null,
                    'tanggal_lahir' => $row['tanggal_lahir'] ?? null,
                    'alamat' => $row['alamat'] ?? null,
                    'desa' => $row['desa'] ?? null,
                    'kelurahan' => $row['kelurahan'] ?? null,
                    'kecamatan' => $row['kecamatan'] ?? null,
                    'kabupaten' => $row['kabupaten'] ?? null,
                    'kode_pos' => $row['kode_pos'] ?? null,
                    'no_hp_siswa' => $row['no_hp_siswa'] ?? null,
                    'no_hp_ortu1' => $row['no_hp_ortu1'] ?? null,
                    'no_hp_ortu2' => $row['no_hp_ortu2'] ?? null,
                    'nama_ortu1' => $row['nama_ortu1'] ?? null,
                    'nama_ortu2' => $row['nama_ortu2'] ?? null,
                    'nama_wali' => $row['nama_wali'] ?? null,
                    'status_aktif' => isset($row['status_aktif']) ? filter_var($row['status_aktif'], FILTER_VALIDATE_BOOLEAN) : true,
                    'noreg_legacy' => $row['noreg'] ?? null,
                ];
            } else {
                $importErrors[] = [
                    'row' => $rowIndex,
                    'data' => $row,
                    'errors' => $rowErrors,
                ];
            }

            $rowIndex++;
        }

        // Simpan validData ke session untuk import nanti
        session(['import_siswa_data' => $validData]);

        return view('siswa.import_preview', compact('validData', 'importErrors'));
    }

    public function importProcess(Request $request): RedirectResponse
    {
        set_time_limit(300);

        $data = session('import_siswa_data');

        if (! $data || ! is_array($data)) {
            return redirect()->route('siswa.import.form')->with('error', 'Data import tidak ditemukan. Silakan upload ulang file.');
        }

        $successCount = 0;
        $errors = [];

        foreach ($data as $index => $row) {
            try {
                $validated = [
                    'nis' => $row['nis'] ?? null,
                    'nisn' => $row['nisn'] ?? null,
                    'nama_lengkap' => $row['nama_lengkap'] ?? null,
                    'jenis_kelamin' => strtoupper($row['jenis_kelamin'] ?? 'L') === 'L' ? 'L' : 'P',
                    'kelas_id' => $row['kelas_id'] ?? null,
                    'angkatan' => $row['angkatan'] ?? null,
                    'tempat_lahir' => $row['tempat_lahir'] ?? null,
                    'tanggal_lahir' => $row['tanggal_lahir'] ?? null,
                    'alamat' => $row['alamat'] ?? null,
                    'desa' => $row['desa'] ?? null,
                    'kelurahan' => $row['kelurahan'] ?? null,
                    'kecamatan' => $row['kecamatan'] ?? null,
                    'kabupaten' => $row['kabupaten'] ?? null,
                    'kode_pos' => $row['kode_pos'] ?? null,
                    'no_hp_siswa' => $row['no_hp_siswa'] ?? null,
                    'no_hp_ortu1' => $row['no_hp_ortu1'] ?? null,
                    'no_hp_ortu2' => $row['no_hp_ortu2'] ?? null,
                    'nama_ortu1' => $row['nama_ortu1'] ?? null,
                    'nama_ortu2' => $row['nama_ortu2'] ?? null,
                    'nama_wali' => $row['nama_wali'] ?? null,
                    'status_aktif' => isset($row['status_aktif']) ? filter_var($row['status_aktif'], FILTER_VALIDATE_BOOLEAN) : true,
                    'noreg_legacy' => $row['noreg'] ?? null,
                ];

                // Basic validation
                if (empty($validated['nisn']) || empty($validated['nama_lengkap'])) {
                    throw new \Exception('nisn dan nama_lengkap wajib diisi');
                }

                if (Siswa::where('nisn', $validated['nisn'])->exists()) {
                    throw new \Exception('nisn sudah ada');
                }

                $siswa = Siswa::create($validated);

                // Create user account for login
                $email = $validated['nisn'].'@smkn5madiun.sch.id';
                $user = User::where('email', $email)->first();
                if (! $user) {
                    $user = User::create([
                        'name' => $validated['nama_lengkap'],
                        'email' => $email,
                        'password' => bcrypt('password123'), // Default password
                        'phone' => $validated['no_hp_siswa'] ?? null,
                        'role_utama' => 'siswa',
                        'siswa_id' => $siswa->id,
                    ]);

                    // Assign role
                    $user->assignRole('siswa');
                }

                // Update siswa with user_id
                $siswa->update(['user_id' => $user->id]);

                Log::channel('sis')->info('[Siswa Import] User baru dibuat', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'nisn' => $validated['nisn'],
                ]);

                $successCount++;
            } catch (\Exception $e) {
                $errors[] = 'Baris '.($index + 2).': '.$e->getMessage();
            }
        }

        $message = $successCount.' data berhasil diimport.';
        if (! empty($errors)) {
            $message .= ' Error: '.implode('; ', array_slice($errors, 0, 5));
        }

        // Clear session data
        session()->forget('import_siswa_data');

        return redirect()->route('siswa.index')->with('success', $message);
    }

    public function exportCV(Siswa $siswa)
    {
        Log::channel('sis')->info('[Siswa] Export CV PDF', [
            'siswa_id' => $siswa->id,
            'user_id' => request()->user()->id,
        ]);

        $siswa->load(['kelas', 'absenSiswa' => fn ($q) => $q->whereYear('tanggal', date('Y'))]);

        $pdf = Pdf::loadView('pdf.cv_siswa', compact('siswa'));

        return $pdf->download('CV_'.$siswa->nama_lengkap.'.pdf');
    }

    public function downloadTemplate()
    {
        Log::channel('sis')->info('[Siswa] Download template CSV', [
            'user_id' => request()->user()->id,
        ]);

        $filename = 'template_import_siswa.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            // Header
            fputcsv($file, [
                'nisn', 'nama_lengkap', 'jenis_kelamin', 'nama_kelas',
                'nis', 'angkatan', 'tempat_lahir', 'tanggal_lahir',
                'alamat', 'desa', 'kelurahan', 'kecamatan', 'kabupaten', 'kode_pos',
                'no_hp_siswa', 'no_hp_ortu1', 'no_hp_ortu2',
                'nama_ortu1', 'nama_ortu2', 'nama_wali', 'noreg', 'status_aktif',
            ]);

            // Contoh data siswa 1
            fputcsv($file, [
                '1234567890', 'Ahmad Surya Pratama', 'L', 'X RPL 1',
                '2021001', '2021', 'Jakarta', '2005-01-15',
                'Jl. Sudirman No. 123', 'Desa Sukamaju', 'Kelurahan Jakarta Pusat', 'Kecamatan Tanah Abang', 'Kabupaten Jakarta Pusat', '10160',
                '081234567890', '081234567891', '',
                'Budi Santoso', 'Siti Aminah', '', 'REG001', '1',
            ]);

            // Contoh data siswa 2
            fputcsv($file, [
                '1234567891', 'Siti Nurhaliza', 'P', 'X RPL 1',
                '2021002', '2021', 'Bandung', '2005-03-20',
                'Jl. Asia Afrika No. 45', 'Desa Cibaduyut', 'Kelurahan Bandung Wetan', 'Kecamatan Bandung Kidul', 'Kabupaten Bandung', '40111',
                '081234567892', '081234567893', '081234567894',
                'Ahmad Rahman', 'Fatimah Zahra', 'Umar bin Khattab', 'REG002', '1',
            ]);

            // Contoh data siswa 3
            fputcsv($file, [
                '1234567892', 'Budi Santoso Putra', 'L', 'X TKJ 1',
                '2021003', '2021', 'Surabaya', '2005-07-10',
                'Jl. Tunjungan No. 78', 'Desa Wonocolo', 'Kelurahan Surabaya Barat', 'Kecamatan Bubutan', 'Kabupaten Surabaya', '60271',
                '081234567895', '081234567896', '',
                'Joko Widodo', 'Iriana Jokowi', '', 'REG003', '1',
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function export()
    {
        Log::channel('sis')->info('[Siswa] Export CSV', [
            'user_id' => request()->user()->id,
        ]);

        $filename = 'export_siswa_'.date('Y-m-d_H-i-s').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            // Header
            fputcsv($file, [
                'nisn', 'nama_lengkap', 'jenis_kelamin', 'nama_kelas',
                'nis', 'angkatan', 'tempat_lahir', 'tanggal_lahir',
                'alamat', 'desa', 'kelurahan', 'kecamatan', 'kabupaten', 'kode_pos',
                'no_hp_siswa', 'no_hp_ortu1', 'no_hp_ortu2',
                'nama_ortu1', 'nama_ortu2', 'nama_wali', 'noreg', 'status_aktif',
            ]);

            // Data siswa
            $siswas = Siswa::with('kelas')->get();
            foreach ($siswas as $siswa) {
                fputcsv($file, [
                    $siswa->nisn,
                    $siswa->nama_lengkap,
                    $siswa->jenis_kelamin,
                    $siswa->kelas?->nama_kelas,
                    $siswa->nis,
                    $siswa->angkatan,
                    $siswa->tempat_lahir,
                    $siswa->tanggal_lahir,
                    $siswa->alamat,
                    $siswa->desa,
                    $siswa->kelurahan,
                    $siswa->kecamatan,
                    $siswa->kabupaten,
                    $siswa->kode_pos,
                    $siswa->no_hp_siswa,
                    $siswa->no_hp_ortu1,
                    $siswa->no_hp_ortu2,
                    $siswa->nama_ortu1,
                    $siswa->nama_ortu2,
                    $siswa->nama_wali,
                    $siswa->noreg_legacy,
                    $siswa->status_aktif ? '1' : '0',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
