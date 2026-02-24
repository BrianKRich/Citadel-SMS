<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cohort;
use App\Models\Student;
use App\Services\ReportCardService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportCardController extends Controller
{
    public function __construct(
        protected ReportCardService $reportCardService
    ) {}

    /**
     * List students for report card selection.
     */
    public function index(Request $request)
    {
        $cohorts = Cohort::with('class.academicYear')->orderByDesc('start_date')->get();

        $students = Student::active()
            ->when($request->input('search'), fn ($q, $s) => $q->search($s))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/ReportCards/Index', [
            'students' => $students,
            'cohorts'  => $cohorts,
            'filters'  => $request->only(['search', 'cohort_id']),
        ]);
    }

    /**
     * Show report card preview for a student and cohort.
     */
    public function show(Request $request, Student $student)
    {
        $cohorts = Cohort::with('class.academicYear')->orderByDesc('start_date')->get();
        $cohortId = $request->integer('cohort_id') ?: $cohorts->first()?->id;
        $cohort = $cohortId ? Cohort::with('class.academicYear')->findOrFail($cohortId) : null;

        if (! $cohort) {
            return redirect()->route('admin.report-cards.index')
                ->with('error', 'No cohort found. Please select a cohort.');
        }

        $data = $this->reportCardService->getData($student, $cohort);

        return Inertia::render('Admin/ReportCards/Show', [
            'student'        => $student,
            'cohort'         => $data['cohort'],
            'enrollments'    => $data['enrollments'],
            'cohortGpa'      => $data['cohortGpa'],
            'cumulativeGpa'  => $data['cumulativeGpa'],
            'cohorts'        => $cohorts,
            'selectedCohortId' => $cohort->id,
        ]);
    }

    /**
     * Download PDF report card for a student and cohort.
     */
    public function pdf(Request $request, Student $student)
    {
        $cohortId = $request->integer('cohort_id');
        $cohort   = Cohort::findOrFail($cohortId);

        $pdf = $this->reportCardService->generatePdf($student, $cohort);

        $filename = sprintf(
            'report-card-%s-%s-%s.pdf',
            str($student->student_id)->slug(),
            str($cohort->class->class_number ?? '')->slug(),
            $cohort->name
        );

        return $pdf->download($filename);
    }
}
