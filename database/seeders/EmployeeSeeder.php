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

        $teachers = [
            // Original 4 teachers
            ['first_name' => 'Marcus', 'last_name' => 'Williams', 'email' => 'm.williams@gyca.edu', 'date_of_birth' => '1980-03-14', 'hire_date' => '2018-08-01', 'qualifications' => 'M.Ed. Mathematics, Certified Secondary Math Teacher'],
            ['first_name' => 'Patricia', 'last_name' => 'Johnson', 'email' => 'p.johnson@gyca.edu', 'date_of_birth' => '1975-07-22', 'hire_date' => '2015-08-01', 'qualifications' => 'B.A. English Literature, M.A. Education, Certified ELA Teacher'],
            ['first_name' => 'David', 'last_name' => 'Thompson', 'email' => 'd.thompson@gyca.edu', 'date_of_birth' => '1983-11-05', 'hire_date' => '2020-08-01', 'qualifications' => 'B.S. Biology, M.S. Science Education, Certified Science Teacher'],
            ['first_name' => 'Angela', 'last_name' => 'Davis', 'email' => 'a.davis@gyca.edu', 'date_of_birth' => '1978-05-30', 'hire_date' => '2012-08-01', 'qualifications' => 'B.S. History, M.A. Social Studies Education, Certified Social Studies Teacher'],
            // 16 new teachers
            ['first_name' => 'Karen', 'last_name' => 'Lee', 'email' => 'k.lee@gyca.edu', 'date_of_birth' => '1985-02-18', 'hire_date' => '2019-08-01', 'qualifications' => 'M.S. Mathematics, Certified Secondary Math Teacher'],
            ['first_name' => 'Thomas', 'last_name' => 'Brown', 'email' => 't.brown@gyca.edu', 'date_of_birth' => '1979-09-10', 'hire_date' => '2017-08-01', 'qualifications' => 'M.A. English, Certified ELA Teacher'],
            ['first_name' => 'Linda', 'last_name' => 'Garcia', 'email' => 'l.garcia@gyca.edu', 'date_of_birth' => '1982-06-25', 'hire_date' => '2021-08-01', 'qualifications' => 'M.S. Chemistry, Certified Science Teacher'],
            ['first_name' => 'Charles', 'last_name' => 'Martinez', 'email' => 'c.martinez@gyca.edu', 'date_of_birth' => '1977-12-03', 'hire_date' => '2014-08-01', 'qualifications' => 'Ph.D. Biology, Certified Science Teacher'],
            ['first_name' => 'Jennifer', 'last_name' => 'Taylor', 'email' => 'j.taylor@gyca.edu', 'date_of_birth' => '1986-04-15', 'hire_date' => '2022-08-01', 'qualifications' => 'M.A. American History, Certified Social Studies Teacher'],
            ['first_name' => 'Daniel', 'last_name' => 'Anderson', 'email' => 'd.anderson@gyca.edu', 'date_of_birth' => '1984-08-20', 'hire_date' => '2020-08-01', 'qualifications' => 'M.S. Computer Science, Certified Technology Teacher'],
            ['first_name' => 'Michelle', 'last_name' => 'Robinson', 'email' => 'm.robinson@gyca.edu', 'date_of_birth' => '1988-01-30', 'hire_date' => '2023-08-01', 'qualifications' => 'B.S. Computer Science, M.Ed. Instructional Technology'],
            ['first_name' => 'Kevin', 'last_name' => 'Clark', 'email' => 'k.clark@gyca.edu', 'date_of_birth' => '1981-07-12', 'hire_date' => '2016-08-01', 'qualifications' => 'M.S. Kinesiology, Certified Physical Education Teacher'],
            ['first_name' => 'Lisa', 'last_name' => 'Walker', 'email' => 'l.walker@gyca.edu', 'date_of_birth' => '1983-10-08', 'hire_date' => '2018-08-01', 'qualifications' => 'M.S. Exercise Science, Certified Physical Education Teacher'],
            ['first_name' => 'Brian', 'last_name' => 'Hall', 'email' => 'b.hall@gyca.edu', 'date_of_birth' => '1980-05-22', 'hire_date' => '2017-08-01', 'qualifications' => 'M.S. Public Health, Certified Health Education Teacher'],
            ['first_name' => 'Stephanie', 'last_name' => 'Young', 'email' => 's.young@gyca.edu', 'date_of_birth' => '1987-03-17', 'hire_date' => '2021-08-01', 'qualifications' => 'M.F.A. Studio Art, Certified Art Teacher'],
            ['first_name' => 'Richard', 'last_name' => 'King', 'email' => 'r.king@gyca.edu', 'date_of_birth' => '1976-11-28', 'hire_date' => '2013-08-01', 'qualifications' => 'M.F.A. Digital Media, Certified Art Teacher'],
            ['first_name' => 'Amanda', 'last_name' => 'Wright', 'email' => 'a.wright@gyca.edu', 'date_of_birth' => '1985-09-05', 'hire_date' => '2019-08-01', 'qualifications' => 'M.M. Music Education, Certified Music Teacher'],
            ['first_name' => 'Maria', 'last_name' => 'Hernandez', 'email' => 'm.hernandez@gyca.edu', 'date_of_birth' => '1982-04-14', 'hire_date' => '2016-08-01', 'qualifications' => 'M.A. Spanish, Native Speaker, Certified Foreign Language Teacher'],
            ['first_name' => 'Christopher', 'last_name' => 'Lopez', 'email' => 'c.lopez@gyca.edu', 'date_of_birth' => '1984-06-20', 'hire_date' => '2020-08-01', 'qualifications' => 'M.A. Spanish Literature, Certified Foreign Language Teacher'],
            ['first_name' => 'Nicole', 'last_name' => 'Scott', 'email' => 'n.scott@gyca.edu', 'date_of_birth' => '1986-08-11', 'hire_date' => '2022-08-01', 'qualifications' => 'M.S. Financial Planning, Certified Family & Consumer Sciences Teacher'],
        ];

        foreach ($teachers as $data) {
            Employee::create(array_merge($data, [
                'department_id' => $education->id,
                'role_id' => $teacherRole->id,
                'status' => 'active',
            ]));
        }

        // Non-teaching staff
        $staff = [
            [
                'first_name' => 'Robert', 'last_name' => 'Harris', 'email' => 'r.harris@gyca.edu',
                'department_id' => $administration->id, 'role_id' => $administratorRole->id,
                'date_of_birth' => '1970-09-18', 'hire_date' => '2010-06-15',
                'qualifications' => 'M.S. Educational Administration', 'status' => 'active',
            ],
            [
                'first_name' => 'Sandra', 'last_name' => 'Mitchell', 'email' => 's.mitchell@gyca.edu',
                'department_id' => $counseling->id, 'role_id' => $counselorRole->id,
                'date_of_birth' => '1982-01-12', 'hire_date' => '2019-09-01',
                'qualifications' => 'M.S. Counseling Psychology, Licensed Professional Counselor', 'status' => 'active',
            ],
            [
                'first_name' => 'James', 'last_name' => 'Washington', 'email' => 'j.washington@gyca.edu',
                'department_id' => $cadre->id, 'role_id' => $drillInstructorRole->id,
                'date_of_birth' => '1976-04-25', 'hire_date' => '2016-01-10',
                'qualifications' => 'U.S. Army Retired, Staff Sergeant (E-6), 20 years service', 'status' => 'active',
            ],
        ];

        foreach ($staff as $data) {
            Employee::create($data);
        }
    }
}
