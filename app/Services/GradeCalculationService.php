<?php

namespace App\Services;

use App\Models\ClassCourse;
use App\Models\Enrollment;
use App\Models\GradingScale;
use App\Models\Student;
use Illuminate\Support\Collection;

class GradeCalculationService
{
    public function calculateWeightedAverage(Enrollment $enrollment): float
    {
        $enrollment->load(['grades.assessment.category']);

        $grades = $enrollment->grades;

        if ($grades->isEmpty()) {
            return 0.0;
        }

        // Group grades by assessment category
        $byCategory = $grades->groupBy(fn ($grade) => $grade->assessment->assessment_category_id);

        $weightedSum = 0.0;
        $totalWeight = 0.0;
        $extraCreditSum = 0.0;

        foreach ($byCategory as $categoryId => $categoryGrades) {
            $category = $categoryGrades->first()->assessment->category;
            $categoryWeight = (float) $category->weight;

            $earnedPoints = 0.0;
            $maxPoints = 0.0;

            foreach ($categoryGrades as $grade) {
                $assessment = $grade->assessment;

                if ($assessment->is_extra_credit) {
                    $maxScore = (float) $assessment->max_score;
                    if ($maxScore > 0) {
                        $extraCreditSum += ($grade->adjusted_score / $maxScore) * (float) ($assessment->weight ?? $categoryWeight) * 100;
                    }
                    continue;
                }

                $earnedPoints += $grade->adjusted_score;
                $maxPoints += (float) $assessment->max_score;
            }

            if ($maxPoints > 0) {
                $categoryPercentage = ($earnedPoints / $maxPoints) * 100;
                $weightedSum += $categoryPercentage * $categoryWeight;
                $totalWeight += $categoryWeight;
            }
        }

        if ($totalWeight <= 0) {
            return $extraCreditSum;
        }

        $average = ($weightedSum / $totalWeight) + $extraCreditSum;

        return round(min(100, max(0, $average)), 2);
    }

    public function calculateClassGpa(Student $student, int $classId): float
    {
        $enrollments = $student->enrollments()
            ->whereHas('classCourse', fn ($q) => $q->where('class_id', $classId))
            ->whereNotNull('grade_points')
            ->with('classCourse.course')
            ->get();

        if ($enrollments->isEmpty()) {
            return 0.0;
        }

        $totalPoints = 0.0;
        $totalCredits = 0.0;

        foreach ($enrollments as $enrollment) {
            $credits = (float) ($enrollment->classCourse->course->credits ?? 1);
            $totalPoints += (float) $enrollment->grade_points * $credits;
            $totalCredits += $credits;
        }

        if ($totalCredits <= 0) {
            return 0.0;
        }

        return round($totalPoints / $totalCredits, 2);
    }

    public function calculateCumulativeGpa(Student $student): float
    {
        $enrollments = $student->enrollments()
            ->whereNotNull('grade_points')
            ->with('classCourse.course')
            ->get();

        if ($enrollments->isEmpty()) {
            return 0.0;
        }

        $totalPoints = 0.0;
        $totalCredits = 0.0;

        foreach ($enrollments as $enrollment) {
            $credits = (float) ($enrollment->classCourse->course->credits ?? 1);
            $totalPoints += (float) $enrollment->grade_points * $credits;
            $totalCredits += $credits;
        }

        if ($totalCredits <= 0) {
            return 0.0;
        }

        return round($totalPoints / $totalCredits, 2);
    }

    public function updateEnrollmentGrade(Enrollment $enrollment): void
    {
        $average = $this->calculateWeightedAverage($enrollment);

        $scale = GradingScale::default()->first();

        if (! $scale) {
            return;
        }

        $letter = $scale->getLetterGrade($average);
        $gpaPoints = $scale->getGpaPoints($letter);

        $enrollment->update([
            'weighted_average' => $average,
            'final_letter_grade' => $letter,
            'grade_points' => $gpaPoints,
        ]);
    }

    public function calculateClassRank(ClassCourse $classCourse): Collection
    {
        $enrollments = $classCourse->enrollments()
            ->enrolled()
            ->whereNotNull('weighted_average')
            ->with('student')
            ->orderByDesc('weighted_average')
            ->get();

        $rank = 0;
        $lastAverage = null;
        $skip = 0;

        return $enrollments->map(function ($enrollment) use (&$rank, &$lastAverage, &$skip) {
            $skip++;
            if ((float) $enrollment->weighted_average !== $lastAverage) {
                $rank = $skip;
                $lastAverage = (float) $enrollment->weighted_average;
            }

            return [
                'enrollment_id' => $enrollment->id,
                'student_id' => $enrollment->student_id,
                'weighted_average' => $enrollment->weighted_average,
                'rank' => $rank,
            ];
        });
    }

    public function updateAllClassCourseGrades(ClassCourse $classCourse): void
    {
        $enrollments = $classCourse->enrollments()->enrolled()->get();

        foreach ($enrollments as $enrollment) {
            $this->updateEnrollmentGrade($enrollment);
        }
    }
}
