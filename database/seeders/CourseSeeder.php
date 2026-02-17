<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $courses = [
            // Mathematics
            ['course_code' => 'MATH-101', 'name' => 'Algebra I', 'description' => 'Introduction to algebraic concepts and problem solving', 'credits' => 3.00, 'department' => 'Mathematics', 'level' => 'Beginner'],
            ['course_code' => 'MATH-201', 'name' => 'Geometry', 'description' => 'Study of shapes, angles, and spatial reasoning', 'credits' => 3.00, 'department' => 'Mathematics', 'level' => 'Intermediate'],
            // English
            ['course_code' => 'ENG-101', 'name' => 'English Literature', 'description' => 'Study of classic and modern literature', 'credits' => 3.00, 'department' => 'English', 'level' => 'Beginner'],
            ['course_code' => 'ENG-102', 'name' => 'English Composition', 'description' => 'Fundamentals of essay writing and rhetoric', 'credits' => 3.00, 'department' => 'English', 'level' => 'Beginner'],
            // Science
            ['course_code' => 'SCI-101', 'name' => 'General Science', 'description' => 'Introduction to scientific principles and methods', 'credits' => 4.00, 'department' => 'Science', 'level' => 'Beginner'],
            ['course_code' => 'SCI-201', 'name' => 'Biology', 'description' => 'Study of living organisms and biological systems', 'credits' => 4.00, 'department' => 'Science', 'level' => 'Intermediate'],
            ['course_code' => 'SCI-202', 'name' => 'Chemistry', 'description' => 'Introduction to chemical reactions and matter', 'credits' => 4.00, 'department' => 'Science', 'level' => 'Intermediate'],
            // History
            ['course_code' => 'HIST-101', 'name' => 'World History', 'description' => 'Survey of world civilizations and historical events', 'credits' => 3.00, 'department' => 'History', 'level' => 'Beginner'],
            ['course_code' => 'HIST-201', 'name' => 'U.S. History', 'description' => 'American history from colonial era to present', 'credits' => 3.00, 'department' => 'History', 'level' => 'Intermediate'],
            // Computer Science
            ['course_code' => 'CS-101', 'name' => 'Introduction to Programming', 'description' => 'Fundamentals of computer programming', 'credits' => 3.00, 'department' => 'Computer Science', 'level' => 'Beginner'],
            ['course_code' => 'CS-201', 'name' => 'Web Development', 'description' => 'Building websites with HTML, CSS, and JavaScript', 'credits' => 3.00, 'department' => 'Computer Science', 'level' => 'Intermediate'],
            // Physical Education
            ['course_code' => 'PE-101', 'name' => 'Physical Fitness', 'description' => 'Physical conditioning and fitness fundamentals', 'credits' => 2.00, 'department' => 'Physical Education', 'level' => 'Beginner'],
            ['course_code' => 'PE-102', 'name' => 'Team Sports', 'description' => 'Fundamentals of basketball, volleyball, and soccer', 'credits' => 2.00, 'department' => 'Physical Education', 'level' => 'Beginner'],
            // Health
            ['course_code' => 'HLT-101', 'name' => 'Health & Wellness', 'description' => 'Nutrition, mental health, and healthy lifestyle choices', 'credits' => 2.00, 'department' => 'Health', 'level' => 'Beginner'],
            // Art
            ['course_code' => 'ART-101', 'name' => 'Visual Arts', 'description' => 'Drawing, painting, and visual expression', 'credits' => 2.00, 'department' => 'Art', 'level' => 'Beginner'],
            ['course_code' => 'ART-201', 'name' => 'Digital Media', 'description' => 'Photography, graphic design, and digital art', 'credits' => 3.00, 'department' => 'Art', 'level' => 'Intermediate'],
            // Music
            ['course_code' => 'MUS-101', 'name' => 'Music Fundamentals', 'description' => 'Music theory, rhythm, and appreciation', 'credits' => 2.00, 'department' => 'Music', 'level' => 'Beginner'],
            // Foreign Language
            ['course_code' => 'SPAN-101', 'name' => 'Spanish I', 'description' => 'Introduction to Spanish language and culture', 'credits' => 3.00, 'department' => 'Foreign Language', 'level' => 'Beginner'],
            ['course_code' => 'SPAN-201', 'name' => 'Spanish II', 'description' => 'Intermediate Spanish conversation and grammar', 'credits' => 3.00, 'department' => 'Foreign Language', 'level' => 'Intermediate'],
            // Life Skills
            ['course_code' => 'LIFE-101', 'name' => 'Financial Literacy', 'description' => 'Budgeting, banking, and personal finance', 'credits' => 2.00, 'department' => 'Life Skills', 'level' => 'Beginner'],
        ];

        foreach ($courses as $course) {
            Course::create(array_merge($course, ['is_active' => true]));
        }
    }
}
