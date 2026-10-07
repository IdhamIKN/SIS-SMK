<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Migration: Fix permission event untuk semua role.
 *
 * Bug: Permission event.* dan event-guru.* tidak pernah dibuat/di-assign,
 * sehingga guru tidak bisa akses event, dan siswa bisa kena 403 di event juga.
 *
 * Role assignment:
 *   event.view       → siswa, gtk, wali_kelas, superadmin, kepsek, kepala_sekolah,
 *                       waka, kurikulum, admin_tatib, bk
 *   event.create     → gtk, wali_kelas, superadmin, kepsek, kepala_sekolah,
 *                       waka, kurikulum, admin_tatib, bk
 *   event.update     → gtk, wali_kelas, superadmin, kepsek, kepala_sekolah,
 *                       waka, kurikulum, admin_tatib, bk
 *   event.delete     → superadmin, kepsek, kepala_sekolah, waka, admin_tatib
 *   event.barcode    → gtk, wali_kelas, superadmin, kepsek, kepala_sekolah,
 *                       waka, kurikulum, admin_tatib, bk
 *   event.scan       → siswa
 *   event.rekap      → siswa, gtk, wali_kelas, superadmin, kepsek, kepala_sekolah,
 *                       waka, kurikulum, admin_tatib, bk
 *   event.export     → gtk, wali_kelas, superadmin, kepsek, kepala_sekolah,
 *                       waka, kurikulum, admin_tatib, bk
 *   event.jurnal     → gtk, wali_kelas, superadmin, kepsek, kepala_sekolah,
 *                       waka, kurikulum, admin_tatib, bk
 *
 *   event-guru.view  → gtk, wali_kelas, superadmin, kepsek, kepala_sekolah,
 *                       waka, kurikulum, admin_tatib, bk
 *   event-guru.create   → superadmin, kepsek, kepala_sekolah, waka, admin_tatib, bk
 *   event-guru.update   → superadmin, kepsek, kepala_sekolah, waka, admin_tatib, bk
 *   event-guru.delete   → superadmin, kepsek, kepala_sekolah, waka, admin_tatib
 *   event-guru.barcode  → gtk, wali_kelas, superadmin, kepsek, kepala_sekolah,
 *                          waka, kurikulum, admin_tatib, bk
 *   event-guru.scan     → gtk, wali_kelas
 *   event-guru.rekap    → gtk, wali_kelas, superadmin, kepsek, kepala_sekolah,
 *                          waka, kurikulum, admin_tatib, bk
 *   event-guru.export   → superadmin, kepsek, kepala_sekolah, waka, admin_tatib, bk
 */
return new class extends Migration
{
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Definisi permission dan role yang berhak ────────────────────────
        $eventPermissions = [
            'event.view'   => ['siswa', 'gtk', 'wali_kelas', 'superadmin', 'kepsek', 'kepala_sekolah', 'waka', 'kurikulum', 'admin_tatib', 'bk'],
            'event.create' => ['gtk', 'wali_kelas', 'superadmin', 'kepsek', 'kepala_sekolah', 'waka', 'kurikulum', 'admin_tatib', 'bk'],
            'event.update' => ['gtk', 'wali_kelas', 'superadmin', 'kepsek', 'kepala_sekolah', 'waka', 'kurikulum', 'admin_tatib', 'bk'],
            'event.delete' => ['superadmin', 'kepsek', 'kepala_sekolah', 'waka', 'admin_tatib'],
            'event.barcode'=> ['gtk', 'wali_kelas', 'superadmin', 'kepsek', 'kepala_sekolah', 'waka', 'kurikulum', 'admin_tatib', 'bk'],
            'event.scan'   => ['siswa'],
            'event.rekap'  => ['siswa', 'gtk', 'wali_kelas', 'superadmin', 'kepsek', 'kepala_sekolah', 'waka', 'kurikulum', 'admin_tatib', 'bk'],
            'event.export' => ['gtk', 'wali_kelas', 'superadmin', 'kepsek', 'kepala_sekolah', 'waka', 'kurikulum', 'admin_tatib', 'bk'],
            'event.jurnal' => ['gtk', 'wali_kelas', 'superadmin', 'kepsek', 'kepala_sekolah', 'waka', 'kurikulum', 'admin_tatib', 'bk'],

            'event-guru.view'   => ['gtk', 'wali_kelas', 'superadmin', 'kepsek', 'kepala_sekolah', 'waka', 'kurikulum', 'admin_tatib', 'bk'],
            'event-guru.create' => ['superadmin', 'kepsek', 'kepala_sekolah', 'waka', 'admin_tatib', 'bk'],
            'event-guru.update' => ['superadmin', 'kepsek', 'kepala_sekolah', 'waka', 'admin_tatib', 'bk'],
            'event-guru.delete' => ['superadmin', 'kepsek', 'kepala_sekolah', 'waka', 'admin_tatib'],
            'event-guru.barcode'=> ['gtk', 'wali_kelas', 'superadmin', 'kepsek', 'kepala_sekolah', 'waka', 'kurikulum', 'admin_tatib', 'bk'],
            'event-guru.scan'   => ['gtk', 'wali_kelas'],
            'event-guru.rekap'  => ['gtk', 'wali_kelas', 'superadmin', 'kepsek', 'kepala_sekolah', 'waka', 'kurikulum', 'admin_tatib', 'bk'],
            'event-guru.export' => ['superadmin', 'kepsek', 'kepala_sekolah', 'waka', 'admin_tatib', 'bk'],
        ];

        foreach ($eventPermissions as $permName => $roleNames) {
            $permission = Permission::firstOrCreate([
                'name'       => $permName,
                'guard_name' => 'web',
            ]);

            foreach ($roleNames as $roleName) {
                $role = Role::where('name', $roleName)
                    ->where('guard_name', 'web')
                    ->first();

                if ($role && ! $role->hasPermissionTo($permName)) {
                    $role->givePermissionTo($permission);
                }
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'event.view', 'event.create', 'event.update', 'event.delete',
            'event.barcode', 'event.scan', 'event.rekap', 'event.export', 'event.jurnal',
            'event-guru.view', 'event-guru.create', 'event-guru.update', 'event-guru.delete',
            'event-guru.barcode', 'event-guru.scan', 'event-guru.rekap', 'event-guru.export',
        ];

        foreach ($permissions as $permName) {
            $perm = Permission::where('name', $permName)->where('guard_name', 'web')->first();
            if ($perm) {
                // Cabut dari semua role dulu
                foreach (Role::all() as $role) {
                    if ($role->hasPermissionTo($permName)) {
                        $role->revokePermissionTo($perm);
                    }
                }
                $perm->delete();
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
