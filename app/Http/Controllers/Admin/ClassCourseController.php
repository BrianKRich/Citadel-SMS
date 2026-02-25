<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassCourse;
use App\Models\ClassModel;
use App\Models\Course;
use App\Models\EducationalInstitution;
use App\Models\Employee;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClassCourseController extends Controller
{
    public function index(Request $request)
    {
        $classCourses = ClassCourse::query()
            ->with(['class.academicYear', 'course', 'employee', 'institution'])
            ->when($request->class_id, function ($query, $classId) {
                $query->forClass($classId);
            })
            ->when($request->course_id, function ($query, $courseId) {
                $query->where('course_id', $courseId);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->instructor_type, function ($query, $type) {
                $query->where('instructor_type', $type);
            })
            ->when($request->search, function ($query, $search) {
                $query->whereHas('course', fn ($q) => $q->where('name', 'like', "%{$search}%")
                    ->orWhere('course_code', 'like', "%{$search}%"));
            })
            ->paginate(10)
            ->withQueryString();

        $courses = Course::active()->orderBy('name')->get();

        return Inertia::render('Admin/ClassCourses/Index', [
            'classCourses' => $classCourses,
            'courses'      => $courses,
            'filters'      => $request->only(['search', 'class_id', 'course_id', 'status', 'instructor_type']),
        ]);
    }

    public function create(Request $request)
    {
        $classes = ClassModel::orderBy('class_number')->get();
        $courses = Course::active()->orderBy('name')->get();
        $employees = Employee::active()->orderBy('first_name')->get();
        $institutions = EducationalInstitution::orderBy('name')->get();

        return Inertia::render('Admin/ClassCourses/Create', [
            'classes'            => $classes,
            'courses'            => $courses,
            'employees'          => $employees,
            'institutions'       => $institutions,
            'preselectedClassId' => $request->class_id ? (int) $request->class_id : null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'class_id'        => ['required', 'exists:classes,id'],
            'course_id'       => ['required', 'exists:courses,id'],
            'instructor_type' => ['required', 'in:staff,technical_college,university'],
            'employee_id'     => ['nullable', 'exists:employees,id'],
            'institution_id'  => ['nullable', 'exists:educational_institutions,id'],
            'room'            => ['nullable', 'string', 'max:255'],
            'schedule'        => ['nullable', 'array'],
            'schedule.*.day'        => ['required', 'string', 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday'],
            'schedule.*.start_time' => ['required', 'string'],
            'schedule.*.end_time'   => ['required', 'string'],
            'max_students'    => ['required', 'integer', 'min:1', 'max:500'],
            'status'          => ['required', 'in:open,closed,in_progress,completed'],
        ]);

        $this->validateInstructor($validated);

        $classCourse = ClassCourse::create($validated);

        return redirect()->route('admin.class-courses.show', $classCourse)
            ->with('success', 'Course assignment created successfully.');
    }

    public function show(ClassCourse $classCourse)
    {
        $classCourse->load([
            'class.academicYear',
            'course',
            'employee',
            'institution',
            'enrollments.student',
        ]);

        $classCourse->append(['enrolled_count', 'available_seats']);

        return Inertia::render('Admin/ClassCourses/Show', [
            'classCourse' => $classCourse,
        ]);
    }

    public function edit(ClassCourse $classCourse)
    {
        $classes = ClassModel::orderBy('class_number')->get();
        $courses = Course::active()->orderBy('name')->get();
        $employees = Employee::active()->orderBy('first_name')->get();
        $institutions = EducationalInstitution::orderBy('name')->get();

        return Inertia::render('Admin/ClassCourses/Edit', [
            'classCourse'  => $classCourse->load(['class', 'course', 'employee', 'institution']),
            'classes'      => $classes,
            'courses'      => $courses,
            'employees'    => $employees,
            'institutions' => $institutions,
        ]);
    }

    public function update(Request $request, ClassCourse $classCourse)
    {
        $validated = $request->validate([
            'class_id'        => ['required', 'exists:classes,id'],
            'course_id'       => ['required', 'exists:courses,id'],
            'instructor_type' => ['required', 'in:staff,technical_college,university'],
            'employee_id'     => ['nullable', 'exists:employees,id'],
            'institution_id'  => ['nullable', 'exists:educational_institutions,id'],
            'room'            => ['nullable', 'string', 'max:255'],
            'schedule'        => ['nullable', 'array'],
            'schedule.*.day'        => ['required', 'string', 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday'],
            'schedule.*.start_time' => ['required', 'string'],
            'schedule.*.end_time'   => ['required', 'string'],
            'max_students'    => ['required', 'integer', 'min:1', 'max:500'],
            'status'          => ['required', 'in:open,closed,in_progress,completed'],
        ]);

        $this->validateInstructor($validated);

        $classCourse->update($validated);

        return redirect()->route('admin.class-courses.show', $classCourse)
            ->with('success', 'Course assignment updated successfully.');
    }

    public function destroy(Request $request, ClassCourse $classCourse)
    {
        if ($classCourse->enrollments()->count() > 0) {
            return back()->withErrors([
                'error' => 'Cannot delete a course assignment with existing enrollments.',
            ]);
        }

        $classId = $classCourse->class_id;
        $classCourse->delete();

        if ($request->from === 'class' && $classId) {
            return redirect()->route('admin.classes.show', $classId)
                ->with('success', 'Course removed from class successfully.');
        }

        return redirect()->route('admin.class-courses.index')
            ->with('success', 'Course assignment deleted successfully.');
    }

    private function validateInstructor(array $validated): void
    {
        if ($validated['instructor_type'] === 'staff' && empty($validated['employee_id'])) {
            abort(422, 'An employee must be selected for staff instructors.');
        }

        if (in_array($validated['instructor_type'], ['technical_college', 'university']) && empty($validated['institution_id'])) {
            abort(422, 'An institution must be selected for college/university instructors.');
        }
    }
}
