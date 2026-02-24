<?php

namespace Database\Seeders;

use App\Models\County;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Skip if already seeded — counties are static reference data seeded first,
        // so their presence reliably indicates the DB has been seeded before.
        if (County::count() > 0) {
            $this->command->info('Database already seeded — skipping.');
            return;
        }

        User::factory()->create([
            'name' => 'Site Admin',
            'email' => 'admin@admin.com',
            'password' => 'admin',
            'role' => 'site_admin',
        ]);

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
            InstitutionSeeder::class,
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
