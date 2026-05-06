<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create current academic year
        $currentYear = date('Y');
        AcademicYear::create([
            'name' => $currentYear.'/'.($currentYear + 1),
            'year_start' => $currentYear,
            'year_end' => $currentYear + 1,
            'is_active' => true,
            'promotion_deadline' => Carbon::now()->addMonths(6)->format('Y-m-d'),
            'promotion_waves' => [
                'wave_1' => [
                    'from' => 'X',
                    'to' => 'XI',
                    'deadline' => Carbon::now()->addMonths(2)->format('Y-m-d'),
                ],
                'wave_2' => [
                    'from' => 'XI',
                    'to' => 'XII',
                    'deadline' => Carbon::now()->addMonths(4)->format('Y-m-d'),
                ],
                'wave_3' => [
                    'from' => 'XII',
                    'to' => 'graduated',
                    'deadline' => Carbon::now()->addMonths(6)->format('Y-m-d'),
                ],
            ],
        ]);

        // Create next academic year (inactive)
        AcademicYear::create([
            'name' => ($currentYear + 1).'/'.($currentYear + 2),
            'year_start' => $currentYear + 1,
            'year_end' => $currentYear + 2,
            'is_active' => false,
            'promotion_deadline' => null,
            'promotion_waves' => null,
        ]);
    }
}
