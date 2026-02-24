<?php

namespace App\Services;

use App\Models\Cohort;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportCardService
{
    public function __construct(
        protected GradeCalculationService $gradeService
    ) {}

    /**
     * Assemble all data needed to render the report card template.
     */
    public function getData(Student $student, Cohort $cohort): array
    {
        $cohort->load('class.academicYear');

        $enrollments = $student->enrollments()
            ->whereHas('cohortCourse', fn ($q) => $q->where('cohort_id', $cohort->id))
            ->with(['cohortCourse.course', 'cohortCourse.employee'])
            ->get();

        $cohortGpa = $this->gradeService->calculateCohortGpa($student, $cohort->id);
        $cumulativeGpa = $this->gradeService->calculateCumulativeGpa($student);

        return [
            'student'       => $student,
            'cohort'        => $cohort,
            'enrollments'   => $enrollments,
            'cohortGpa'     => $cohortGpa,
            'cumulativeGpa' => $cumulativeGpa,
            'generatedAt'   => now(),
        ];
    }

    /**
     * Generate a DomPDF instance for the given student and cohort.
     */
    public function generatePdf(Student $student, Cohort $cohort): \Barryvdh\DomPDF\PDF
    {
        $data = $this->getData($student, $cohort);

        return Pdf::loadView('pdf.report-card', $data)
            ->setPaper('letter', 'portrait');
    }
}
