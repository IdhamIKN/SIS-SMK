<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MataPelajaranController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = MataPelajaran::query();

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('nama_mapel', 'like', "%{$search}%")
                  ->orWhere('kode_mapel', 'like', "%{$search}%");
        }

        if ($request->has('kategori') && $request->kategori) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('status_aktif', $request->boolean('status'));
        }

        $mataPelajaran = $query->orderBy('nama_mapel')->paginate(20);

        return view('admin.mata_pelajaran.index', compact('mataPelajaran'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.mata_pelajaran.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode_mapel' => 'required|string|max:10|unique:mata_pelajaran,kode_mapel',
            'nama_mapel' => 'required|string|max:255',
            'deskripsi' => 'nullable|string|max:1000',
            'kategori' => 'required|in:umum,jurusan,mulok',
            'status_aktif' => 'boolean',
        ]);

        MataPelajaran::create($validated);

        return redirect()->route('admin.mata-pelajaran.index')
            ->with('success', 'Mata pelajaran berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(MataPelajaran $mataPelajaran): View
    {
        $jadwalKBM = $mataPelajaran->jadwalKBM()
            ->with(['kelas', 'gtk'])
            ->orderBy('hari')
            ->orderBy('jam_ke')
            ->paginate(10);

        return view('admin.mata_pelajaran.show', compact('mataPelajaran', 'jadwalKBM'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MataPelajaran $mataPelajaran): View
    {
        return view('admin.mata_pelajaran.edit', compact('mataPelajaran'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MataPelajaran $mataPelajaran): RedirectResponse
    {
        $validated = $request->validate([
            'kode_mapel' => 'required|string|max:10|unique:mata_pelajaran,kode_mapel,' . $mataPelajaran->id,
            'nama_mapel' => 'required|string|max:255',
            'deskripsi' => 'nullable|string|max:1000',
            'kategori' => 'required|in:umum,jurusan,mulok',
            'status_aktif' => 'boolean',
        ]);

        $mataPelajaran->update($validated);

        return redirect()->route('admin.mata-pelajaran.index')
            ->with('success', 'Mata pelajaran berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MataPelajaran $mataPelajaran): RedirectResponse
    {
        // Cek apakah mata pelajaran masih digunakan di jadwal
        if ($mataPelajaran->jadwalKBM()->exists()) {
            return back()->withErrors(['error' => 'Mata pelajaran tidak dapat dihapus karena masih digunakan dalam jadwal KBM']);
        }

        $mataPelajaran->delete();

        return redirect()->route('admin.mata-pelajaran.index')
            ->with('success', 'Mata pelajaran berhasil dihapus');
    }
}
