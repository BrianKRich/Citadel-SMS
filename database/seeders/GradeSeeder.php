<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\User;
use App\Services\GradeCalculationService;
use Illuminate\Database\Seeder;

class GradeSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::first();
        $publishedAssessments = Assessment::published()->get();

        foreach ($publishedAssessments as $assessment) {
            $enrollments = Enrollment::where('class_course_id', $assessment->class_course_id)
                ->enrolled()
                ->get();

            foreach ($enrollments as $enrollment) {
                $score  = fake()->randomFloat(2, 45, (float) $assessment->max_score);
                $isLate = fake()->boolean(15);

                Grade::create([
                    'enrollment_id' => $enrollment->id,
                    'assessment_id' => $assessment->id,
                    'score'         => $score,
                    'is_late'       => $isLate,
                    'late_penalty'  => $isLate ? 10.00 : null,
                    'graded_by'     => $admin?->id,
                    'graded_at'     => now(),
                ]);
            }
        }

        // Recalculate all enrollment grades
        $service     = app(GradeCalculationService::class);
        $enrollments = Enrollment::enrolled()->get();

        foreach ($enrollments as $enrollment) {
            $service->updateEnrollmentGrade($enrollment);
        }
    }
}
