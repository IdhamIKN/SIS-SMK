<?php

namespace Database\Seeders;

use App\Models\Siswa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class UpdateSiswaPasswordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Log::info('[UpdateSiswaPasswordSeeder] Mulai update password siswa ke NISN');

        $siswas = Siswa::with('user')->whereHas('user')->get();
        $updatedCount = 0;

        foreach ($siswas as $siswa) {
            if ($siswa->user && $siswa->nisn) {
                // Update password ke NISN
                $siswa->user->update([
                    'password' => $siswa->getHashedDefaultPassword()
                ]);

                Log::info('[UpdateSiswaPasswordSeeder] Password updated', [
                    'siswa_id' => $siswa->id,
                    'nisn' => $siswa->nisn,
                    'user_id' => $siswa->user->id,
                ]);

                $updatedCount++;
            }
        }

        Log::info('[UpdateSiswaPasswordSeeder] Selesai update password', [
            'total_updated' => $updatedCount,
        ]);

        $this->command->info("Berhasil update password {$updatedCount} siswa ke NISN");
    }
}