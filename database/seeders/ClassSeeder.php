<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Cohort;
use App\Models\CohortCourse;
use App\Models\Course;
use App\Models\EducationalInstitution;
use App\Models\Employee;
use Illuminate\Database\Seeder;

class ClassSeeder extends Seeder
{
    public function run(): void
    {
        $academicYear = AcademicYear::where('name', '2025-2026')->first();

        // Create Class 42 (active) — cohorts auto-created by ClassModel::boot()
        $class42 = ClassModel::create([
            'academic_year_id' => $academicYear->id,
            'class_number'     => '42',
            'ngb_number'       => 'NGB-42',
            'status'           => 'active',
        ]);

        // Retrieve the auto-created cohorts
        $alpha = Cohort::where('class_id', $class42->id)->where('name', 'alpha')->first();
        $bravo = Cohort::where('class_id', $class42->id)->where('name', 'bravo')->first();

        // Set cohort dates
        $alpha->update(['start_date' => '2025-09-01', 'end_date' => '2026-02-15']);
        $bravo->update(['start_date' => '2026-02-16', 'end_date' => '2026-06-30']);

        // Instructor assignments for Alpha cohort: [course_code, email, room, schedule]
        $alphaAssignments = [
            ['course' => 'MATH-101', 'email' => 'm.williams@gyca.edu', 'room' => '101', 'schedule' => [['day' => 'Monday', 'start_time' => '08:00', 'end_time' => '09:00'], ['day' => 'Wednesday', 'start_time' => '08:00', 'end_time' => '09:00'], ['day' => 'Friday', 'start_time' => '08:00', 'end_time' => '09:00']]],
            ['course' => 'ENG-101',  'email' => 'p.johnson@gyca.edu',  'room' => '102', 'schedule' => [['day' => 'Monday', 'start_time' => '09:15', 'end_time' => '10:15'], ['day' => 'Wednesday', 'start_time' => '09:15', 'end_time' => '10:15'], ['day' => 'Friday', 'start_time' => '09:15', 'end_time' => '10:15']]],
            ['course' => 'SCI-101',  'email' => 'd.thompson@gyca.edu', 'room' => '105', 'schedule' => [['day' => 'Tuesday', 'start_time' => '08:00', 'end_time' => '09:30'], ['day' => 'Thursday', 'start_time' => '08:00', 'end_time' => '09:30']]],
            ['course' => 'HIST-101', 'email' => 'a.davis@gyca.edu',    'room' => '103', 'schedule' => [['day' => 'Tuesday', 'start_time' => '10:00', 'end_time' => '11:00'], ['day' => 'Thursday', 'start_time' => '10:00', 'end_time' => '11:00']]],
            ['course' => 'MATH-201', 'email' => 'k.lee@gyca.edu',      'room' => '104', 'schedule' => [['day' => 'Monday', 'start_time' => '10:30', 'end_time' => '11:30'], ['day' => 'Wednesday', 'start_time' => '10:30', 'end_time' => '11:30'], ['day' => 'Friday', 'start_time' => '10:30', 'end_time' => '11:30']]],
            ['course' => 'ENG-102',  'email' => 't.brown@gyca.edu',    'room' => '106', 'schedule' => [['day' => 'Monday', 'start_time' => '13:00', 'end_time' => '14:00'], ['day' => 'Wednesday', 'start_time' => '13:00', 'end_time' => '14:00'], ['day' => 'Friday', 'start_time' => '13:00', 'end_time' => '14:00']]],
            ['course' => 'SCI-202',  'email' => 'l.garcia@gyca.edu',   'room' => '107', 'schedule' => [['day' => 'Tuesday', 'start_time' => '09:45', 'end_time' => '11:15'], ['day' => 'Thursday', 'start_time' => '09:45', 'end_time' => '11:15']]],
            ['course' => 'CS-101',   'email' => 'd.anderson@gyca.edu', 'room' => '110', 'schedule' => [['day' => 'Tuesday', 'start_time' => '14:15', 'end_time' => '15:15'], ['day' => 'Thursday', 'start_time' => '14:15', 'end_time' => '15:15']]],
            ['course' => 'PE-101',   'email' => 'k.clark@gyca.edu',    'room' => 'GYM-A', 'schedule' => [['day' => 'Monday', 'start_time' => '08:00', 'end_time' => '09:00'], ['day' => 'Wednesday', 'start_time' => '08:00', 'end_time' => '09:00']]],
            ['course' => 'LIFE-101', 'email' => 'n.scott@gyca.edu',    'room' => '115', 'schedule' => [['day' => 'Friday', 'start_time' => '09:15', 'end_time' => '11:15']]],
        ];

        // Instructor assignments for Bravo cohort (mix of staff + one institution)
        $bravoAssignments = [
            ['course' => 'MATH-101', 'email' => 'm.williams@gyca.edu', 'room' => '101', 'schedule' => [['day' => 'Monday', 'start_time' => '08:00', 'end_time' => '09:00'], ['day' => 'Wednesday', 'start_time' => '08:00', 'end_time' => '09:00'], ['day' => 'Friday', 'start_time' => '08:00', 'end_time' => '09:00']]],
            ['course' => 'ENG-101',  'email' => 'p.johnson@gyca.edu',  'room' => '102', 'schedule' => [['day' => 'Monday', 'start_time' => '09:15', 'end_time' => '10:15'], ['day' => 'Wednesday', 'start_time' => '09:15', 'end_time' => '10:15'], ['day' => 'Friday', 'start_time' => '09:15', 'end_time' => '10:15']]],
            ['course' => 'SCI-101',  'email' => 'd.thompson@gyca.edu', 'room' => '105', 'schedule' => [['day' => 'Tuesday', 'start_time' => '08:00', 'end_time' => '09:30'], ['day' => 'Thursday', 'start_time' => '08:00', 'end_time' => '09:30']]],
            ['course' => 'HIST-101', 'email' => 'a.davis@gyca.edu',    'room' => '103', 'schedule' => [['day' => 'Tuesday', 'start_time' => '10:00', 'end_time' => '11:00'], ['day' => 'Thursday', 'start_time' => '10:00', 'end_time' => '11:00']]],
            ['course' => 'MATH-201', 'email' => 'k.lee@gyca.edu',      'room' => '104', 'schedule' => [['day' => 'Monday', 'start_time' => '10:30', 'end_time' => '11:30'], ['day' => 'Wednesday', 'start_time' => '10:30', 'end_time' => '11:30'], ['day' => 'Friday', 'start_time' => '10:30', 'end_time' => '11:30']]],
            ['course' => 'ENG-102',  'email' => 't.brown@gyca.edu',    'room' => '106', 'schedule' => [['day' => 'Monday', 'start_time' => '13:00', 'end_time' => '14:00'], ['day' => 'Wednesday', 'start_time' => '13:00', 'end_time' => '14:00'], ['day' => 'Friday', 'start_time' => '13:00', 'end_time' => '14:00']]],
            ['course' => 'SCI-202',  'email' => 'l.garcia@gyca.edu',   'room' => '107', 'schedule' => [['day' => 'Tuesday', 'start_time' => '09:45', 'end_time' => '11:15'], ['day' => 'Thursday', 'start_time' => '09:45', 'end_time' => '11:15']]],
            ['course' => 'CS-101',   'email' => 'd.anderson@gyca.edu', 'room' => '110', 'schedule' => [['day' => 'Tuesday', 'start_time' => '14:15', 'end_time' => '15:15'], ['day' => 'Thursday', 'start_time' => '14:15', 'end_time' => '15:15']]],
            ['course' => 'PE-101',   'email' => 'k.clark@gyca.edu',    'room' => 'GYM-A', 'schedule' => [['day' => 'Monday', 'start_time' => '08:00', 'end_time' => '09:00'], ['day' => 'Wednesday', 'start_time' => '08:00', 'end_time' => '09:00']]],
            ['course' => 'LIFE-101', 'email' => 'n.scott@gyca.edu',    'room' => '115', 'schedule' => [['day' => 'Friday', 'start_time' => '09:15', 'end_time' => '11:15']]],
        ];

        $this->createCohortCourses($alpha, $alphaAssignments, 'in_progress');
        $this->createCohortCourses($bravo, $bravoAssignments, 'open');
    }

    private function createCohortCourses(Cohort $cohort, array $assignments, string $status): void
    {
        foreach ($assignments as $a) {
            $employee = Employee::where('email', $a['email'])->first();
            $course   = Course::where('course_code', $a['course'])->first();

            if (! $employee || ! $course) {
                continue;
            }

            CohortCourse::create([
                'cohort_id'       => $cohort->id,
                'course_id'       => $course->id,
                'instructor_type' => 'staff',
                'employee_id'     => $employee->id,
                'institution_id'  => null,
                'room'            => $a['room'],
                'schedule'        => $a['schedule'],
                'max_students'    => 25,
                'status'          => $status,
            ]);
        }
    }
}
