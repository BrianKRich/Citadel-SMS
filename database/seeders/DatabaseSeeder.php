<?php

namespace Database\Seeders;

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
        // Skip if already seeded
        if (User::where('email', 'admin@admin.com')->exists()) {
            $this->command->info('Database already seeded — skipping.');
            return;
        }

        $siteAdmin = User::factory()->create([
            'name' => 'Site Admin',
            'email' => 'admin@admin.com',
            'password' => 'admin',
            'role' => 'site_admin',
        ]);

        $keithRich = User::firstOrCreate(
            ['email' => 'krmoble@gmail.com'],
            [
                'name' => 'Keith Rich',
                'email_verified_at' => now(),
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'remember_token' => \Illuminate\Support\Str::random(10),
                'role' => 'site_admin',
            ]
        );

        $this->call([
            DepartmentSeeder::class,
        ]);

        // Create employee records for the two default admin users
        $adminDept = Department::where('name', 'Operations')->first();
        $adminRole = EmployeeRole::where('department_id', $adminDept->id)->where('name', 'Site Administrator')->first();

        Employee::firstOrCreate(['user_id' => $siteAdmin->id], [
            'first_name'    => 'Site',
            'last_name'     => 'Admin',
            'email'         => 'admin@admin.com',
            'department_id' => $adminDept->id,
            'role_id'       => $adminRole->id,
            'hire_date'     => now()->toDateString(),
            'status'        => 'active',
        ]);

        Employee::firstOrCreate(['user_id' => $keithRich->id], [
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
