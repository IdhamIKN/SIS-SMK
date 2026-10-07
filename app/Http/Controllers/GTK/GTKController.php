<?php

namespace App\Http\Controllers\GTK;

use App\Http\Controllers\Controller;
use App\Http\Requests\GTKStoreRequest;
use App\Http\Requests\GTKUpdateRequest;
use App\Models\GTK;
use App\Models\MataPelajaran;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class GTKController extends Controller
{
    // private function defaultPasswordFromNik(array $data): string
    // {
    //     return trim((string) ($data['nik'] ?? ''));
    // }
    private function defaultPasswordFromNik(array $data): string
    {
        $nik = trim((string) ($data['nik'] ?? ''));
    
        return $nik !== '' ? $nik : '12345678';
    }
    
    public function resetPassword(GTK $gtk): RedirectResponse
    {
        $user = $gtk->user;
    
        if (! $user) {
            return redirect()->route('gtk.show', $gtk)
                ->with('error', 'User akun untuk GTK ini tidak ditemukan.');
        }
    
        $user->forceFill([
            'password' => Hash::make('12345678'),
        ])->save();
    
        Log::channel('sis')->info('[GTK] Reset password GTK', [
            'gtk_id'  => $gtk->id,
            'kd_guru' => $gtk->kd_guru,
            'user_id' => $user->id,
            'by'      => request()->user()->id,
        ]);
    
        return redirect()->route('gtk.show', $gtk)
            ->with('success', 'Password GTK berhasil direset ke default (12345678).');
    }

    private function generateKdGuru(): string
    {
        // Ambil kode guru terakhir yang dimulai dengan 'G'
        $lastKdGuru = GTK::where('kd_guru', 'like', 'G%')
            ->orderByRaw('CAST(SUBSTRING(kd_guru, 2) AS UNSIGNED) DESC')
            ->value('kd_guru');

        if ($lastKdGuru) {
            // Ekstrak angka dari kode terakhir, tambah 1
            $number = (int) substr($lastKdGuru, 1) + 1;
        } else {
            // Jika belum ada, mulai dari 1
            $number = 1;
        }

        // Format sebagai G001, G002, dll.
        return 'G'.str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    // public function index(Request $request): View
    // {
    //     Log::channel('sis')->info('[GTK] Index access', [
    //         'user_id' => $request->user()->id,
    //     ]);

    //     $gtks = GTK::query()
    //         ->with('mataPelajaran')
    //         ->when($request->search, fn ($q) => $q->where('nama_lengkap', 'like', '%'.$request->search.'%'))
    //         ->when($request->jabatan, fn ($q) => $q->where('jabatan', $request->jabatan))
    //         ->paginate(20);

    //     return view('gtk.index', compact('gtks'));
    // }
    public function index(Request $request): View
{
    Log::channel('sis')->info('[GTK] Index access', [
        'user_id' => $request->user()->id,
    ]);

    $gtks = GTK::query()
        ->with(['mataPelajaran', 'user.roles'])
        ->when($request->search, fn ($q) => $q->where(function ($q) use ($request) {
            $q->where('nama_lengkap', 'like', '%'.$request->search.'%')
              ->orWhere('nip', 'like', '%'.$request->search.'%');
        }))
        ->when($request->role, fn ($q) => $q->whereHas('user.roles', fn ($r) =>
            $r->where('name', $request->role)
        ))
        ->paginate(20);

    $aktifCount    = GTK::where('status_aktif', true)->count();
    $nonAktifCount = GTK::where('status_aktif', false)->count();

    return view('gtk.index', compact('gtks', 'aktifCount', 'nonAktifCount'));
}

    public function create(): View
    {
        $selectedIds = collect(session()->getOldInput('mata_pelajaran', []))
            ->filter()
            ->unique()
            ->values();

        $mataPelajarans = MataPelajaran::query()
            ->whereIn('id', $selectedIds)
            ->orderBy('nama_mapel')
            ->get();

        // Generate kode guru otomatis untuk preview
        $generatedKdGuru = $this->generateKdGuru();

        return view('gtk.create', compact('mataPelajarans', 'generatedKdGuru'));
    }

    public function searchMataPelajaran(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));
        $page = max((int) $request->query('page', 1), 1);
        $perPage = 20;

        $mataPelajarans = MataPelajaran::query()
            ->select(['id', 'kode_mapel', 'nama_mapel', 'kategori'])
            ->when($term !== '', function ($query) use ($term) {
                $query->where(function ($query) use ($term) {
                    $query->where('nama_mapel', 'like', "%{$term}%")
                        ->orWhere('kode_mapel', 'like', "%{$term}%")
                        ->orWhere('kategori', 'like', "%{$term}%");
                });
            })
            ->orderBy('nama_mapel')
            ->skip(($page - 1) * $perPage)
            ->take($perPage + 1)
            ->get();

        return response()->json([
            'results' => $mataPelajarans
                ->take($perPage)
                ->map(fn (MataPelajaran $mataPelajaran) => [
                    'id' => $mataPelajaran->id,
                    'text' => $mataPelajaran->kode_mapel
                        ? "{$mataPelajaran->nama_mapel} ({$mataPelajaran->kode_mapel})"
                        : $mataPelajaran->nama_mapel,
                    'kode_mapel' => $mataPelajaran->kode_mapel,
                    'kategori' => $mataPelajaran->kategori,
                ])
                ->values(),
            'pagination' => [
                'more' => $mataPelajarans->count() > $perPage,
            ],
        ]);
    }

    public function store(GTKStoreRequest $request): RedirectResponse
    {
        // Generate kode guru otomatis jika belum ada
        if (! $request->has('kd_guru') || empty($request->kd_guru)) {
            $request->merge(['kd_guru' => $this->generateKdGuru()]);
        }

        $validated = $request->validated();

        // Buat user otomatis jika belum ada
        $email = $validated['kd_guru'].'@school.local';
        $user = User::where('email', $email)->first();
        $password = Hash::make($this->defaultPasswordFromNik($validated));
        if (! $user) {
            $user = User::create([
                'name' => $validated['nama_lengkap'],
                'email' => $email,
                'password' => $password,
                'role_utama' => 'gtk',
            ]);
            // Superadmin bisa memilih multi-role saat create; selain itu default gtk
            $newRoles = ($request->user()->hasRole('superadmin') && ! empty($validated['roles']))
                ? array_values(array_filter((array) $validated['roles']))
                : ['gtk'];
            $user->syncRoles($newRoles);
            // role_utama: prioritaskan gtk jika ada dalam pilihan, fallback ke role pertama
            $user->forceFill([
                'role_utama' => in_array('gtk', $newRoles) ? 'gtk' : $newRoles[0],
            ])->save();
        } else {
            $user->forceFill([
                'name' => $validated['nama_lengkap'],
                'password' => $password,
            ])->save();
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

        // Buang field roles agar tidak masuk ke kolom tabel gtks
        unset($validated['roles']);

        $gtk = GTK::create($validated);

        if ($request->mata_pelajaran) {
            $gtk->mataPelajaran()->attach($request->mata_pelajaran);
        }

        return redirect()->route('gtk.index')->with('success', 'GTK berhasil ditambahkan');
    }

    public function show(GTK $gtk): View
    {
        return view('gtk.show', compact('gtk'));
    }

    public function edit(GTK $gtk): View
    {
        $gtk->loadMissing(['mataPelajaran', 'user']);

        $selectedIds = collect(old('mata_pelajaran', $gtk->mataPelajaran->pluck('id')->all()))
            ->filter()
            ->unique()
            ->values();

        $mataPelajarans = MataPelajaran::query()
            ->whereIn('id', $selectedIds)
            ->orderBy('nama_mapel')
            ->get();

        // Kirim daftar roles ke view agar superadmin bisa pilih multi-role user GTK
        $roles = \Spatie\Permission\Models\Role::orderBy('name')->pluck('name');
        // Roles yang saat ini dimiliki user (array of string)
        $currentRoles = $gtk->user?->getRoleNames()->toArray() ?? [];

        return view('gtk.edit', compact('gtk', 'mataPelajarans', 'roles', 'currentRoles'));
    }

    public function update(GTKUpdateRequest $request, GTK $gtk): RedirectResponse
    {
        $validated = $request->validated();

        // Pastikan user ada
        $email = $validated['kd_guru'].'@school.local';
        $user = User::where('email', $email)->first();
        $password = Hash::make($this->defaultPasswordFromNik($validated));
        if (! $user) {
            $user = User::create([
                'name' => $validated['nama_lengkap'],
                'email' => $email,
                'password' => $password,
                'role_utama' => 'gtk',
            ]);
            $user->assignRole('gtk');
        } else {
            $user->forceFill([
                'name' => $validated['nama_lengkap'],
                'password' => $password,
            ])->save();
        }
        $validated['user_id'] = $user->id;

        // Superadmin dapat mengubah role Spatie dan role_utama user GTK
        if ($request->user()->hasRole('superadmin') && $request->has('roles')) {
            $newRoles = array_filter((array) ($validated['roles'] ?? []));

            if (! empty($newRoles)) {
                // Sync multi-role — mengganti semua role lama dengan pilihan baru
                $user->syncRoles($newRoles);

                // role_utama = role pertama dalam array (jabatan struktural utama)
                // Prioritas: gtk > role lain, agar GTK tetap teridentifikasi sbg guru
                $roleUtama = in_array('gtk', $newRoles) ? 'gtk' : $newRoles[0];
                $user->forceFill(['role_utama' => $roleUtama])->save();

                Log::channel('sis')->info('[GTK] Multi-role diubah oleh superadmin', [
                    'gtk_id' => $gtk->id,
                    'user_id' => $user->id,
                    'new_roles' => $newRoles,
                    'role_utama' => $roleUtama,
                    'by' => $request->user()->id,
                ]);
            }
        }

        Log::channel('sis')->info('[GTK] Update GTK', [
            'gtk_id' => $gtk->id,
            'kd_guru' => $validated['kd_guru'],
            'user_id' => $user->id,
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('gtk', 'public');
        }

        // Buang field roles dari array validated sebelum update GTK (bukan kolom di tabel gtks)
        unset($validated['roles']);

        $gtk->update($validated);

        $gtk->mataPelajaran()->sync($request->mata_pelajaran ?: []);

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
                if (empty($validated['kd_guru']) || empty($validated['nama_lengkap']) || empty($validated['nik'])) {
                    throw new \Exception('kd_guru, nik, dan nama_lengkap wajib diisi');
                }

                if (GTK::where('kd_guru', $validated['kd_guru'])->exists()) {
                    throw new \Exception('kd_guru sudah ada');
                }

                // Buat user otomatis jika belum ada
                $email = $validated['kd_guru'].'@smkn5.ac.id';
                $user = User::where('email', $email)->first();
                $password = Hash::make($this->defaultPasswordFromNik($validated));
                if (! $user) {
                    $user = User::create([
                        'name' => $validated['nama_lengkap'],
                        'email' => $email,
                        'password' => $password,
                        'role_utama' => 'gtk',
                    ]);
                    $user->assignRole('gtk');
                    Log::channel('sis')->info('[GTK Import] User baru dibuat', [
                        'user_id' => $user->id,
                        'email' => $email,
                        'kd_guru' => $validated['kd_guru'],
                    ]);
                } else {
                    $user->forceFill([
                        'name' => $validated['nama_lengkap'],
                        'password' => $password,
                    ])->save();
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
