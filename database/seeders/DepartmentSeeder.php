<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Education' => ['Teacher', 'Instructor'],
            'Administration' => ['Administrator', 'Coordinator'],
            'Counseling' => ['Counselor', 'Case Manager'],
            'Cadre' => ['Drill Instructor', 'Platoon Sergeant'],
            'Health Services' => ['Nurse', 'Health Aide'],
        ];

        foreach ($data as $departmentName => $roles) {
            $department = Department::create(['name' => $departmentName]);

            foreach ($roles as $roleName) {
                $department->roles()->create(['name' => $roleName]);
            }
        }
    }
}
