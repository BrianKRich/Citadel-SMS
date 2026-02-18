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
            ->with(['class.course', 'class.term.academicYear'])
            ->get();

        // Group enrollments by term, ordered chronologically
        $termGroups = $enrollments
            ->groupBy(fn ($e) => $e->class->term_id)
            ->map(function ($termEnrollments) {
                $term = $termEnrollments->first()->class->term;

                $termCredits = $termEnrollments->sum(
                    fn ($e) => (float) ($e->class->course->credits ?? 1)
                );

                $termGpa = $this->gradeService->calculateTermGpa(
                    $termEnrollments->first()->student,
                    $term
                );

                return [
                    'term'        => $term,
                    'enrollments' => $termEnrollments->values(),
                    'termGpa'     => $termGpa,
                    'termCredits' => $termCredits,
                ];
            })
            ->sortBy(fn ($group) => $group['term']->start_date)
            ->values();

        $totalCredits = $enrollments->sum(
            fn ($e) => (float) ($e->class->course->credits ?? 1)
        );

        $cumulativeGpa = $this->gradeService->calculateCumulativeGpa($student);

        return [
            'student'       => $student,
            'official'      => $official,
            'termGroups'    => $termGroups,
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
