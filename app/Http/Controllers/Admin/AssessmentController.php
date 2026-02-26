<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentCategory;
use App\Models\ClassCourse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AssessmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Assessment::with(['classCourse.course', 'classCourse.class', 'category']);

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('class_course_id')) {
            $query->classCourse($request->class_course_id);
        }

        if ($request->filled('category_id')) {
            $query->category($request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $assessments = $query->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Assessments/Index', [
            'assessments'  => $assessments,
            'filters'      => $request->only(['search', 'class_course_id', 'category_id', 'status']),
            'classCourses' => ClassCourse::with('course')->orderBy('id')->get(),
            'categories'   => AssessmentCategory::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Assessments/Create', [
            'classCourses' => ClassCourse::with('course')->orderBy('id')->get(),
            'categories'   => AssessmentCategory::orderBy('name')->get(['id', 'name', 'weight']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'class_course_id'        => ['required', 'exists:class_courses,id'],
            'assessment_category_id' => ['required', 'exists:assessment_categories,id'],
            'name'                   => ['required', 'string', 'max:255'],
            'max_score'              => ['required', 'numeric', 'min:0.01'],
            'due_date'               => ['nullable', 'date'],
            'weight'                 => ['nullable', 'numeric', 'min:0', 'max:1'],
            'is_extra_credit'        => ['boolean'],
            'status'                 => ['required', 'in:draft,published'],
        ]);

        $assessment = Assessment::create($validated);

        return redirect()->route('admin.assessments.show', $assessment)
            ->with('success', 'Assessment created successfully.');
    }

    public function show(Assessment $assessment)
    {
        $assessment->load(['classCourse.course', 'category', 'grades.enrollment.student']);

        $gradeStats = null;
        if ($assessment->grades->isNotEmpty()) {
            $scores = $assessment->grades->pluck('score');
            $gradeStats = [
                'average' => round($scores->avg(), 2),
                'min'     => $scores->min(),
                'max'     => $scores->max(),
                'count'   => $scores->count(),
            ];
        }

        return Inertia::render('Admin/Assessments/Show', [
            'assessment' => $assessment,
            'gradeStats' => $gradeStats,
        ]);
    }

    public function edit(Assessment $assessment)
    {
        return Inertia::render('Admin/Assessments/Edit', [
            'assessment'   => $assessment->load(['classCourse', 'category']),
            'classCourses' => ClassCourse::with('course')->orderBy('id')->get(),
            'categories'   => AssessmentCategory::orderBy('name')->get(['id', 'name', 'weight']),
        ]);
    }

    public function update(Request $request, Assessment $assessment)
    {
        $validated = $request->validate([
            'class_course_id'        => ['required', 'exists:class_courses,id'],
            'assessment_category_id' => ['required', 'exists:assessment_categories,id'],
            'name'                   => ['required', 'string', 'max:255'],
            'max_score'              => ['required', 'numeric', 'min:0.01'],
            'due_date'               => ['nullable', 'date'],
            'weight'                 => ['nullable', 'numeric', 'min:0', 'max:1'],
            'is_extra_credit'        => ['boolean'],
            'status'                 => ['required', 'in:draft,published'],
        ]);

        $assessment->update($validated);

        return redirect()->route('admin.assessments.show', $assessment)
            ->with('success', 'Assessment updated successfully.');
    }

    public function destroy(Assessment $assessment)
    {
        $assessment->delete();

        return redirect()->route('admin.assessments.index')
            ->with('success', 'Assessment deleted successfully.');
    }
}
