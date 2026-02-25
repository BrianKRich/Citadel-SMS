<?php

namespace Database\Seeders;

use App\Models\County;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeRole;
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

        $siteAdmin = User::factory()->create([
            'name' => 'Site Admin',
            'email' => 'admin@admin.com',
            'password' => 'admin',
            'role' => 'site_admin',
        ]);

        $keithRich = User::factory()->create([
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

        // Create employee records for the two default admin users
        $adminDept = Department::where('name', 'Administration')->first();
        $adminRole = EmployeeRole::where('department_id', $adminDept->id)->where('name', 'Administrator')->first();

        Employee::create([
            'user_id'       => $siteAdmin->id,
            'first_name'    => 'Site',
            'last_name'     => 'Admin',
            'email'         => 'admin@admin.com',
            'department_id' => $adminDept->id,
            'role_id'       => $adminRole->id,
            'hire_date'     => now()->toDateString(),
            'status'        => 'active',
        ]);

        Employee::create([
            'user_id'       => $keithRich->id,
            'first_name'    => 'Keith',
            'last_name'     => 'Rich',
            'email'         => 'krmoble@gmail.com',
            'department_id' => $adminDept->id,
            'role_id'       => $adminRole->id,
            'hire_date'     => now()->toDateString(),
            'status'        => 'active',
        ]);
    }
}
