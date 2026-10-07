<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * DashboardLaporanPermissionSeeder
 *
 * Buat permission 'dashboard-laporan.view' dan assign ke role yang sesuai.
 * Jalankan dengan: php artisan db:seed --class=DashboardLaporanPermissionSeeder
 */
class DashboardLaporanPermissionSeeder extends Seeder
{
    protected array $autoRoles = [
        'superadmin',
        'kepala_sekolah',
        'kepsek',
        'waka',
        'admin_tatib',
        'bk',
        'kurikulum',
    ];

    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permission = Permission::firstOrCreate([
            'name'       => 'dashboard-laporan.view',
            'guard_name' => 'web',
        ]);

        $this->command->info('Permission [dashboard-laporan.view] siap.');

        foreach ($this->autoRoles as $roleName) {
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();

            if (! $role) {
                $this->command->warn("  Role [{$roleName}] tidak ditemukan, dilewati.");
                continue;
            }

            if (! $role->hasPermissionTo('dashboard-laporan.view')) {
                $role->givePermissionTo($permission);
                $this->command->info("  ✓ [{$roleName}] → dashboard-laporan.view");
            } else {
                $this->command->line("  – [{$roleName}] sudah punya permission ini.");
            }
        }

        $this->command->info('');
        $this->command->info('Selesai! Dashboard Laporan Aktivitas siap diakses.');
    }
}
