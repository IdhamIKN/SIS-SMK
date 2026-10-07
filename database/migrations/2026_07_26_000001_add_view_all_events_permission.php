<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Migration: Tambah permission view_all_events
 *
 * Role yang OTOMATIS dapat permission ini:
 *   superadmin, kepsek, waka, kurikulum, admin_tatib, bk
 *
 * Role yang TIDAK otomatis (harus diberi manual via UI/seeder):
 *   gtk, wali_kelas
 *
 * Siswa tidak relevan — filter siswa pakai mekanisme berbeda (peserta event).
 */
return new class extends Migration
{
    public function up(): void
    {
        // Buat permission jika belum ada
        $permission = Permission::firstOrCreate([
            'name'       => 'view_all_events',
            'guard_name' => 'web',
        ]);

        // Role yang otomatis dapat permission ini
        $autoRoles = [
            'superadmin',
            'kepsek',
            'waka',
            'kurikulum',
            'admin_tatib',
            'bk',
        ];

        foreach ($autoRoles as $roleName) {
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();
            if ($role && !$role->hasPermissionTo('view_all_events')) {
                $role->givePermissionTo($permission);
            }
        }
    }

    public function down(): void
    {
        // Cabut dari semua role dulu, baru hapus permission-nya
        $permission = Permission::where('name', 'view_all_events')
            ->where('guard_name', 'web')
            ->first();

        if ($permission) {
            // Spatie akan otomatis hapus pivot saat permission dihapus
            $permission->delete();
        }
    }
};