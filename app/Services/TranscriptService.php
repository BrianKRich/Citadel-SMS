<?php

namespace App\Services;

use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;

class TranscriptService
{
    public function __construct(
        protected GradeCalculationService $gradeService
    ) {}

    /**
     * Assemble all data needed to render the transcript template.
     */
    public function getData(Student $student, bool $official = false): array
    {
        $enrollments = $student->enrollments()
            ->with(['cohortCourse.course', 'cohortCourse.cohort.class.academicYear'])
            ->get();

        // Group enrollments by cohort, ordered chronologically
        $cohortGroups = $enrollments
            ->groupBy(fn ($e) => $e->cohortCourse->cohort_id)
            ->map(function ($cohortEnrollments) {
                $cohort = $cohortEnrollments->first()->cohortCourse->cohort;

                $cohortCredits = $cohortEnrollments->sum(
                    fn ($e) => (float) ($e->cohortCourse->course->credits ?? 1)
                );

                $cohortGpa = $this->gradeService->calculateCohortGpa(
                    $cohortEnrollments->first()->student,
                    $cohort->id
                );

                return [
                    'cohort'      => $cohort,
                    'enrollments' => $cohortEnrollments->values(),
                    'cohortGpa'   => $cohortGpa,
                    'cohortCredits' => $cohortCredits,
                ];
            })
            ->sortBy(fn ($group) => $group['cohort']->start_date)
            ->values();

        $totalCredits = $enrollments->sum(
            fn ($e) => (float) ($e->cohortCourse->course->credits ?? 1)
        );

        $cumulativeGpa = $this->gradeService->calculateCumulativeGpa($student);

        return [
            'student'       => $student,
            'official'      => $official,
            'cohortGroups'  => $cohortGroups,
            'totalCredits'  => $totalCredits,
            'cumulativeGpa' => $cumulativeGpa,
            'generatedAt'   => now(),
        ];
    }

    /**
     * Generate a DomPDF instance for the given student.
     */
    public function generatePdf(Student $student, bool $official = false): \Barryvdh\DomPDF\PDF
    {
        $data = $this->getData($student, $official);

        return Pdf::loadView('pdf.transcript', $data)
            ->setPaper('letter', 'portrait');
    }
}
