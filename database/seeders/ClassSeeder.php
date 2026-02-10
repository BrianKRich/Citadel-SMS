<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Course;
use App\Models\Employee;
use App\Models\Term;
use Illuminate\Database\Seeder;

class ClassSeeder extends Seeder
{
    public function run(): void
    {
        $academicYear = AcademicYear::where('name', '2025-2026')->first();
        $fallTerm = Term::where('name', 'Fall 2025')->first();
        $springTerm = Term::where('name', 'Spring 2026')->first();

        $math = Course::where('course_code', 'MATH-101')->first();
        $english = Course::where('course_code', 'ENG-101')->first();
        $science = Course::where('course_code', 'SCI-101')->first();
        $history = Course::where('course_code', 'HIST-101')->first();
        $cs = Course::where('course_code', 'CS-101')->first();

        $marcus = Employee::where('email', 'm.williams@gyca.edu')->first();   // Math
        $patricia = Employee::where('email', 'p.johnson@gyca.edu')->first();  // English
        $david = Employee::where('email', 'd.thompson@gyca.edu')->first();    // Science
        $angela = Employee::where('email', 'a.davis@gyca.edu')->first();      // History/Social Studies

        $classes = [
            // --- Fall 2025 ---
            [
                'course_id' => $math->id,
                'employee_id' => $marcus->id,
                'academic_year_id' => $academicYear->id,
                'term_id' => $fallTerm->id,
                'section_name' => 'A',
                'room' => '101',
                'schedule' => [
                    ['day' => 'Monday', 'start_time' => '08:00', 'end_time' => '09:00'],
                    ['day' => 'Wednesday', 'start_time' => '08:00', 'end_time' => '09:00'],
                    ['day' => 'Friday', 'start_time' => '08:00', 'end_time' => '09:00'],
                ],
                'max_students' => 25,
                'status' => 'in_progress',
            ],
            [
                'course_id' => $english->id,
                'employee_id' => $patricia->id,
                'academic_year_id' => $academicYear->id,
                'term_id' => $fallTerm->id,
                'section_name' => 'A',
                'room' => '102',
                'schedule' => [
                    ['day' => 'Monday', 'start_time' => '09:15', 'end_time' => '10:15'],
                    ['day' => 'Wednesday', 'start_time' => '09:15', 'end_time' => '10:15'],
                    ['day' => 'Friday', 'start_time' => '09:15', 'end_time' => '10:15'],
                ],
                'max_students' => 25,
                'status' => 'in_progress',
            ],
            [
                'course_id' => $science->id,
                'employee_id' => $david->id,
                'academic_year_id' => $academicYear->id,
                'term_id' => $fallTerm->id,
                'section_name' => 'A',
                'room' => '105',
                'schedule' => [
                    ['day' => 'Tuesday', 'start_time' => '08:00', 'end_time' => '09:30'],
                    ['day' => 'Thursday', 'start_time' => '08:00', 'end_time' => '09:30'],
                ],
                'max_students' => 20,
                'status' => 'in_progress',
            ],
            [
                'course_id' => $history->id,
                'employee_id' => $angela->id,
                'academic_year_id' => $academicYear->id,
                'term_id' => $fallTerm->id,
                'section_name' => 'A',
                'room' => '103',
                'schedule' => [
                    ['day' => 'Tuesday', 'start_time' => '10:00', 'end_time' => '11:00'],
                    ['day' => 'Thursday', 'start_time' => '10:00', 'end_time' => '11:00'],
                ],
                'max_students' => 25,
                'status' => 'in_progress',
            ],
            [
                'course_id' => $cs->id,
                'employee_id' => $marcus->id,
                'academic_year_id' => $academicYear->id,
                'term_id' => $fallTerm->id,
                'section_name' => 'A',
                'room' => '110',
                'schedule' => [
                    ['day' => 'Tuesday', 'start_time' => '13:00', 'end_time' => '14:00'],
                    ['day' => 'Thursday', 'start_time' => '13:00', 'end_time' => '14:00'],
                ],
                'max_students' => 20,
                'status' => 'in_progress',
            ],

            // --- Spring 2026 ---
            [
                'course_id' => $math->id,
                'employee_id' => $marcus->id,
                'academic_year_id' => $academicYear->id,
                'term_id' => $springTerm->id,
                'section_name' => 'A',
                'room' => '101',
                'schedule' => [
                    ['day' => 'Monday', 'start_time' => '08:00', 'end_time' => '09:00'],
                    ['day' => 'Wednesday', 'start_time' => '08:00', 'end_time' => '09:00'],
                    ['day' => 'Friday', 'start_time' => '08:00', 'end_time' => '09:00'],
                ],
                'max_students' => 25,
                'status' => 'open',
            ],
            [
                'course_id' => $english->id,
                'employee_id' => $patricia->id,
                'academic_year_id' => $academicYear->id,
                'term_id' => $springTerm->id,
                'section_name' => 'A',
                'room' => '102',
                'schedule' => [
                    ['day' => 'Monday', 'start_time' => '09:15', 'end_time' => '10:15'],
                    ['day' => 'Wednesday', 'start_time' => '09:15', 'end_time' => '10:15'],
                    ['day' => 'Friday', 'start_time' => '09:15', 'end_time' => '10:15'],
                ],
                'max_students' => 25,
                'status' => 'open',
            ],
            [
                'course_id' => $science->id,
                'employee_id' => $david->id,
                'academic_year_id' => $academicYear->id,
                'term_id' => $springTerm->id,
                'section_name' => 'A',
                'room' => '105',
                'schedule' => [
                    ['day' => 'Tuesday', 'start_time' => '08:00', 'end_time' => '09:30'],
                    ['day' => 'Thursday', 'start_time' => '08:00', 'end_time' => '09:30'],
                ],
                'max_students' => 20,
                'status' => 'open',
            ],
            [
                'course_id' => $history->id,
                'employee_id' => $angela->id,
                'academic_year_id' => $academicYear->id,
                'term_id' => $springTerm->id,
                'section_name' => 'A',
                'room' => '103',
                'schedule' => [
                    ['day' => 'Tuesday', 'start_time' => '10:00', 'end_time' => '11:00'],
                    ['day' => 'Thursday', 'start_time' => '10:00', 'end_time' => '11:00'],
                ],
                'max_students' => 25,
                'status' => 'open',
            ],
            [
                'course_id' => $cs->id,
                'employee_id' => $marcus->id,
                'academic_year_id' => $academicYear->id,
                'term_id' => $springTerm->id,
                'section_name' => 'A',
                'room' => '110',
                'schedule' => [
                    ['day' => 'Tuesday', 'start_time' => '13:00', 'end_time' => '14:00'],
                    ['day' => 'Thursday', 'start_time' => '13:00', 'end_time' => '14:00'],
                ],
                'max_students' => 20,
                'status' => 'open',
            ],
        ];

        foreach ($classes as $data) {
            ClassModel::create($data);
        }
    }
}
