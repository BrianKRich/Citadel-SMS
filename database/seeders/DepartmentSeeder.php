<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $department = Department::firstOrCreate(['name' => 'Operations']);
        $department->roles()->firstOrCreate(['name' => 'Site Administrator']);
    }
}
