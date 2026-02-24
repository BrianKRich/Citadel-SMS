<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\TranscriptService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TranscriptController extends Controller
{
    public function __construct(
        protected TranscriptService $transcriptService
    ) {}

    /**
     * List students for transcript selection.
     */
    public function index(Request $request)
    {
        $students = Student::active()
            ->when($request->input('search'), fn ($q, $s) => $q->search($s))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Transcripts/Index', [
            'students' => $students,
            'filters'  => $request->only(['search']),
        ]);
    }

    /**
     * Show transcript preview for a student.
     */
    public function show(Request $request, Student $student)
    {
        $official = $request->boolean('official');

        $data = $this->transcriptService->getData($student, $official);

        return Inertia::render('Admin/Transcripts/Show', [
            'student'       => $student,
            'official'      => $official,
            'cohortGroups'  => $data['cohortGroups'],
            'totalCredits'  => $data['totalCredits'],
            'cumulativeGpa' => $data['cumulativeGpa'],
        ]);
    }

    /**
     * Download PDF transcript for a student.
     */
    public function pdf(Request $request, Student $student)
    {
        $official = $request->boolean('official');

        $pdf = $this->transcriptService->generatePdf($student, $official);

        $type = $official ? 'official' : 'unofficial';
        $filename = sprintf(
            'transcript-%s-%s.pdf',
            str($student->student_id)->slug(),
            $type
        );

        return $pdf->download($filename);
    }
}
