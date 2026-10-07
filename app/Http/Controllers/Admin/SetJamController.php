<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SetJam;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class SetJamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index(Request $request): View
    // {
    //     $query = SetJam::query();

    //     if ($request->has('shif') && $request->shif) {
    //         $query->where('shif', $request->shif);
    //     }

    //     if ($request->filled('kelompok')) {
    //         $query->where('kelompok_jam', $request->kelompok);
    //     }

    //     if ($request->has('status') && $request->status !== '') {
    //         $query->where('statusjam', $request->boolean('status'));
    //     }

    //     $setJam = $query->orderBy('kelompok_jam')
    //         ->orderBy('id_jam')
    //         ->paginate(20);

    //     return view('admin.set_jam.index', compact('setJam'));
    // }
    public function index(Request $request): View
    {
        $query = SetJam::query();

        // Search nama jam
        if ($request->filled('search')) {
            $query->where('nama_jam', 'like', '%' . $request->search . '%');
        }

        // Filter shift
        if ($request->filled('shif')) {
            $query->where('shif', $request->shif);
        }

        // Filter kelompok
        if ($request->filled('kelompok')) {
            $query->where('kelompok_jam', $request->kelompok);
        }

        // Filter status
        if ($request->filled('status') && $request->status !== '') {
            $query->where('statusjam', $request->boolean('status'));
        }

        $setJam = $query->orderBy('kelompok_jam')
            ->orderBy('id_jam')
            ->paginate(20)
            ->withQueryString();

        return view('admin.set_jam.index', compact('setJam'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.set_jam.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_jam'       => ['required', 'integer', 'unique:tblsetjam,id_jam'],
            'shif'         => ['required', Rule::in(['Pagi', 'Siang', 'Sore', 'Malam'])],
            'kelompok_jam' => ['required', Rule::in(['reguler', 'reguler_1112', 'jumat'])],
            'nama_jam'     => ['required', 'string', 'max:50'],
            'time_in'      => ['required', 'date_format:H:i'],
            'limit_in'     => ['nullable', 'date_format:H:i'],
            'time_out'     => ['required', 'date_format:H:i'],
            'limit_out'    => ['nullable', 'date_format:H:i'],
            'statusjam'    => ['nullable', 'boolean'],
        ]);

        $validated = $this->normalizeSetJamData($validated);

        SetJam::create($validated);

        return redirect()->route('admin.set-jam.index')
            ->with('success', 'Jam pelajaran berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(SetJam $setJam): View
    {
        return view('admin.set_jam.show', compact('setJam'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SetJam $setJam): View
    {
        return view('admin.set_jam.edit', compact('setJam'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SetJam $setJam): RedirectResponse
    {
        $validated = $request->validate([
            'id_jam'       => ['required', 'integer', Rule::unique('tblsetjam', 'id_jam')->ignore($setJam->id_jam, 'id_jam')],
            'shif'         => ['required', Rule::in(['Pagi', 'Siang', 'Sore', 'Malam'])],
            'kelompok_jam' => ['required', Rule::in(['reguler', 'reguler_1112', 'jumat'])],
            'nama_jam'     => ['required', 'string', 'max:50'],
            'time_in'      => ['required', 'date_format:H:i'],
            'limit_in'     => ['nullable', 'date_format:H:i'],
            'time_out'     => ['required', 'date_format:H:i'],
            'limit_out'    => ['nullable', 'date_format:H:i'],
            'statusjam'    => ['nullable', 'boolean'],
        ]);

        $validated = $this->normalizeSetJamData($validated);

        $setJam->update($validated);

        return redirect()->route('admin.set-jam.index')
            ->with('success', 'Jam pelajaran berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SetJam $setJam): RedirectResponse
    {
        // Cek apakah jam masih digunakan di jadwal
        if (\App\Models\JadwalKBM::where('jam_ke', $setJam->id_jam)->exists()) {
            return back()->withErrors(['error' => 'Jam pelajaran tidak dapat dihapus karena masih digunakan dalam jadwal KBM']);
        }

        $setJam->delete();

        return redirect()->route('admin.set-jam.index')
            ->with('success', 'Jam pelajaran berhasil dihapus');
    }

    private function normalizeSetJamData(array $validated): array
    {
        foreach (['time_in', 'limit_in', 'time_out', 'limit_out'] as $field) {
            $validated[$field] = !empty($validated[$field]) ? $validated[$field] . ':00' : null;
        }

        $validated['statusjam'] = !empty($validated['statusjam']);

        return $validated;
    }
}
