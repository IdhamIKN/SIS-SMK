<?php

namespace App\Http\Controllers\Siswa;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use App\Imports\SiswaImport;
use App\Exports\SiswaExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class SiswaController extends Controller
{
    public function index(Request $request): View
    {
        Log::channel('sis')->info('[Siswa] Index access', [
            'user_id' => $request->user()->id,
        ]);

        $siswas = Siswa::query()
            ->with(['kelas', 'user'])
            ->when($request->search, fn($q) => $q->where('nama_lengkap', 'like', '%' . $request->search . '%')
                ->orWhere('nis', 'like', '%' . $request->search . '%')
                ->orWhere('nisn', 'like', '%' . $request->search . '%'))
            ->when($request->kelas_id, fn($q) => $q->where('kelas_id', $request->kelas_id))
            ->when($request->status_aktif !== null, fn($q) => $q->where('status_aktif', $request->boolean('status_aktif')))
            ->paginate(20);

        $kelas = Kelas::orderBy('nama_kelas')->get();

        return view('siswa.index', compact('siswas', 'kelas'));
    }

    public function create(): View
    {
        $kelas = Kelas::orderBy('nama_kelas')->get();
        return view('siswa.create', compact('kelas'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nis' => 'nullable|string|max:20|unique:siswas',
            'nisn' => 'required|string|max:20|unique:siswas',
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'kelas_id' => 'required|exists:kelas,id',
            'angkatan' => 'nullable|integer|min:2000|max:' . (date('Y') + 1),
            'foto' => 'nullable|image|max:2048',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date|before:today',
            'alamat' => 'nullable|string|max:500',
            'no_hp_siswa' => 'nullable|string|max:20',
            'no_hp_ortu1' => 'nullable|string|max:20',
            'no_hp_ortu2' => 'nullable|string|max:20',
            'nama_ortu1' => 'nullable|string|max:100',
            'nama_ortu2' => 'nullable|string|max:100',
            'nama_wali' => 'nullable|string|max:100',
            'status_aktif' => 'boolean',
        ]);

        Log::channel('sis')->info('[Siswa] Create new siswa', [
            'nisn' => $validated['nisn'],
            'nama' => $validated['nama_lengkap'],
            'user_id' => $request->user()->id,
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('siswa', 'public');
        }

        Siswa::create($validated);

        return redirect()->route('siswa.index')->with('success', 'Siswa berhasil ditambahkan');
    }

    public function show(Siswa $siswa): View
    {
        $siswa->load(['kelas', 'user', 'absenSiswa' => fn($q) => $q->latest()->limit(10)]);
        return view('siswa.show', compact('siswa'));
    }

    public function edit(Siswa $siswa): View
    {
        $kelas = Kelas::orderBy('nama_kelas')->get();
        return view('siswa.edit', compact('siswa', 'kelas'));
    }

    public function update(Request $request, Siswa $siswa): RedirectResponse
    {
        $validated = $request->validate([
            'nis' => 'nullable|string|max:20|unique:siswas,nis,' . $siswa->id,
            'nisn' => 'required|string|max:20|unique:siswas,nisn,' . $siswa->id,
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'kelas_id' => 'required|exists:kelas,id',
            'angkatan' => 'nullable|integer|min:2000|max:' . (date('Y') + 1),
            'foto' => 'nullable|image|max:2048',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date|before:today',
            'alamat' => 'nullable|string|max:500',
            'no_hp_siswa' => 'nullable|string|max:20',
            'no_hp_ortu1' => 'nullable|string|max:20',
            'no_hp_ortu2' => 'nullable|string|max:20',
            'nama_ortu1' => 'nullable|string|max:100',
            'nama_ortu2' => 'nullable|string|max:100',
            'nama_wali' => 'nullable|string|max:100',
            'status_aktif' => 'boolean',
        ]);

        Log::channel('sis')->info('[Siswa] Update siswa', [
            'siswa_id' => $siswa->id,
            'nisn' => $validated['nisn'],
            'user_id' => $request->user()->id,
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('siswa', 'public');
        }

        $siswa->update($validated);

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

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        Log::channel('sis')->info('[Siswa] Import Excel', [
            'filename' => $request->file('file')->getClientOriginalName(),
            'user_id' => $request->user()->id,
        ]);

        Excel::import(new SiswaImport, $request->file('file'));

        return back()->with('success', 'Data siswa berhasil diimport');
    }

    public function export(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        Log::channel('sis')->info('[Siswa] Export Excel', [
            'user_id' => request()->user()->id,
        ]);

        return Excel::download(new SiswaExport, 'data_siswa_' . date('Y-m-d') . '.xlsx');
    }

    public function exportCV(Siswa $siswa)
    {
        Log::channel('sis')->info('[Siswa] Export CV PDF', [
            'siswa_id' => $siswa->id,
            'user_id' => request()->user()->id,
        ]);

        $siswa->load(['kelas', 'absenSiswa' => fn($q) => $q->whereYear('tanggal', date('Y'))]);

        $pdf = Pdf::loadView('pdf.cv_siswa', compact('siswa'));

        return $pdf->download('CV_' . $siswa->nama_lengkap . '.pdf');
    }
}
