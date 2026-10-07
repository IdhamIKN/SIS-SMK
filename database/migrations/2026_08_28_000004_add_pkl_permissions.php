<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Tambahkan permission baru untuk fitur PKL.
 *
 * Permissions:
 *   - pkl.view          → lihat daftar lokasi & penugasan PKL (admin)
 *   - pkl.create        → buat lokasi & assign siswa ke lokasi PKL
 *   - pkl.update        → edit lokasi, batalkan/selesaikan penugasan, verifikasi jurnal
 *   - pkl.delete        → hapus lokasi PKL (soft delete)
 *   - pkl.siswa.view    → siswa mengakses dashboard & jurnal PKL miliknya
 *
 * Role assignment (default):
 *   - superadmin, admin → semua permission pkl.*
 *   - Siswa             → pkl.siswa.view (hanya tampil jika punya penugasan aktif)
 */
return new class extends Migration
{
    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'pkl.view',
            'pkl.create',
            'pkl.update',
            'pkl.delete',
            'pkl.siswa.view',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // Assign ke superadmin & admin
        $adminPermissions = ['pkl.view', 'pkl.create', 'pkl.update', 'pkl.delete'];

        foreach (['superadmin', 'admin', 'Tatib', 'Waka'] as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->givePermissionTo($adminPermissions);
            }
        }

        // Siswa mendapat akses dashboard PKL (controller akan cek penugasan aktif)
        $siswaRole = Role::where('name', 'Siswa')->first();
        if ($siswaRole) {
            $siswaRole->givePermissionTo('pkl.siswa.view');
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'pkl.view', 'pkl.create', 'pkl.update', 'pkl.delete', 'pkl.siswa.view',
        ];

        foreach ($permissions as $name) {
            Permission::where('name', $name)->delete();
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
