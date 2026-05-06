<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles sesuai spec section 2
        $roles = [
            'superadmin',
            'kepala_sekolah',
            'waka',
            'admin_tatib',
            'gtk',
            'bk',
            'kurikulum',
            'wali_kelas',
            'siswa',
        ];

        foreach ($roles as $role) {
            Role::create(['name' => $role]);
        }

        // Create superadmin user
        $superadmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@smkn5madiun.id',
            'phone' => '085649925785',
            'role_utama' => 'superadmin',
            'password' => bcrypt('password'),  // Ganti di production
        ]);

        $superadmin->assignRole('superadmin');

        // Permissions (contoh - tambah sesuai kebutuhan)
        Permission::create(['name' => 'panel.realtime']);
        Permission::create(['name' => 'absen.manage']);
        // etc...
    }
}
