<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * ViewAllEventsPermissionSeeder
 *
 * Buat permission 'view_all_events' dan assign ke role yang sesuai.
 * Jalankan dengan: php artisan db:seed --class=ViewAllEventsPermissionSeeder
 *
 * Atau tambahkan ke DatabaseSeeder:
 *   $this->call(ViewAllEventsPermissionSeeder::class);
 */
class ViewAllEventsPermissionSeeder extends Seeder
{
    /**
     * Role yang OTOMATIS mendapat permission view_all_events.
     * gtk dan wali_kelas TIDAK ada di sini —
     * mereka harus diberi secara manual per user via UI/Filament.
     */
    protected array $autoRoles = [
        'superadmin',
        'kepsek',
        'waka',
        'kurikulum',
        'admin_tatib',
        'bk',
    ];

    public function run(): void
    {
        // Reset cache Spatie agar tidak pakai cache lama
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Buat permission jika belum ada
        $permission = Permission::firstOrCreate([
            'name'       => 'view_all_events',
            'guard_name' => 'web',
        ]);

        $this->command->info('Permission [view_all_events] siap.');

        // Assign ke role yang otomatis
        foreach ($this->autoRoles as $roleName) {
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();

            if (!$role) {
                $this->command->warn("Role [{$roleName}] tidak ditemukan, dilewati.");
                continue;
            }

            if (!$role->hasPermissionTo('view_all_events')) {
                $role->givePermissionTo($permission);
                $this->command->info("  ✓ [{$roleName}] → view_all_events");
            } else {
                $this->command->line("  – [{$roleName}] sudah punya permission ini.");
            }
        }

        $this->command->info('');
        $this->command->info('Role gtk & wali_kelas TIDAK otomatis dapat permission ini.');
        $this->command->info('Assign manual per user via: $user->givePermissionTo(\'view_all_events\')');
    }
}