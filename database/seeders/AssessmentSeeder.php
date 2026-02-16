<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\AssessmentCategory;
use App\Models\ClassModel;
use Illuminate\Database\Seeder;

class AssessmentSeeder extends Seeder
{
    public function run(): void
    {
        $homework = AssessmentCategory::where('name', 'Homework')->first();
        $quizzes = AssessmentCategory::where('name', 'Quizzes')->first();
        $midterm = AssessmentCategory::where('name', 'Midterm Exam')->first();
        $final = AssessmentCategory::where('name', 'Final Exam')->first();
        $projects = AssessmentCategory::where('name', 'Projects')->first();

        // Create assessments for each Fall 2025 class (in_progress status)
        $fallClasses = ClassModel::where('status', 'in_progress')->get();

        foreach ($fallClasses as $class) {
            Assessment::create([
                'class_id' => $class->id,
                'assessment_category_id' => $homework->id,
                'name' => 'Homework 1',
                'max_score' => 100,
                'due_date' => '2025-09-15',
                'status' => 'published',
            ]);

            Assessment::create([
                'class_id' => $class->id,
                'assessment_category_id' => $homework->id,
                'name' => 'Homework 2',
                'max_score' => 100,
                'due_date' => '2025-10-01',
                'status' => 'published',
            ]);

            Assessment::create([
                'class_id' => $class->id,
                'assessment_category_id' => $quizzes->id,
                'name' => 'Quiz 1',
                'max_score' => 50,
                'due_date' => '2025-09-20',
                'status' => 'published',
            ]);

            Assessment::create([
                'class_id' => $class->id,
                'assessment_category_id' => $midterm->id,
                'name' => 'Midterm Exam',
                'max_score' => 100,
                'due_date' => '2025-10-15',
                'status' => 'published',
            ]);

            Assessment::create([
                'class_id' => $class->id,
                'assessment_category_id' => $projects->id,
                'name' => 'Research Project',
                'max_score' => 100,
                'due_date' => '2025-11-01',
                'status' => 'published',
            ]);

            Assessment::create([
                'class_id' => $class->id,
                'assessment_category_id' => $final->id,
                'name' => 'Final Exam',
                'max_score' => 100,
                'due_date' => '2025-12-10',
                'status' => 'draft',
            ]);
        }
    }
}
