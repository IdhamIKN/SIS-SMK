<?php

namespace App\Http\Controllers\GTK;

use App\Models\GTK;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GTKController extends Controller
{
    public function index(Request $request): View
    {
        Log::channel('sis')->info('[GTK] Index access', [
            'user_id' => $request->user()->id,
        ]);

        $gtks = GTK::query()
            ->when($request->search, fn($q) => $q->where('nama_lengkap', 'like', '%' . $request->search . '%'))
            ->when($request->jabatan, fn($q) => $q->where('jabatan', $request->jabatan))
            ->paginate(20);

        return view('gtk.index', compact('gtks'));
    }

    public function create(): View
    {
        return view('gtk.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kd_guru' => 'required|string|max:10|unique:gtks',
            'nip' => 'nullable|string|max:20',
            'nuptk' => 'nullable|string|max:20',
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'no_hp' => 'nullable|string|max:20',
            'foto' => 'nullable|image|max:2048',
            'mata_pelajaran' => 'nullable|string|max:255',
            'jabatan' => 'required|string|max:255',
            'status_aktif' => 'boolean',
            'acc_absen' => 'boolean',
            'acc_kurikulum' => 'boolean',
            'acc_jurnal' => 'boolean',
            'acc_bk' => 'boolean',
            'guru_piket' => 'boolean',
            'acc_profil' => 'boolean',
            'group_acc' => 'boolean',
            'view_siswa' => 'required|in:limit,full',
        ]);

        Log::channel('sis')->info('[GTK] Create new GTK', [
            'kd_guru' => $validated['kd_guru'],
            'nama' => $validated['nama_lengkap'],
            'user_id' => $request->user()->id,
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

    public function update(Request $request, GTK $gtk): RedirectResponse
    {
        $validated = $request->validate([
            'kd_guru' => 'required|string|max:10|unique:gtks,kd_guru,' . $gtk->id,
            'nip' => 'nullable|string|max:20',
            'nuptk' => 'nullable|string|max:20',
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'no_hp' => 'nullable|string|max:20',
            'foto' => 'nullable|image|max:2048',
            'mata_pelajaran' => 'nullable|string|max:255',
            'jabatan' => 'required|string|max:255',
            'status_aktif' => 'boolean',
            'acc_absen' => 'boolean',
            'acc_kurikulum' => 'boolean',
            'acc_jurnal' => 'boolean',
            'acc_bk' => 'boolean',
            'guru_piket' => 'boolean',
            'acc_profil' => 'boolean',
            'group_acc' => 'boolean',
            'view_siswa' => 'required|in:limit,full',
        ]);

        Log::channel('sis')->info('[GTK] Update GTK', [
            'gtk_id' => $gtk->id,
            'kd_guru' => $validated['kd_guru'],
            'user_id' => $request->user()->id,
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
}
