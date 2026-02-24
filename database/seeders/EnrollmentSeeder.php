<?php

namespace Database\Seeders;

use App\Models\CohortCourse;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Database\Seeder;

class EnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $students = Student::all();

        // Enroll students in Alpha cohort's in_progress courses
        $alphaCohortCourses = CohortCourse::where('status', 'in_progress')->get();

        if ($alphaCohortCourses->isEmpty()) {
            return;
        }

        // Track remaining capacity per cohort-course
        $capacity = $alphaCohortCourses->pluck('max_students', 'id')->toArray();

        foreach ($students as $student) {
            // Pick 5 random cohort-courses that still have capacity
            $available = $alphaCohortCourses->filter(fn ($cc) => ($capacity[$cc->id] ?? 0) > 0);

            $selected = $available->random(min(5, $available->count()));

            foreach ($selected as $cohortCourse) {
                Enrollment::create([
                    'student_id'       => $student->id,
                    'cohort_course_id' => $cohortCourse->id,
                    'enrollment_date'  => '2025-08-15',
                    'status'           => 'enrolled',
                ]);

                $capacity[$cohortCourse->id]--;
            }
        }
    }
}
