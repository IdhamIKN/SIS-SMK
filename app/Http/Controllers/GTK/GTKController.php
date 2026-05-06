<?php

namespace App\Http\Controllers\GTK;

use App\Http\Controllers\Controller;
use App\Http\Requests\GTKStoreRequest;
use App\Http\Requests\GTKUpdateRequest;
use App\Models\GTK;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class GTKController extends Controller
{
    public function index(Request $request): View
    {
        Log::channel('sis')->info('[GTK] Index access', [
            'user_id' => $request->user()->id,
        ]);

        $gtks = GTK::query()
            ->when($request->search, fn ($q) => $q->where('nama_lengkap', 'like', '%'.$request->search.'%'))
            ->when($request->jabatan, fn ($q) => $q->where('jabatan', $request->jabatan))
            ->paginate(20);

        return view('gtk.index', compact('gtks'));
    }

    public function create(): View
    {
        return view('gtk.create');
    }

    public function store(GTKStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Buat user otomatis jika belum ada
        $email = $validated['kd_guru'].'@school.local';
        $user = User::where('email', $email)->first();
        if (! $user) {
            $user = User::create([
                'name' => $validated['nama_lengkap'],
                'email' => $email,
                'password' => Hash::make('password123'),
                'role_utama' => 'gtk',
            ]);
            $user->assignRole('gtk');
        }
        $validated['user_id'] = $user->id;

        Log::channel('sis')->info('[GTK] Create new GTK', [
            'kd_guru' => $validated['kd_guru'],
            'nama' => $validated['nama_lengkap'],
            'user_id' => $user->id,
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('gtk', 'public');
        }

        GTK::create($validated);

        return redirect()->route('gtk.index')->with('success', 'GTK berhasil ditambahkan');
    }

    public function show(GTK $gtk): View
    {
        return view('gtk.show', compact('gtk'));
    }

    public function edit(GTK $gtk): View
    {
        return view('gtk.edit', compact('gtk'));
    }

    public function update(GTKUpdateRequest $request, GTK $gtk): RedirectResponse
    {
        $validated = $request->validated();

        // Pastikan user ada
        $email = $validated['kd_guru'].'@school.local';
        $user = User::where('email', $email)->first();
        if (! $user) {
            $user = User::create([
                'name' => $validated['nama_lengkap'],
                'email' => $email,
                'password' => Hash::make('password123'),
                'role_utama' => 'gtk',
            ]);
            $user->assignRole('gtk');
        }
        $validated['user_id'] = $user->id;

        Log::channel('sis')->info('[GTK] Update GTK', [
            'gtk_id' => $gtk->id,
            'kd_guru' => $validated['kd_guru'],
            'user_id' => $user->id,
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('gtk', 'public');
        }

        $gtk->update($validated);

        return redirect()->route('gtk.show', $gtk)->with('success', 'GTK berhasil diupdate');
    }

    public function destroy(GTK $gtk): RedirectResponse
    {
        Log::channel('sis')->info('[GTK] Delete GTK', [
            'gtk_id' => $gtk->id,
            'kd_guru' => $gtk->kd_guru,
            'user_id' => request()->user()->id,
        ]);

        $gtk->delete();

        return redirect()->route('gtk.index')->with('success', 'GTK berhasil dihapus');
    }

    public function import(): View
    {
        return view('gtk.import');
    }

    public function importProcess(Request $request): RedirectResponse
    {
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
            // For Excel, we need to install maatwebsite/excel or use simple method
            // For simplicity, assume CSV for now, but can extend later
            return redirect()->back()->with('error', 'Format Excel belum didukung. Gunakan CSV.');
        }

        $successCount = 0;
        $errors = [];

        foreach ($data as $index => $row) {
            try {
                $validated = [
                    'kd_guru' => $row['kd_guru'] ?? null,
                    'nip' => $row['nip'] ?? null,
                    'nik' => $row['nik'] ?? null,
                    'nuptk' => $row['nuptk'] ?? null,
                    'nama_lengkap' => $row['nama_lengkap'] ?? null,
                    'jenis_kelamin' => $row['jenis_kelamin'] ?? 'L',
                    'no_hp' => $row['no_hp'] ?? null,
                    'mata_pelajaran' => $row['mata_pelajaran'] ?? null,
                    'jabatan' => $row['jabatan'] ?? 'Guru',
                    'status_aktif' => isset($row['status_aktif']) ? filter_var($row['status_aktif'], FILTER_VALIDATE_BOOLEAN) : true,
                    'acc_absen' => isset($row['acc_absen']) ? filter_var($row['acc_absen'], FILTER_VALIDATE_BOOLEAN) : false,
                    'acc_kurikulum' => isset($row['acc_kurikulum']) ? filter_var($row['acc_kurikulum'], FILTER_VALIDATE_BOOLEAN) : false,
                    'acc_jurnal' => isset($row['acc_jurnal']) ? filter_var($row['acc_jurnal'], FILTER_VALIDATE_BOOLEAN) : false,
                    'acc_bk' => isset($row['acc_bk']) ? filter_var($row['acc_bk'], FILTER_VALIDATE_BOOLEAN) : false,
                    'guru_piket' => isset($row['guru_piket']) ? filter_var($row['guru_piket'], FILTER_VALIDATE_BOOLEAN) : false,
                    'acc_profil' => isset($row['acc_profil']) ? filter_var($row['acc_profil'], FILTER_VALIDATE_BOOLEAN) : false,
                    'group_acc' => isset($row['group_acc']) ? filter_var($row['group_acc'], FILTER_VALIDATE_BOOLEAN) : false,
                    'view_siswa' => $row['view_siswa'] ?? 'limit',
                ];

                // Basic validation
                if (empty($validated['kd_guru']) || empty($validated['nama_lengkap'])) {
                    throw new \Exception('kd_guru dan nama_lengkap wajib diisi');
                }

                if (GTK::where('kd_guru', $validated['kd_guru'])->exists()) {
                    throw new \Exception('kd_guru sudah ada');
                }

                // Buat user otomatis jika belum ada
                $email = $validated['kd_guru'].'@school.local';
                $user = User::where('email', $email)->first();
                if (! $user) {
                    $user = User::create([
                        'name' => $validated['nama_lengkap'],
                        'email' => $email,
                        'password' => Hash::make('password123'), // Password default
                        'role_utama' => 'gtk',
                    ]);
                    $user->assignRole('gtk');
                    Log::channel('sis')->info('[GTK Import] User baru dibuat', [
                        'user_id' => $user->id,
                        'email' => $email,
                        'kd_guru' => $validated['kd_guru'],
                    ]);
                }
                $validated['user_id'] = $user->id;

                GTK::create($validated);
                $successCount++;
            } catch (\Exception $e) {
                $errors[] = 'Baris '.($index + 2).': '.$e->getMessage();
            }
        }

        $message = $successCount.' data berhasil diimport.';
        if (! empty($errors)) {
            $message .= ' Error: '.implode('; ', array_slice($errors, 0, 5));
        }

        return redirect()->route('gtk.index')->with('success', $message);
    }

    public function downloadTemplate()
    {
        $filename = 'template_import_gtk.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            // Header
            fputcsv($file, [
                'kd_guru', 'nip', 'nik', 'nuptk', 'nama_lengkap', 'jenis_kelamin',
                'no_hp', 'mata_pelajaran', 'jabatan', 'status_aktif',
                'acc_absen', 'acc_kurikulum', 'acc_jurnal', 'acc_bk',
                'guru_piket', 'acc_profil', 'group_acc', 'view_siswa',
            ]);

            // Contoh data
            fputcsv($file, [
                'G001', '1987654321', '1234567890123456', '1234567890', 'Ahmad Surya', 'L',
                '081234567890', 'Matematika', 'Guru', '1',
                '0', '0', '0', '0',
                '0', '0', '0', 'limit',
            ]);

            fputcsv($file, [
                'G002', '1987654322', '1234567890123457', '1234567891', 'Siti Aminah', 'P',
                '081234567891', 'Bahasa Indonesia', 'Guru', '1',
                '0', '0', '0', '0',
                '0', '0', '0', 'limit',
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
