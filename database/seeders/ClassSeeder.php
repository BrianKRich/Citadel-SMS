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

        // Map each teacher (by email) to their course, room, and schedule
        $assignments = [
            ['email' => 'm.williams@gyca.edu', 'course' => 'MATH-101', 'room' => '101', 'schedule' => [['day' => 'Monday', 'start_time' => '08:00', 'end_time' => '09:00'], ['day' => 'Wednesday', 'start_time' => '08:00', 'end_time' => '09:00'], ['day' => 'Friday', 'start_time' => '08:00', 'end_time' => '09:00']], 'max' => 25],
            ['email' => 'p.johnson@gyca.edu', 'course' => 'ENG-101', 'room' => '102', 'schedule' => [['day' => 'Monday', 'start_time' => '09:15', 'end_time' => '10:15'], ['day' => 'Wednesday', 'start_time' => '09:15', 'end_time' => '10:15'], ['day' => 'Friday', 'start_time' => '09:15', 'end_time' => '10:15']], 'max' => 25],
            ['email' => 'd.thompson@gyca.edu', 'course' => 'SCI-101', 'room' => '105', 'schedule' => [['day' => 'Tuesday', 'start_time' => '08:00', 'end_time' => '09:30'], ['day' => 'Thursday', 'start_time' => '08:00', 'end_time' => '09:30']], 'max' => 25],
            ['email' => 'a.davis@gyca.edu', 'course' => 'HIST-101', 'room' => '103', 'schedule' => [['day' => 'Tuesday', 'start_time' => '10:00', 'end_time' => '11:00'], ['day' => 'Thursday', 'start_time' => '10:00', 'end_time' => '11:00']], 'max' => 25],
            ['email' => 'k.lee@gyca.edu', 'course' => 'MATH-201', 'room' => '104', 'schedule' => [['day' => 'Monday', 'start_time' => '10:30', 'end_time' => '11:30'], ['day' => 'Wednesday', 'start_time' => '10:30', 'end_time' => '11:30'], ['day' => 'Friday', 'start_time' => '10:30', 'end_time' => '11:30']], 'max' => 25],
            ['email' => 't.brown@gyca.edu', 'course' => 'ENG-102', 'room' => '106', 'schedule' => [['day' => 'Monday', 'start_time' => '13:00', 'end_time' => '14:00'], ['day' => 'Wednesday', 'start_time' => '13:00', 'end_time' => '14:00'], ['day' => 'Friday', 'start_time' => '13:00', 'end_time' => '14:00']], 'max' => 25],
            ['email' => 'l.garcia@gyca.edu', 'course' => 'SCI-202', 'room' => '107', 'schedule' => [['day' => 'Tuesday', 'start_time' => '09:45', 'end_time' => '11:15'], ['day' => 'Thursday', 'start_time' => '09:45', 'end_time' => '11:15']], 'max' => 25],
            ['email' => 'c.martinez@gyca.edu', 'course' => 'SCI-201', 'room' => '108', 'schedule' => [['day' => 'Monday', 'start_time' => '14:15', 'end_time' => '15:45'], ['day' => 'Wednesday', 'start_time' => '14:15', 'end_time' => '15:45']], 'max' => 25],
            ['email' => 'j.taylor@gyca.edu', 'course' => 'HIST-201', 'room' => '109', 'schedule' => [['day' => 'Tuesday', 'start_time' => '13:00', 'end_time' => '14:00'], ['day' => 'Thursday', 'start_time' => '13:00', 'end_time' => '14:00']], 'max' => 25],
            ['email' => 'd.anderson@gyca.edu', 'course' => 'CS-101', 'room' => '110', 'schedule' => [['day' => 'Tuesday', 'start_time' => '14:15', 'end_time' => '15:15'], ['day' => 'Thursday', 'start_time' => '14:15', 'end_time' => '15:15']], 'max' => 25],
            ['email' => 'm.robinson@gyca.edu', 'course' => 'CS-201', 'room' => '111', 'schedule' => [['day' => 'Monday', 'start_time' => '11:45', 'end_time' => '12:45'], ['day' => 'Wednesday', 'start_time' => '11:45', 'end_time' => '12:45']], 'max' => 25],
            ['email' => 'k.clark@gyca.edu', 'course' => 'PE-101', 'room' => 'GYM-A', 'schedule' => [['day' => 'Monday', 'start_time' => '08:00', 'end_time' => '09:00'], ['day' => 'Wednesday', 'start_time' => '08:00', 'end_time' => '09:00']], 'max' => 30],
            ['email' => 'l.walker@gyca.edu', 'course' => 'PE-102', 'room' => 'GYM-B', 'schedule' => [['day' => 'Tuesday', 'start_time' => '08:00', 'end_time' => '09:00'], ['day' => 'Thursday', 'start_time' => '08:00', 'end_time' => '09:00']], 'max' => 30],
            ['email' => 'b.hall@gyca.edu', 'course' => 'HLT-101', 'room' => '112', 'schedule' => [['day' => 'Monday', 'start_time' => '14:15', 'end_time' => '15:15'], ['day' => 'Wednesday', 'start_time' => '14:15', 'end_time' => '15:15']], 'max' => 30],
            ['email' => 's.young@gyca.edu', 'course' => 'ART-101', 'room' => '201', 'schedule' => [['day' => 'Tuesday', 'start_time' => '11:30', 'end_time' => '12:30'], ['day' => 'Thursday', 'start_time' => '11:30', 'end_time' => '12:30']], 'max' => 25],
            ['email' => 'r.king@gyca.edu', 'course' => 'ART-201', 'room' => '202', 'schedule' => [['day' => 'Monday', 'start_time' => '09:15', 'end_time' => '10:15'], ['day' => 'Wednesday', 'start_time' => '09:15', 'end_time' => '10:15']], 'max' => 25],
            ['email' => 'a.wright@gyca.edu', 'course' => 'MUS-101', 'room' => '203', 'schedule' => [['day' => 'Tuesday', 'start_time' => '14:15', 'end_time' => '15:15'], ['day' => 'Thursday', 'start_time' => '14:15', 'end_time' => '15:15']], 'max' => 30],
            ['email' => 'm.hernandez@gyca.edu', 'course' => 'SPAN-101', 'room' => '113', 'schedule' => [['day' => 'Monday', 'start_time' => '10:30', 'end_time' => '11:30'], ['day' => 'Wednesday', 'start_time' => '10:30', 'end_time' => '11:30'], ['day' => 'Friday', 'start_time' => '10:30', 'end_time' => '11:30']], 'max' => 25],
            ['email' => 'c.lopez@gyca.edu', 'course' => 'SPAN-201', 'room' => '114', 'schedule' => [['day' => 'Tuesday', 'start_time' => '10:00', 'end_time' => '11:00'], ['day' => 'Thursday', 'start_time' => '10:00', 'end_time' => '11:00']], 'max' => 25],
            ['email' => 'n.scott@gyca.edu', 'course' => 'LIFE-101', 'room' => '115', 'schedule' => [['day' => 'Friday', 'start_time' => '09:15', 'end_time' => '11:15']], 'max' => 30],
        ];

        foreach ($assignments as $a) {
            $teacher = Employee::where('email', $a['email'])->first();
            $course = Course::where('course_code', $a['course'])->first();

            // Fall — in_progress
            ClassModel::create([
                'course_id' => $course->id,
                'employee_id' => $teacher->id,
                'academic_year_id' => $academicYear->id,
                'term_id' => $fallTerm->id,
                'section_name' => 'A',
                'room' => $a['room'],
                'schedule' => $a['schedule'],
                'max_students' => $a['max'],
                'status' => 'in_progress',
            ]);

            // Spring — open
            ClassModel::create([
                'course_id' => $course->id,
                'employee_id' => $teacher->id,
                'academic_year_id' => $academicYear->id,
                'term_id' => $springTerm->id,
                'section_name' => 'A',
                'room' => $a['room'],
                'schedule' => $a['schedule'],
                'max_students' => $a['max'],
                'status' => 'open',
            ]);
        }
    }
}
