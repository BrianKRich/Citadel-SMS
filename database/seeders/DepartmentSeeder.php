<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Education'       => ['Teacher', 'Instructor', 'Para-Pro', 'Trainer'],
            'Administration'  => ['Administrator', 'Coordinator', 'Trainer'],
            'Counseling'      => ['Counselor', 'Case Manager', 'Trainer'],
            'Cadre'           => ['Drill Instructor', 'Platoon Sergeant', 'Trainer'],
            'Health Services' => ['Nurse', 'Health Aide', 'Trainer'],
            'Operations'      => ['Director', 'Deputy Director', 'Commandant', 'Administrative Assistant', 'Site Administrator', 'Trainer'],
        ];

        foreach ($data as $departmentName => $roles) {
            $department = Department::firstOrCreate(['name' => $departmentName]);

            foreach ($roles as $roleName) {
                $department->roles()->firstOrCreate(['name' => $roleName]);
            }
        }
    }
}
