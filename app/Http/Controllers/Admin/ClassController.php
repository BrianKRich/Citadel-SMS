<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Course;
use App\Models\Teacher;
use App\Models\Term;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClassController extends Controller
{
    /**
     * Display a listing of classes
     */
    public function index(Request $request)
    {
        $classes = ClassModel::query()
            ->with(['course', 'teacher', 'term.academicYear'])
            ->when($request->term_id, function ($query, $termId) {
                $query->term($termId);
            })
            ->when($request->course_id, function ($query, $courseId) {
                $query->course($courseId);
            })
            ->when($request->teacher_id, function ($query, $teacherId) {
                $query->teacher($teacherId);
            })
            ->when($request->status, function ($query, $status) {
                $query->status($status);
            })
            ->when($request->search, function ($query, $search) {
                $query->search($search);
            })
            ->paginate(10)
            ->withQueryString();

        // Get filter options
        $terms = Term::with('academicYear')->orderBy('start_date', 'desc')->get();
        $courses = Course::active()->orderBy('name')->get();
        $teachers = Teacher::active()->orderBy('first_name')->get();

        return Inertia::render('Admin/Classes/Index', [
            'classes' => $classes,
            'terms' => $terms,
            'courses' => $courses,
            'teachers' => $teachers,
            'filters' => $request->only(['search', 'term_id', 'course_id', 'teacher_id', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new class
     */
    public function create()
    {
        $courses = Course::active()->orderBy('name')->get();
        $teachers = Teacher::active()->orderBy('first_name')->get();
        $academicYears = AcademicYear::with('terms')->orderBy('start_date', 'desc')->get();

        return Inertia::render('Admin/Classes/Create', [
            'courses' => $courses,
            'teachers' => $teachers,
            'academicYears' => $academicYears,
        ]);
    }

    /**
     * Store a newly created class
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'teacher_id' => ['required', 'exists:teachers,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'term_id' => ['required', 'exists:terms,id'],
            'section_name' => ['required', 'string', 'max:255'],
            'room' => ['nullable', 'string', 'max:255'],
            'schedule' => ['nullable', 'array'],
            'schedule.*.day' => ['required', 'string', 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday'],
            'schedule.*.start_time' => ['required', 'string'],
            'schedule.*.end_time' => ['required', 'string'],
            'max_students' => ['required', 'integer', 'min:1', 'max:500'],
            'status' => ['required', 'in:open,closed,in_progress,completed'],
        ]);

        // Check for schedule conflicts with teacher's other classes
        if (!empty($validated['schedule'])) {
            $conflict = $this->checkScheduleConflict(
                $validated['teacher_id'],
                $validated['term_id'],
                $validated['schedule']
            );

            if ($conflict) {
                return back()->withErrors([
                    'schedule' => "Schedule conflict detected with {$conflict->course->name} ({$conflict->section_name})"
                ])->withInput();
            }
        }

        $class = ClassModel::create($validated);

        return redirect()->route('admin.classes.show', $class)
            ->with('success', 'Class created successfully.');
    }

    /**
     * Display the specified class
     */
    public function show(ClassModel $class)
    {
        $class->load([
            'course',
            'teacher',
            'academicYear',
            'term',
            'enrollments' => function ($query) {
                $query->with('student')->where('status', 'enrolled');
            }
        ]);

        return Inertia::render('Admin/Classes/Show', [
            'class' => $class,
        ]);
    }

    /**
     * Show the form for editing the specified class
     */
    public function edit(ClassModel $class)
    {
        $courses = Course::active()->orderBy('name')->get();
        $teachers = Teacher::active()->orderBy('first_name')->get();
        $academicYears = AcademicYear::with('terms')->orderBy('start_date', 'desc')->get();

        return Inertia::render('Admin/Classes/Edit', [
            'class' => $class->load(['course', 'teacher', 'academicYear', 'term']),
            'courses' => $courses,
            'teachers' => $teachers,
            'academicYears' => $academicYears,
        ]);
    }

    /**
     * Update the specified class
     */
    public function update(Request $request, ClassModel $class)
    {
        $validated = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'teacher_id' => ['required', 'exists:teachers,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'term_id' => ['required', 'exists:terms,id'],
            'section_name' => ['required', 'string', 'max:255'],
            'room' => ['nullable', 'string', 'max:255'],
            'schedule' => ['nullable', 'array'],
            'schedule.*.day' => ['required', 'string', 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday'],
            'schedule.*.start_time' => ['required', 'string'],
            'schedule.*.end_time' => ['required', 'string'],
            'max_students' => ['required', 'integer', 'min:1', 'max:500'],
            'status' => ['required', 'in:open,closed,in_progress,completed'],
        ]);

        // Check for schedule conflicts (excluding current class)
        if (!empty($validated['schedule'])) {
            $conflict = $this->checkScheduleConflict(
                $validated['teacher_id'],
                $validated['term_id'],
                $validated['schedule'],
                $class->id
            );

            if ($conflict) {
                return back()->withErrors([
                    'schedule' => "Schedule conflict detected with {$conflict->course->name} ({$conflict->section_name})"
                ])->withInput();
            }
        }

        // Check if reducing max_students below current enrollments
        $enrolledCount = $class->enrollments()->where('status', 'enrolled')->count();
        if ($validated['max_students'] < $enrolledCount) {
            return back()->withErrors([
                'max_students' => "Cannot reduce capacity below current enrollment count ({$enrolledCount} students)"
            ])->withInput();
        }

        $class->update($validated);

        return redirect()->route('admin.classes.show', $class)
            ->with('success', 'Class updated successfully.');
    }

    /**
     * Remove the specified class
     */
    public function destroy(ClassModel $class)
    {
        // Check if class has enrollments
        if ($class->enrollments()->count() > 0) {
            return back()->withErrors([
                'error' => 'Cannot delete class with existing enrollments. Please remove all enrollments first.'
            ]);
        }

        $class->delete();

        return redirect()->route('admin.classes.index')
            ->with('success', 'Class deleted successfully.');
    }

    /**
     * Check for schedule conflicts with teacher's other classes
     */
    private function checkScheduleConflict($teacherId, $termId, $schedule, $excludeClassId = null)
    {
        $teacherClasses = ClassModel::where('teacher_id', $teacherId)
            ->where('term_id', $termId)
            ->when($excludeClassId, function ($query, $excludeId) {
                $query->where('id', '!=', $excludeId);
            })
            ->with('course')
            ->get();

        foreach ($teacherClasses as $existingClass) {
            if (empty($existingClass->schedule)) {
                continue;
            }

            foreach ($schedule as $newSlot) {
                foreach ($existingClass->schedule as $existingSlot) {
                    if ($newSlot['day'] === $existingSlot['day']) {
                        // Check for time overlap
                        if ($this->timesOverlap(
                            $newSlot['start_time'], $newSlot['end_time'],
                            $existingSlot['start_time'], $existingSlot['end_time']
                        )) {
                            return $existingClass;
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * Check if two time ranges overlap
     */
    private function timesOverlap($start1, $end1, $start2, $end2)
    {
        return ($start1 < $end2) && ($end1 > $start2);
    }
}
