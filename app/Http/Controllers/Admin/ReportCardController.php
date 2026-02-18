<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Term;
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
        $terms = Term::with('academicYear')->orderByDesc('start_date')->get();
        $currentTerm = Term::current()->with('academicYear')->first();

        $selectedTermId = $request->integer('term_id') ?: $currentTerm?->id;

        $students = Student::active()
            ->when($request->input('search'), fn ($q, $s) => $q->search($s))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/ReportCards/Index', [
            'students'       => $students,
            'terms'          => $terms,
            'currentTerm'    => $currentTerm,
            'selectedTermId' => $selectedTermId,
            'filters'        => $request->only(['search', 'term_id']),
        ]);
    }

    /**
     * Show report card preview for a student and term.
     */
    public function show(Request $request, Student $student)
    {
        $terms = Term::with('academicYear')->orderByDesc('start_date')->get();
        $currentTerm = Term::current()->with('academicYear')->first();

        $termId = $request->integer('term_id') ?: $currentTerm?->id;
        $term = $termId ? Term::with('academicYear')->findOrFail($termId) : $currentTerm;

        if (! $term) {
            return redirect()->route('admin.report-cards.index')
                ->with('error', 'No term found. Please select a term.');
        }

        $data = $this->reportCardService->getData($student, $term);

        return Inertia::render('Admin/ReportCards/Show', [
            'student'       => $student,
            'term'          => $data['term'],
            'enrollments'   => $data['enrollments'],
            'termGpa'       => $data['termGpa'],
            'cumulativeGpa' => $data['cumulativeGpa'],
            'terms'         => $terms,
            'selectedTermId' => $term->id,
        ]);
    }

    /**
     * Download PDF report card for a student and term.
     */
    public function pdf(Request $request, Student $student)
    {
        $termId = $request->integer('term_id');
        $term = $termId
            ? Term::findOrFail($termId)
            : Term::current()->firstOrFail();

        $pdf = $this->reportCardService->generatePdf($student, $term);

        $filename = sprintf(
            'report-card-%s-%s.pdf',
            str($student->student_id)->slug(),
            str($term->name)->slug()
        );

        return $pdf->download($filename);
    }
}
