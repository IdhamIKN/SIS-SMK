<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class DeveloperUserSeeder extends Seeder
{
    /**
     * Buat akun developer berdasarkan DEVELOPER_EMAIL di .env.
     *
     * Seeder ini aman dijalankan berulang kali (idempotent):
     * - Jika user sudah ada → update name & pastikan role superadmin ter-assign.
     * - Jika belum ada → buat baru.
     *
     * Cara pakai:
     *   php artisan db:seed --class=DeveloperUserSeeder
     */
    public function run(): void
    {
        // Reset permission cache agar assignRole tidak error
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $developerEmails = array_filter(array_map(
            'trim',
            explode(',', env('DEVELOPER_EMAIL', ''))
        ));

        if (empty($developerEmails)) {
            $this->command->warn('  DEVELOPER_EMAIL tidak diset di .env. Seeder dilewati.');
            return;
        }

        foreach ($developerEmails as $email) {
            $user = User::where('email', $email)->first();

            if ($user) {
                // Sudah ada — pastikan role superadmin ter-assign
                if (! $user->hasRole('superadmin')) {
                    $user->assignRole('superadmin');
                    $this->command->line("  <comment>Role superadmin ditambahkan ke:</comment> {$email}");
                } else {
                    $this->command->line("  <info>User sudah ada (tidak diubah):</info> {$email}");
                }
            } else {
                // Belum ada — buat baru
                // Password default: bagian sebelum @ dari email
                $defaultPassword = strstr($email, '@', true) ?: 'developer123';

                $user = User::create([
                    'name'       => 'Developer',
                    'email'      => $email,
                    'password'   => Hash::make($defaultPassword),
                    'role_utama' => 'superadmin',
                ]);

                $user->assignRole('superadmin');

                $this->command->line("  <info>Developer user dibuat:</info> {$email}");
                $this->command->line("  <comment>Password default:</comment> {$defaultPassword}");
                $this->command->warn('  ⚠  Segera ganti password setelah login pertama!');
            }

            $this->command->line("  <info>Akses dev panel:</info> " . env('APP_URL', 'http://localhost') . "/dev-panel");
        }
    }
}
