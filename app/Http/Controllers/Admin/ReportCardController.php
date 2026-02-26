<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassModel;
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
        $classes = ClassModel::with('academicYear')->orderByDesc('start_date')->get();

        $students = Student::active()
            ->whereHas('enrollments')
            ->when($request->input('search'), fn ($q, $s) => $q->search($s))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/ReportCards/Index', [
            'students' => $students,
            'classes'  => $classes,
            'filters'  => $request->only(['search', 'class_id']),
        ]);
    }

    /**
     * Show report card preview for a student and class.
     */
    public function show(Request $request, Student $student)
    {
        $classes = ClassModel::with('academicYear')->orderByDesc('start_date')->get();
        $classId = $request->integer('class_id') ?: $classes->first()?->id;
        $class = $classId ? ClassModel::with('academicYear')->findOrFail($classId) : null;

        if (! $class) {
            return redirect()->route('admin.report-cards.index')
                ->with('error', 'No class found. Please select a class.');
        }

        $data = $this->reportCardService->getData($student, $class);

        return Inertia::render('Admin/ReportCards/Show', [
            'student'         => $student,
            'currentClass'    => $data['class'],
            'enrollments'     => $data['enrollments'],
            'classGpa'        => $data['classGpa'],
            'cumulativeGpa'   => $data['cumulativeGpa'],
            'classes'         => $classes,
            'selectedClassId' => $class->id,
        ]);
    }

    /**
     * Download PDF report card for a student and class.
     */
    public function pdf(Request $request, Student $student)
    {
        $classId = $request->integer('class_id');
        $class   = ClassModel::findOrFail($classId);

        $pdf = $this->reportCardService->generatePdf($student, $class);

        $filename = sprintf(
            'report-card-%s-%s.pdf',
            str($student->student_id)->slug(),
            str($class->class_number ?? '')->slug()
        );

        return $pdf->download($filename);
    }
}
