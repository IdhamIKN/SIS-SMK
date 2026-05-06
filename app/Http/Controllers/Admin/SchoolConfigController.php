<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SchoolConfigUpdateRequest;
use App\Models\Sekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SchoolConfigController extends Controller
{
    public function index()
    {
        $sekolah = Sekolah::aktif();
        return view('admin.school-config.index', compact('sekolah'));
    }

    public function update(SchoolConfigUpdateRequest $request)
    {
        $validated = $request->validated();

        $sekolah = Sekolah::aktif();

        // Prepare data for update
        $updateData = [];

        // Informasi Dasar Sekolah
        if (isset($validated['sekolah'])) {
            $updateData['sekolah'] = $validated['sekolah'];
        }
        if (isset($validated['alsekolah'])) {
            $updateData['alsekolah'] = $validated['alsekolah'];
        }
        if (isset($validated['telp'])) {
            $updateData['telp'] = $validated['telp'];
        }
        if (isset($validated['email'])) {
            $updateData['email'] = $validated['email'];
        }
        if (isset($validated['kab'])) {
            $updateData['kab'] = $validated['kab'];
        }
        if (isset($validated['alias'])) {
            $updateData['alias'] = $validated['alias'];
        }

        // Kepala Sekolah
        if (isset($validated['nama_ks'])) {
            $updateData['nama_ks'] = $validated['nama_ks'];
        }
        if (isset($validated['nip_ks'])) {
            $updateData['nip_ks'] = $validated['nip_ks'];
        }

        // Wakil Kepala Sekolah
        if (isset($validated['nama_waka'])) {
            $updateData['nama_waka'] = $validated['nama_waka'];
        }
        if (isset($validated['nip_waka'])) {
            $updateData['nip_waka'] = $validated['nip_waka'];
        }

        // Ketua
        if (isset($validated['nama_ketua'])) {
            $updateData['nama_ketua'] = $validated['nama_ketua'];
        }
        if (isset($validated['nip_ketua'])) {
            $updateData['nip_ketua'] = $validated['nip_ketua'];
        }

        // Website & Media
        if (isset($validated['site_url'])) {
            $updateData['site_url'] = $validated['site_url'];
        }
        if (isset($validated['site_logo'])) {
            $updateData['site_logo'] = $validated['site_logo'];
        }
        if (isset($validated['wasekolah'])) {
            $updateData['wasekolah'] = $validated['wasekolah'];
        }

        // Jam Sekolah
        if (isset($validated['jam_masuk'])) {
            $updateData['jam_masuk'] = $validated['jam_masuk'];
        }
        if (isset($validated['jam_pulang'])) {
            $updateData['jam_pulang'] = $validated['jam_pulang'];
        }
        if (isset($validated['hari_efektif'])) {
            $updateData['hari_efektif'] = json_encode($validated['hari_efektif']);
        }

        // Lokasi & Sistem
        if (isset($validated['latitude'])) {
            $updateData['latitude'] = $validated['latitude'];
        }
        if (isset($validated['longitude'])) {
            $updateData['longitude'] = $validated['longitude'];
        }
        if (isset($validated['system_name'])) {
            $updateData['system_name'] = $validated['system_name'];
        }

        $sekolah->update($updateData);

        // Clear cache untuk memastikan perubahan langsung terlihat
        Cache::forget('sekolah_data');
        Cache::forget('config');

        return redirect()->back()->with('success', 'Konfigurasi sekolah berhasil diperbarui.');
    }
}
