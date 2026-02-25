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
            ->with(['classCourse.course', 'classCourse.class.academicYear'])
            ->get();

        // Group enrollments by class, ordered chronologically
        $classGroups = $enrollments
            ->groupBy(fn ($e) => $e->classCourse->class_id)
            ->map(function ($classEnrollments) {
                $class = $classEnrollments->first()->classCourse->class;

                $classCredits = $classEnrollments->sum(
                    fn ($e) => (float) ($e->classCourse->course->credits ?? 1)
                );

                $classGpa = $this->gradeService->calculateClassGpa(
                    $classEnrollments->first()->student,
                    $class->id
                );

                return [
                    'class'        => $class,
                    'enrollments'  => $classEnrollments->values(),
                    'classGpa'     => $classGpa,
                    'classCredits' => $classCredits,
                ];
            })
            ->sortBy(fn ($group) => $group['class']->start_date)
            ->values();

        $totalCredits = $enrollments->sum(
            fn ($e) => (float) ($e->classCourse->course->credits ?? 1)
        );

        $cumulativeGpa = $this->gradeService->calculateCumulativeGpa($student);

        return [
            'student'       => $student,
            'official'      => $official,
            'classGroups'   => $classGroups,
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
