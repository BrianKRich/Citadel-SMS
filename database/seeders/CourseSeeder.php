<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $courses = [
            [
                'course_code' => 'MATH-101',
                'name' => 'Algebra I',
                'description' => 'Introduction to algebraic concepts and problem solving',
                'credits' => 3.00,
                'department' => 'Mathematics',
                'level' => 'Beginner',
                'is_active' => true,
            ],
            [
                'course_code' => 'ENG-101',
                'name' => 'English Literature',
                'description' => 'Study of classic and modern literature',
                'credits' => 3.00,
                'department' => 'English',
                'level' => 'Beginner',
                'is_active' => true,
            ],
            [
                'course_code' => 'SCI-101',
                'name' => 'General Science',
                'description' => 'Introduction to scientific principles and methods',
                'credits' => 4.00,
                'department' => 'Science',
                'level' => 'Beginner',
                'is_active' => true,
            ],
            [
                'course_code' => 'HIST-101',
                'name' => 'World History',
                'description' => 'Survey of world civilizations and historical events',
                'credits' => 3.00,
                'department' => 'History',
                'level' => 'Beginner',
                'is_active' => true,
            ],
            [
                'course_code' => 'CS-101',
                'name' => 'Introduction to Programming',
                'description' => 'Fundamentals of computer programming',
                'credits' => 3.00,
                'department' => 'Computer Science',
                'level' => 'Beginner',
                'is_active' => true,
            ],
        ];

        foreach ($courses as $course) {
            Course::create($course);
        }
    }
}
