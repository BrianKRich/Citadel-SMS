<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeRole;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $education = Department::where('name', 'Education')->first();
        $administration = Department::where('name', 'Administration')->first();
        $counseling = Department::where('name', 'Counseling')->first();
        $cadre = Department::where('name', 'Cadre')->first();

        $teacherRole = EmployeeRole::where('department_id', $education->id)->where('name', 'Teacher')->first();
        $administratorRole = EmployeeRole::where('department_id', $administration->id)->where('name', 'Administrator')->first();
        $counselorRole = EmployeeRole::where('department_id', $counseling->id)->where('name', 'Counselor')->first();
        $drillInstructorRole = EmployeeRole::where('department_id', $cadre->id)->where('name', 'Drill Instructor')->first();

        $employees = [
            // 4 Teachers (Education department)
            [
                'first_name' => 'Marcus',
                'last_name' => 'Williams',
                'email' => 'm.williams@gyca.edu',
                'department_id' => $education->id,
                'role_id' => $teacherRole->id,
                'date_of_birth' => '1980-03-14',
                'hire_date' => '2018-08-01',
                'qualifications' => 'M.Ed. Mathematics, Certified Secondary Math Teacher',
                'status' => 'active',
            ],
            [
                'first_name' => 'Patricia',
                'last_name' => 'Johnson',
                'email' => 'p.johnson@gyca.edu',
                'department_id' => $education->id,
                'role_id' => $teacherRole->id,
                'date_of_birth' => '1975-07-22',
                'hire_date' => '2015-08-01',
                'qualifications' => 'B.A. English Literature, M.A. Education, Certified ELA Teacher',
                'status' => 'active',
            ],
            [
                'first_name' => 'David',
                'last_name' => 'Thompson',
                'email' => 'd.thompson@gyca.edu',
                'department_id' => $education->id,
                'role_id' => $teacherRole->id,
                'date_of_birth' => '1983-11-05',
                'hire_date' => '2020-08-01',
                'qualifications' => 'B.S. Biology, M.S. Science Education, Certified Science Teacher',
                'status' => 'active',
            ],
            [
                'first_name' => 'Angela',
                'last_name' => 'Davis',
                'email' => 'a.davis@gyca.edu',
                'department_id' => $education->id,
                'role_id' => $teacherRole->id,
                'date_of_birth' => '1978-05-30',
                'hire_date' => '2012-08-01',
                'qualifications' => 'B.S. History, M.A. Social Studies Education, Certified Social Studies Teacher',
                'status' => 'active',
            ],
            // Additional staff
            [
                'first_name' => 'Robert',
                'last_name' => 'Harris',
                'email' => 'r.harris@gyca.edu',
                'department_id' => $administration->id,
                'role_id' => $administratorRole->id,
                'date_of_birth' => '1970-09-18',
                'hire_date' => '2010-06-15',
                'qualifications' => 'M.S. Educational Administration',
                'status' => 'active',
            ],
            [
                'first_name' => 'Sandra',
                'last_name' => 'Mitchell',
                'email' => 's.mitchell@gyca.edu',
                'department_id' => $counseling->id,
                'role_id' => $counselorRole->id,
                'date_of_birth' => '1982-01-12',
                'hire_date' => '2019-09-01',
                'qualifications' => 'M.S. Counseling Psychology, Licensed Professional Counselor',
                'status' => 'active',
            ],
            [
                'first_name' => 'James',
                'last_name' => 'Washington',
                'email' => 'j.washington@gyca.edu',
                'department_id' => $cadre->id,
                'role_id' => $drillInstructorRole->id,
                'date_of_birth' => '1976-04-25',
                'hire_date' => '2016-01-10',
                'qualifications' => 'U.S. Army Retired, Staff Sergeant (E-6), 20 years service',
                'status' => 'active',
            ],
        ];

        foreach ($employees as $data) {
            Employee::create($data);
        }
    }
}
