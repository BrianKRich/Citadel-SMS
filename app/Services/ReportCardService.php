<?php

namespace App\Services;

use App\Models\ClassModel;
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
    public function getData(Student $student, ClassModel $class): array
    {
        $class->load('academicYear');

        $enrollments = $student->enrollments()
            ->whereHas('classCourse', fn ($q) => $q->where('class_id', $class->id))
            ->with(['classCourse.course', 'classCourse.employee'])
            ->get();

        $classGpa = $this->gradeService->calculateClassGpa($student, $class->id);
        $cumulativeGpa = $this->gradeService->calculateCumulativeGpa($student);

        return [
            'student'       => $student,
            'class'         => $class,
            'enrollments'   => $enrollments,
            'classGpa'      => $classGpa,
            'cumulativeGpa' => $cumulativeGpa,
            'generatedAt'   => now(),
        ];
    }

    /**
     * Generate a DomPDF instance for the given student and class.
     */
    public function generatePdf(Student $student, ClassModel $class): \Barryvdh\DomPDF\PDF
    {
        $data = $this->getData($student, $class);

        return Pdf::loadView('pdf.report-card', $data)
            ->setPaper('letter', 'portrait');
    }
}
