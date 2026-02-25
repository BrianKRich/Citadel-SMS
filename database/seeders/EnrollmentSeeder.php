<?php

namespace Database\Seeders;

use App\Models\ClassCourse;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Database\Seeder;

class EnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $students = Student::all();

        // Enroll students in in_progress class courses
        $activeClassCourses = ClassCourse::where('status', 'in_progress')->get();

        if ($activeClassCourses->isEmpty()) {
            return;
        }

        // Track remaining capacity per class-course
        $capacity = $activeClassCourses->pluck('max_students', 'id')->toArray();

        foreach ($students as $student) {
            // Pick 5 random class-courses that still have capacity
            $available = $activeClassCourses->filter(fn ($cc) => ($capacity[$cc->id] ?? 0) > 0);

            $selected = $available->random(min(5, $available->count()));

            foreach ($selected as $classCourse) {
                Enrollment::create([
                    'student_id'      => $student->id,
                    'class_course_id' => $classCourse->id,
                    'enrollment_date' => '2025-08-15',
                    'status'          => 'enrolled',
                ]);

                $capacity[$classCourse->id]--;
            }
        }
    }
}
