<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\SavesCustomFieldValues;
use App\Http\Controllers\Controller;
use App\Models\CustomField;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CourseController extends Controller
{
    use SavesCustomFieldValues;

    /**
     * Display a listing of courses
     */
    public function index(Request $request)
    {
        $query = Course::query();

        // Search by course code, name, or description
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by department
        if ($request->filled('department')) {
            $query->department($request->department);
        }

        // Filter by level
        if ($request->filled('level')) {
            $query->level($request->level);
        }

        // Filter by active status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $courses = $query->latest()
            ->paginate(10)
            ->withQueryString();

        // Get unique departments and levels for filters
        $departments = Course::distinct()->pluck('department')->filter();
        $levels = Course::distinct()->pluck('level')->filter();

        return Inertia::render('Admin/Courses/Index', [
            'courses' => $courses,
            'filters' => $request->only(['search', 'department', 'level', 'is_active']),
            'departments' => $departments,
            'levels' => $levels,
        ]);
    }

    /**
     * Show the form for creating a new course
     */
    public function create()
    {
        return Inertia::render('Admin/Courses/Create', [
            'customFields' => CustomField::forEntity('Course')->active()->orderBy('sort_order')->get(),
        ]);
    }

    /**
     * Store a newly created course
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_code' => ['required', 'string', 'max:255', 'unique:courses,course_code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'credits' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'department' => ['nullable', 'string', 'max:255'],
            'level' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $course = Course::create($validated);

        $this->saveCustomFieldValues($request, 'Course', $course->id);

        return redirect()->route('admin.courses.show', $course)
            ->with('success', "Course {$course->course_code} created successfully.");
    }

    /**
     * Display the specified course
     */
    public function show(Course $course)
    {
        $course->load(['cohortCourses.cohort.class', 'cohortCourses.employee', 'cohortCourses.institution']);

        return Inertia::render('Admin/Courses/Show', [
            'course' => $course,
            'customFields' => CustomField::forEntityWithValues('Course', $course->id),
        ]);
    }

    /**
     * Show the form for editing the specified course
     */
    public function edit(Course $course)
    {
        return Inertia::render('Admin/Courses/Edit', [
            'course' => $course,
            'customFields' => CustomField::forEntity('Course')->orderBy('sort_order')->get(),
        ]);
    }

    /**
     * Update the specified course
     */
    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'course_code' => ['required', 'string', 'max:255', Rule::unique('courses')->ignore($course->id)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'credits' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'department' => ['nullable', 'string', 'max:255'],
            'level' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $course->update($validated);

        $this->saveCustomFieldValues($request, 'Course', $course->id);

        return redirect()->route('admin.courses.show', $course)
            ->with('success', 'Course updated successfully.');
    }

    /**
     * Remove the specified course
     */
    public function destroy(Course $course)
    {
        $courseCode = $course->course_code;
        $course->delete();

        return redirect()->route('admin.courses.index')
            ->with('success', "Course {$courseCode} deleted successfully.");
    }
}
