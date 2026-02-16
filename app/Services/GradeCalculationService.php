<?php

namespace App\Services;

use App\Models\ClassModel;
use App\Models\Enrollment;
use App\Models\GradingScale;
use App\Models\Student;
use App\Models\Term;

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

            // Calculate percentage within this category
            $earnedPoints = 0.0;
            $maxPoints = 0.0;

            foreach ($categoryGrades as $grade) {
                $assessment = $grade->assessment;

                if ($assessment->is_extra_credit) {
                    // Extra credit is additive, not part of normal weighting
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

        // Normalize to account for categories that may not sum to 1.0
        $average = ($weightedSum / $totalWeight) + $extraCreditSum;

        return round(min(100, max(0, $average)), 2);
    }

    public function calculateTermGpa(Student $student, Term $term): float
    {
        $enrollments = $student->enrollments()
            ->whereHas('class', fn ($q) => $q->where('term_id', $term->id))
            ->whereNotNull('grade_points')
            ->with('class.course')
            ->get();

        if ($enrollments->isEmpty()) {
            return 0.0;
        }

        $totalPoints = 0.0;
        $totalCredits = 0.0;

        foreach ($enrollments as $enrollment) {
            $credits = (float) ($enrollment->class->course->credits ?? 1);
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
            ->with('class.course')
            ->get();

        if ($enrollments->isEmpty()) {
            return 0.0;
        }

        $totalPoints = 0.0;
        $totalCredits = 0.0;

        foreach ($enrollments as $enrollment) {
            $credits = (float) ($enrollment->class->course->credits ?? 1);
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
            'final_grade' => $letter,
            'grade_points' => $gpaPoints,
        ]);
    }

    public function updateAllClassGrades(ClassModel $classModel): void
    {
        $enrollments = $classModel->enrollments()->enrolled()->get();

        foreach ($enrollments as $enrollment) {
            $this->updateEnrollmentGrade($enrollment);
        }
    }
}
