<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\KelasStoreRequest;
use App\Http\Requests\KelasUpdateRequest;
use App\Models\AcademicYear;
use App\Models\GTK;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\StudentPromotion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class KelasController extends Controller
{
    /**
     * Display a listing of the resource.
     * PERF: 3 queries (kelas + jurusan + waliKelas + bk via eager load)
     */
    public function index(Request $request): View
    {
        Log::channel('sis')->info('[Kelas] Index access', [
            'user_id' => $request->user()->id,
            'filters' => $request->only(['search', 'jurusan_id', 'tingkat']),
        ]);

        $kelas = Kelas::with(['jurusan', 'waliKelas', 'bk', 'siswa'])
            ->withCount('siswa')
            ->when($request->search, function ($q, $search) {
                $q->where('nama_kelas', 'like', '%'.$search.'%');
            })
            ->when($request->jurusan_id, function ($q, $jurusanId) {
                $q->where('jurusan_id', $jurusanId);
            })
            ->when($request->tingkat, function ($q, $tingkat) {
                $q->where('tingkat', $tingkat);
            })
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->paginate(15);

        $jurusans = Jurusan::orderBy('nama_jurusan')->get();

        return view('kelas.index', compact('kelas', 'jurusans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        Log::channel('sis')->info('[Kelas] Create form opened', [
            'user_id' => auth()->id(),
        ]);

        $jurusans = Jurusan::orderBy('nama_jurusan')->get();
        $gtks = GTK::where('status_aktif', true)
            ->orderBy('nama_lengkap')
            ->get();

        return view('kelas.create', compact('jurusans', 'gtks'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(KelasStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Log::channel('sis')->info('[Kelas] Store started', [
            'nama_kelas' => $validated['nama_kelas'],
            'user_id' => $request->user()->id,
        ]);

        try {
            $kelas = Kelas::create($validated);

            Log::channel('sis')->info('[Kelas] Store success', [
                'kelas_id' => $kelas->id,
                'nama_kelas' => $kelas->nama_kelas,
            ]);

            return redirect()
                ->route('kelas.show', $kelas)
                ->with('success', 'Kelas '.$kelas->nama_kelas.' berhasil ditambahkan.');
        } catch (\Throwable $e) {
            Log::channel('sis')->error('[Kelas] Store failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan kelas: '.$e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     * PERF: 2 queries (kelas with relations + siswa count)
     */
    public function show(Kelas $kela): View
    {
        Log::channel('sis')->info('[Kelas] Show detail', [
            'kelas_id' => $kela->id,
            'nama_kelas' => $kela->nama_kelas,
            'user_id' => auth()->id(),
        ]);

        $kela->load(['jurusan', 'waliKelas', 'bk']);
        $siswa = $kela->siswa()
            ->withCount('absenSiswa')
            ->orderBy('nama_lengkap')
            ->paginate(20);

        return view('kelas.show', compact('kela', 'siswa'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Kelas $kela): View
    {
        Log::channel('sis')->info('[Kelas] Edit form opened', [
            'kelas_id' => $kela->id,
            'nama_kelas' => $kela->nama_kelas,
            'user_id' => auth()->id(),
        ]);

        $jurusans = Jurusan::orderBy('nama_jurusan')->get();
        $gtks = GTK::where('status_aktif', true)
            ->orderBy('nama_lengkap')
            ->get();

        return view('kelas.edit', compact('kela', 'jurusans', 'gtks'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(KelasUpdateRequest $request, Kelas $kela): RedirectResponse
    {
        $validated = $request->validated();

        Log::channel('sis')->info('[Kelas] Update started', [
            'kelas_id' => $kela->id,
            'nama_kelas' => $validated['nama_kelas'],
            'user_id' => $request->user()->id,
        ]);

        try {
            $kela->update($validated);

            Log::channel('sis')->info('[Kelas] Update success', [
                'kelas_id' => $kela->id,
                'nama_kelas' => $kela->nama_kelas,
            ]);

            return redirect()
                ->route('kelas.show', $kela)
                ->with('success', 'Kelas '.$kela->nama_kelas.' berhasil diperbarui.');
        } catch (\Throwable $e) {
            Log::channel('sis')->error('[Kelas] Update failed', [
                'kelas_id' => $kela->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui kelas: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kelas $kela): RedirectResponse
    {
        Log::channel('sis')->info('[Kelas] Delete started', [
            'kelas_id' => $kela->id,
            'nama_kelas' => $kela->nama_kelas,
            'user_id' => request()->user()->id,
        ]);

        // Cek apakah kelas masih memiliki siswa
        $jumlahSiswa = $kela->siswa()->count();
        if ($jumlahSiswa > 0) {
            Log::channel('sis')->warning('[Kelas] Delete blocked — kelas memiliki siswa', [
                'kelas_id' => $kela->id,
                'jumlah_siswa' => $jumlahSiswa,
            ]);

            return redirect()
                ->route('kelas.show', $kela)
                ->with('error', 'Kelas tidak dapat dihapus karena masih memiliki '.$jumlahSiswa.' siswa. Pindahkan siswa terlebih dahulu.');
        }

        try {
            $kela->delete();

            Log::channel('sis')->info('[Kelas] Delete success', [
                'kelas_id' => $kela->id,
                'nama_kelas' => $kela->nama_kelas,
            ]);

            return redirect()
                ->route('kelas.index')
                ->with('success', 'Kelas '.$kela->nama_kelas.' berhasil dihapus.');
        } catch (\Throwable $e) {
            Log::channel('sis')->error('[Kelas] Delete failed', [
                'kelas_id' => $kela->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('kelas.show', $kela)
                ->with('error', 'Gagal menghapus kelas: '.$e->getMessage());
        }
    }

    /**
     * Show promotion interface for a class.
     */
    public function promote(Kelas $kela): View
    {
        Log::channel('sis')->info('[Kelas] Promote interface accessed', [
            'kelas_id' => $kela->id,
            'nama_kelas' => $kela->nama_kelas,
            'user_id' => auth()->id(),
        ]);

        // Validate promotion eligibility
        $academicYear = AcademicYear::active()->first();
        if (! $academicYear) {
            return view('kelas.promote', [
                'kela' => $kela,
                'siswa' => collect(),
                'targetKelas' => collect(),
                'nextTingkat' => null,
                'error' => 'Tidak ada tahun ajaran aktif. Silakan atur tahun ajaran terlebih dahulu.',
            ]);
        }

        if (! $academicYear->canPromote()) {
            return view('kelas.promote', [
                'kela' => $kela,
                'siswa' => collect(),
                'targetKelas' => collect(),
                'nextTingkat' => null,
                'error' => 'Promosi belum dibuka atau sudah melewati deadline.',
            ]);
        }

        // Check if class has active academic year
        if ($kela->academic_year_id !== $academicYear->id) {
            return view('kelas.promote', [
                'kela' => $kela,
                'siswa' => collect(),
                'targetKelas' => collect(),
                'nextTingkat' => null,
                'error' => 'Kelas ini tidak termasuk dalam tahun ajaran aktif.',
            ]);
        }

        // Check if class can promote students
        if (! $kela->canPromoteStudents()) {
            return view('kelas.promote', [
                'kela' => $kela,
                'siswa' => collect(),
                'targetKelas' => collect(),
                'nextTingkat' => null,
                'error' => 'Kelas ini tidak memiliki siswa aktif yang dapat dipromosikan.',
            ]);
        }

        $siswa = $kela->siswa()
            ->where('academic_status', 'active')
            ->orderBy('nama_lengkap')
            ->get();

        // Determine next class level
        $nextTingkat = match ($kela->tingkat) {
            'X' => 'XI',
            'XI' => 'XII',
            default => null
        };

        // Find potential target classes (same jurusan, next level, ready for promotion)
        $targetKelas = [];
        if ($nextTingkat) {
            $targetKelas = Kelas::where('jurusan_id', $kela->jurusan_id)
                ->where('tingkat', $nextTingkat)
                ->where('academic_year_id', $academicYear->id)
                ->where('promotion_status', 'ready')
                ->orderBy('nama_kelas')
                ->get();
        }

        return view('kelas.promote', compact('kela', 'siswa', 'targetKelas', 'nextTingkat', 'academicYear'));
    }

    /**
     * Execute bulk student promotion with comprehensive validation.
     */
    public function executePromotion(Request $request, Kelas $kela): RedirectResponse
    {
        $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:siswas,id',
            'target_class_id' => 'required|exists:kelas,id',
        ]);

        $studentIds = $request->student_ids;
        $targetClassId = $request->target_class_id;

        // Get active academic year
        $academicYear = AcademicYear::active()->first();
        if (! $academicYear) {
            return redirect()->back()->with('error', 'Tidak ada tahun ajaran aktif.');
        }

        // Validate promotion eligibility
        if (! $academicYear->canPromote()) {
            return redirect()->back()->with('error', 'Promosi belum dibuka atau sudah melewati deadline.');
        }

        // Validate source class
        if ($kela->academic_year_id !== $academicYear->id) {
            return redirect()->back()->with('error', 'Kelas sumber tidak valid untuk tahun ajaran aktif.');
        }

        // Validate target class
        $targetClass = Kelas::find($targetClassId);
        if (! $targetClass || ! $targetClass->isReadyForPromotion()) {
            return redirect()->back()->with('error', 'Kelas tujuan belum siap menerima siswa baru.');
        }

        if ($targetClass->academic_year_id !== $academicYear->id) {
            return redirect()->back()->with('error', 'Kelas tujuan tidak valid untuk tahun ajaran aktif.');
        }

        // Validate students are in source class and eligible
        $validStudents = Siswa::whereIn('id', $studentIds)
            ->where('kelas_id', $kela->id)
            ->where('academic_status', 'active')
            ->get();

        if ($validStudents->count() !== count($studentIds)) {
            return redirect()->back()->with('error', 'Beberapa siswa tidak valid untuk dipromosikan.');
        }

        Log::channel('sis')->info('[Kelas] Execute promotion started', [
            'from_kelas_id' => $kela->id,
            'from_kelas' => $kela->nama_kelas,
            'target_class_id' => $targetClassId,
            'target_class' => $targetClass->nama_kelas,
            'student_count' => count($studentIds),
            'academic_year_id' => $academicYear->id,
            'user_id' => $request->user()->id,
        ]);

        try {
            // Use database transaction for data integrity
            DB::transaction(function () use ($validStudents, $targetClass, $kela, $academicYear, $request) {
                foreach ($validStudents as $siswa) {
                    // Update student class
                    $siswa->update(['kelas_id' => $targetClass->id]);

                    // Create promotion record
                    StudentPromotion::create([
                        'student_id' => $siswa->id,
                        'academic_year_id' => $academicYear->id,
                        'from_class_id' => $kela->id,
                        'to_class_id' => $targetClass->id,
                        'promotion_type' => 'promoted',
                        'promotion_date' => now(),
                        'approved_by' => $request->user()->id,
                        'notes' => 'Bulk promotion from '.$kela->nama_kelas.' to '.$targetClass->nama_kelas,
                    ]);
                }

                // Check if source class is now empty of active students
                $remainingActiveStudents = $kela->siswa()->where('academic_status', 'active')->count();
                if ($remainingActiveStudents === 0) {
                    $kela->markAsPromoted();
                }
            });

            Log::channel('sis')->info('[Kelas] Execute promotion success', [
                'from_kelas' => $kela->nama_kelas,
                'to_kelas' => $targetClass->nama_kelas,
                'student_count' => $validStudents->count(),
                'academic_year' => $academicYear->name,
            ]);

            return redirect()
                ->route('kelas.show', $kela)
                ->with('success', $validStudents->count().' siswa berhasil dipromosikan ke kelas '.$targetClass->nama_kelas);

        } catch (\Throwable $e) {
            Log::channel('sis')->error('[Kelas] Execute promotion failed', [
                'from_kelas' => $kela->nama_kelas,
                'target_class' => $targetClass->nama_kelas,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal mempromosikan siswa: '.$e->getMessage());
        }
    }
}
