<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Skip if already seeded — safe to re-run on every deploy
        if (User::where('email', 'krmoble@gmail.com')->exists()) {
            $this->command->info('Database already seeded — skipping.');
            return;
        }

        User::factory()->create([
            'name' => 'Keith Rich',
            'email' => 'krmoble@gmail.com',
            'password' => 'password',
            'role' => 'admin',
        ]);

        // Phase 1 seeders
        $this->call([
            CountySeeder::class,
            DepartmentSeeder::class,
            EmployeeSeeder::class,
            AcademicYearSeeder::class,
            CourseSeeder::class,
            StudentSeeder::class,
            ClassSeeder::class,
            EnrollmentSeeder::class,
        ]);

        // Phase 3 seeders
        $this->call([
            GradingScaleSeeder::class,
            AssessmentCategorySeeder::class,
            AssessmentSeeder::class,
            GradeSeeder::class,
        ]);

        // Phase 4 seeders
        $this->call([
            AttendanceSeeder::class,
        ]);
    }
}
