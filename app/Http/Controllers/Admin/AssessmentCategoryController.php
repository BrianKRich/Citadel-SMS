<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssessmentCategory;
use App\Models\Course;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AssessmentCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = AssessmentCategory::with('course');

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('course_id')) {
            $query->course($request->course_id);
        }

        if ($request->boolean('global_only')) {
            $query->global();
        }

        $categories = $query->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/AssessmentCategories/Index', [
            'categories' => $categories,
            'filters' => $request->only(['search', 'course_id', 'global_only']),
            'courses' => Course::active()->orderBy('name')->get(['id', 'name', 'course_code']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/AssessmentCategories/Create', [
            'courses' => Course::active()->orderBy('name')->get(['id', 'name', 'course_code']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => ['nullable', 'exists:courses,id'],
            'name' => ['required', 'string', 'max:255'],
            'weight' => ['required', 'numeric', 'min:0', 'max:1'],
            'description' => ['nullable', 'string'],
        ]);

        AssessmentCategory::create($validated);

        return redirect()->route('admin.assessment-categories.index')
            ->with('success', 'Assessment category created successfully.');
    }

    public function edit(AssessmentCategory $assessmentCategory)
    {
        return Inertia::render('Admin/AssessmentCategories/Edit', [
            'category' => $assessmentCategory->load('course'),
            'courses' => Course::active()->orderBy('name')->get(['id', 'name', 'course_code']),
        ]);
    }

    public function update(Request $request, AssessmentCategory $assessmentCategory)
    {
        $validated = $request->validate([
            'course_id' => ['nullable', 'exists:courses,id'],
            'name' => ['required', 'string', 'max:255'],
            'weight' => ['required', 'numeric', 'min:0', 'max:1'],
            'description' => ['nullable', 'string'],
        ]);

        $assessmentCategory->update($validated);

        return redirect()->route('admin.assessment-categories.index')
            ->with('success', 'Assessment category updated successfully.');
    }

    public function destroy(AssessmentCategory $assessmentCategory)
    {
        if ($assessmentCategory->assessments()->exists()) {
            return back()->with('error', 'Cannot delete category with linked assessments.');
        }

        $assessmentCategory->delete();

        return redirect()->route('admin.assessment-categories.index')
            ->with('success', 'Assessment category deleted successfully.');
    }
}
