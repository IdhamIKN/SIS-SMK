<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolesSeeder::class);
        $this->call(JurusanSeeder::class);
        $this->call(AcademicYearSeeder::class);
        $this->call(GTKSeeder::class);
        $this->call(KelasSeeder::class);
        $this->call(SiswaSeeder::class);
    }
}
