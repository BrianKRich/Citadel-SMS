<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Term;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ReportCardService
{
    public function __construct(
        protected GradeCalculationService $gradeService
    ) {}

    /**
     * Assemble all data needed to render the report card template.
     */
    public function getData(Student $student, Term $term): array
    {
        $term->load('academicYear');

        $enrollments = $student->enrollments()
            ->whereHas('class', fn ($q) => $q->where('term_id', $term->id))
            ->with(['class.course', 'class.employee'])
            ->get();

        $termGpa = $this->gradeService->calculateTermGpa($student, $term);
        $cumulativeGpa = $this->gradeService->calculateCumulativeGpa($student);

        return [
            'student'       => $student,
            'term'          => $term,
            'enrollments'   => $enrollments,
            'termGpa'       => $termGpa,
            'cumulativeGpa' => $cumulativeGpa,
            'generatedAt'   => now(),
        ];
    }

    /**
     * Generate a DomPDF instance for the given student and term.
     */
    public function generatePdf(Student $student, Term $term): \Barryvdh\DomPDF\PDF
    {
        $data = $this->getData($student, $term);

        return Pdf::loadView('pdf.report-card', $data)
            ->setPaper('letter', 'portrait');
    }
}
