<?php

namespace Database\Seeders;

use App\Models\AssessmentCategory;
use Illuminate\Database\Seeder;

class AssessmentCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Homework', 'weight' => 0.15, 'description' => 'Regular homework assignments'],
            ['name' => 'Quizzes', 'weight' => 0.15, 'description' => 'Short quizzes and pop quizzes'],
            ['name' => 'Midterm Exam', 'weight' => 0.25, 'description' => 'Midterm examination'],
            ['name' => 'Final Exam', 'weight' => 0.30, 'description' => 'Final examination'],
            ['name' => 'Projects', 'weight' => 0.15, 'description' => 'Class projects and presentations'],
        ];

        foreach ($categories as $category) {
            AssessmentCategory::create($category);
        }
    }
}
