<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ResetPasswordSiswa extends Command
{
    protected $signature   = 'siswa:reset-password';
    protected $description = 'Reset semua password siswa menjadi NISN';

    public function handle()
    {
        $users = User::where('role_utama', 'siswa')
                     ->whereNotNull('siswa_id')
                     ->with('siswa')
                     ->get();

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        $updated = 0;
        $skipped = 0;

        foreach ($users as $user) {
            if ($user->siswa && $user->siswa->nisn) {
                $user->update(['password' => bcrypt($user->siswa->nisn)]);
                $updated++;
            } else {
                $skipped++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ Berhasil: {$updated} akun");
        $this->warn("⚠️  Dilewati (nisn kosong): {$skipped} akun");
    }
}