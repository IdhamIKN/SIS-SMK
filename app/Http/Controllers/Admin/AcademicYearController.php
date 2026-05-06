<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcademicYearStoreRequest;
use App\Http\Requests\AcademicYearUpdateRequest;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AcademicYearController extends Controller
{
    /**
     * Display a listing of academic years
     */
    public function index(): View
    {
        $academicYears = AcademicYear::with(['classes' => function ($query) {
            $query->withCount('siswa');
        }])->withCount('classes')->orderBy('year_start', 'desc')->paginate(10);

        return view('academic-years.index', compact('academicYears'));
    }

    /**
     * Show the form for creating a new academic year
     */
    public function create(): View
    {
        return view('academic-years.create');
    }

    /**
     * Store a newly created academic year
     */
    public function store(AcademicYearStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $data = $request->only(['year_start', 'year_end', 'promotion_deadline']);
        $data['name'] = $request->year_start.'/'.$request->year_end;
        $data['is_active'] = false;

        Log::channel('sis')->info('[AcademicYear] Create new academic year', [
            'name' => $data['name'],
            'user_id' => $request->user()->id,
        ]);

        $academicYear = AcademicYear::create($data);

        return redirect()
            ->route('academic-years.show', $academicYear)
            ->with('success', 'Tahun ajaran '.$academicYear->name.' berhasil dibuat.');
    }

    /**
     * Display the specified academic year
     */
    public function show(AcademicYear $academicYear): View
    {
        $classes = $academicYear->classes()
            ->with(['jurusan', 'waliKelas'])
            ->withCount('siswa')
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        $promotionStats = [
            'total_classes' => $classes->count(),
            'ready_classes' => $classes->where('promotion_status', 'ready')->count(),
            'promoted_classes' => $classes->where('promotion_status', 'promoted')->count(),
            'graduated_classes' => $classes->where('promotion_status', 'graduated')->count(),
            'total_students' => $classes->sum('siswa_count'),
        ];

        return view('academic-years.show', compact('academicYear', 'classes', 'promotionStats'));
    }

    /**
     * Show the form for editing the academic year
     */
    public function edit(AcademicYear $academicYear): View
    {
        return view('academic-years.edit', compact('academicYear'));
    }

    /**
     * Update the specified academic year
     */
    public function update(AcademicYearUpdateRequest $request, AcademicYear $academicYear): RedirectResponse
    {
        $validated = $request->validated();

        $academicYear->update($request->only(['year_start', 'year_end', 'promotion_deadline']));

        Log::channel('sis')->info('[AcademicYear] Updated promotion deadline', [
            'academic_year_id' => $academicYear->id,
            'deadline' => $academicYear->promotion_deadline,
            'user_id' => $request->user()->id,
        ]);

        return redirect()
            ->route('academic-years.show', $academicYear)
            ->with('success', 'Tahun ajaran berhasil diperbarui.');
    }

    /**
     * Set academic year as active
     */
    public function setActive(AcademicYear $academicYear): RedirectResponse
    {
        // Deactivate all other academic years
        AcademicYear::where('is_active', true)->update(['is_active' => false]);

        // Activate this one
        $academicYear->update(['is_active' => true]);

        Log::channel('sis')->info('[AcademicYear] Set as active', [
            'academic_year_id' => $academicYear->id,
            'name' => $academicYear->name,
            'user_id' => auth()->id(),
        ]);

        return redirect()
            ->route('academic-years.show', $academicYear)
            ->with('success', 'Tahun ajaran '.$academicYear->name.' telah diaktifkan.');
    }

    /**
     * Initialize promotion waves for the academic year
     */
    public function initializePromotionWaves(AcademicYear $academicYear): RedirectResponse
    {
        $waves = [
            'wave_1' => ['from' => 'X', 'to' => 'XI', 'deadline' => now()->addDays(30)->format('Y-m-d')],
            'wave_2' => ['from' => 'XI', 'to' => 'XII', 'deadline' => now()->addDays(60)->format('Y-m-d')],
            'wave_3' => ['from' => 'XII', 'to' => 'graduated', 'deadline' => now()->addDays(90)->format('Y-m-d')],
        ];

        $academicYear->update(['promotion_waves' => $waves]);

        // Update class promotion waves
        foreach ($waves as $waveName => $waveData) {
            $waveNumber = (int) str_replace('wave_', '', $waveName);

            if ($waveData['from'] !== 'XII') {
                $academicYear->classes()
                    ->where('tingkat', $waveData['from'])
                    ->update(['promotion_wave' => $waveNumber]);
            }
        }

        Log::channel('sis')->info('[AcademicYear] Initialized promotion waves', [
            'academic_year_id' => $academicYear->id,
            'waves' => $waves,
            'user_id' => auth()->id(),
        ]);

        return redirect()
            ->route('academic-years.show', $academicYear)
            ->with('success', 'Gelombang promosi berhasil diinisialisasi.');
    }
}
