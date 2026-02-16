<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\ClassModel;
use App\Models\Grade;
use App\Models\Student;
use App\Services\GradeCalculationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GradeController extends Controller
{
    public function __construct(
        protected GradeCalculationService $gradeService
    ) {}

    public function index(Request $request)
    {
        $classes = ClassModel::with(['course', 'term', 'employee'])
            ->withCount('enrollments')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Grades/Index', [
            'classes' => $classes,
            'filters' => $request->only(['search']),
        ]);
    }

    public function classGrades(ClassModel $classModel)
    {
        $classModel->load([
            'course',
            'term',
            'assessments' => fn ($q) => $q->published()->with('category'),
            'enrollments' => fn ($q) => $q->enrolled()->with([
                'student',
                'grades',
            ]),
        ]);

        return Inertia::render('Admin/Grades/ClassGrades', [
            'classModel' => $classModel,
        ]);
    }

    public function enter(Assessment $assessment)
    {
        $assessment->load(['classModel.course', 'category']);

        $enrollments = $assessment->classModel->enrollments()
            ->enrolled()
            ->with(['student', 'grades' => fn ($q) => $q->where('assessment_id', $assessment->id)])
            ->get();

        return Inertia::render('Admin/Grades/Enter', [
            'assessment' => $assessment,
            'enrollments' => $enrollments,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'grades' => ['required', 'array', 'min:1'],
            'grades.*.enrollment_id' => ['required', 'exists:enrollments,id'],
            'grades.*.assessment_id' => ['required', 'exists:assessments,id'],
            'grades.*.score' => ['required', 'numeric', 'min:0'],
            'grades.*.notes' => ['nullable', 'string'],
            'grades.*.is_late' => ['boolean'],
            'grades.*.late_penalty' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $enrollmentIds = collect();

        foreach ($validated['grades'] as $gradeData) {
            Grade::updateOrCreate(
                [
                    'enrollment_id' => $gradeData['enrollment_id'],
                    'assessment_id' => $gradeData['assessment_id'],
                ],
                [
                    'score' => $gradeData['score'],
                    'notes' => $gradeData['notes'] ?? null,
                    'is_late' => $gradeData['is_late'] ?? false,
                    'late_penalty' => $gradeData['late_penalty'] ?? null,
                    'graded_by' => $request->user()->id,
                    'graded_at' => now(),
                ]
            );

            $enrollmentIds->push($gradeData['enrollment_id']);
        }

        // Recalculate grades for affected enrollments
        $enrollmentIds->unique()->each(function ($enrollmentId) {
            $enrollment = \App\Models\Enrollment::find($enrollmentId);
            if ($enrollment) {
                $this->gradeService->updateEnrollmentGrade($enrollment);
            }
        });

        return back()->with('success', 'Grades saved successfully.');
    }

    public function studentGrades(Student $student)
    {
        $student->load([
            'enrollments' => fn ($q) => $q->with([
                'class.course',
                'class.term',
                'grades.assessment.category',
            ]),
        ]);

        $cumulativeGpa = $this->gradeService->calculateCumulativeGpa($student);

        return Inertia::render('Admin/Grades/StudentGrades', [
            'student' => $student,
            'cumulativeGpa' => $cumulativeGpa,
        ]);
    }
}
