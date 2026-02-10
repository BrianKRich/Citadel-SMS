<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        $academicYear = AcademicYear::create([
            'name' => '2025-2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
            'is_current' => true,
        ]);

        // Create terms for the academic year
        $academicYear->terms()->createMany([
            [
                'name' => 'Fall 2025',
                'start_date' => '2025-09-01',
                'end_date' => '2025-12-20',
                'is_current' => true,
            ],
            [
                'name' => 'Spring 2026',
                'start_date' => '2026-01-10',
                'end_date' => '2026-06-30',
                'is_current' => false,
            ],
        ]);
    }
}
