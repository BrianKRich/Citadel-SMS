<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\ClassCourse;
use App\Models\ClassModel;
use App\Models\Course;
use App\Models\Employee;
use Illuminate\Database\Seeder;

class ClassSeeder extends Seeder
{
    public function run(): void
    {
        $academicYear = AcademicYear::where('name', '2025-2026')->first();

        // Create Class 42 (active)
        $class42 = ClassModel::create([
            'academic_year_id' => $academicYear->id,
            'class_number'     => '42',
            'ngb_number'       => 'NGB-42',
            'status'           => 'active',
            'start_date'       => '2025-09-01',
            'end_date'         => '2026-06-30',
        ]);

        // Course assignments for Class 42
        $assignments = [
            ['course' => 'MATH-101', 'email' => 'm.williams@gyca.edu', 'room' => '101', 'status' => 'in_progress', 'schedule' => [['day' => 'Monday', 'start_time' => '08:00', 'end_time' => '09:00'], ['day' => 'Wednesday', 'start_time' => '08:00', 'end_time' => '09:00'], ['day' => 'Friday', 'start_time' => '08:00', 'end_time' => '09:00']]],
            ['course' => 'ENG-101',  'email' => 'p.johnson@gyca.edu',  'room' => '102', 'status' => 'in_progress', 'schedule' => [['day' => 'Monday', 'start_time' => '09:15', 'end_time' => '10:15'], ['day' => 'Wednesday', 'start_time' => '09:15', 'end_time' => '10:15'], ['day' => 'Friday', 'start_time' => '09:15', 'end_time' => '10:15']]],
            ['course' => 'SCI-101',  'email' => 'd.thompson@gyca.edu', 'room' => '105', 'status' => 'in_progress', 'schedule' => [['day' => 'Tuesday', 'start_time' => '08:00', 'end_time' => '09:30'], ['day' => 'Thursday', 'start_time' => '08:00', 'end_time' => '09:30']]],
            ['course' => 'HIST-101', 'email' => 'a.davis@gyca.edu',    'room' => '103', 'status' => 'in_progress', 'schedule' => [['day' => 'Tuesday', 'start_time' => '10:00', 'end_time' => '11:00'], ['day' => 'Thursday', 'start_time' => '10:00', 'end_time' => '11:00']]],
            ['course' => 'MATH-201', 'email' => 'k.lee@gyca.edu',      'room' => '104', 'status' => 'in_progress', 'schedule' => [['day' => 'Monday', 'start_time' => '10:30', 'end_time' => '11:30'], ['day' => 'Wednesday', 'start_time' => '10:30', 'end_time' => '11:30'], ['day' => 'Friday', 'start_time' => '10:30', 'end_time' => '11:30']]],
            ['course' => 'ENG-102',  'email' => 't.brown@gyca.edu',    'room' => '106', 'status' => 'in_progress', 'schedule' => [['day' => 'Monday', 'start_time' => '13:00', 'end_time' => '14:00'], ['day' => 'Wednesday', 'start_time' => '13:00', 'end_time' => '14:00'], ['day' => 'Friday', 'start_time' => '13:00', 'end_time' => '14:00']]],
            ['course' => 'SCI-202',  'email' => 'l.garcia@gyca.edu',   'room' => '107', 'status' => 'in_progress', 'schedule' => [['day' => 'Tuesday', 'start_time' => '09:45', 'end_time' => '11:15'], ['day' => 'Thursday', 'start_time' => '09:45', 'end_time' => '11:15']]],
            ['course' => 'CS-101',   'email' => 'd.anderson@gyca.edu', 'room' => '110', 'status' => 'in_progress', 'schedule' => [['day' => 'Tuesday', 'start_time' => '14:15', 'end_time' => '15:15'], ['day' => 'Thursday', 'start_time' => '14:15', 'end_time' => '15:15']]],
            ['course' => 'PE-101',   'email' => 'k.clark@gyca.edu',    'room' => 'GYM-A', 'status' => 'in_progress', 'schedule' => [['day' => 'Monday', 'start_time' => '08:00', 'end_time' => '09:00'], ['day' => 'Wednesday', 'start_time' => '08:00', 'end_time' => '09:00']]],
            ['course' => 'LIFE-101', 'email' => 'n.scott@gyca.edu',    'room' => '115', 'status' => 'in_progress', 'schedule' => [['day' => 'Friday', 'start_time' => '09:15', 'end_time' => '11:15']]],
        ];

        foreach ($assignments as $a) {
            $employee = Employee::where('email', $a['email'])->first();
            $course   = Course::where('course_code', $a['course'])->first();

            if (! $employee || ! $course) {
                continue;
            }

            ClassCourse::create([
                'class_id'        => $class42->id,
                'course_id'       => $course->id,
                'instructor_type' => 'staff',
                'employee_id'     => $employee->id,
                'institution_id'  => null,
                'room'            => $a['room'],
                'schedule'        => $a['schedule'],
                'max_students'    => 25,
                'status'          => $a['status'],
            ]);
        }
    }
}
