<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassModel;
use App\Models\Cohort;
use App\Models\CohortCourse;
use App\Models\Course;
use App\Models\EducationalInstitution;
use App\Models\Employee;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CohortCourseController extends Controller
{
    public function index(Request $request)
    {
        $cohortCourses = CohortCourse::query()
            ->with(['cohort.class.academicYear', 'course', 'employee', 'institution'])
            ->when($request->cohort_id, function ($query, $cohortId) {
                $query->cohort($cohortId);
            })
            ->when($request->course_id, function ($query, $courseId) {
                $query->course($courseId);
            })
            ->when($request->status, function ($query, $status) {
                $query->status($status);
            })
            ->when($request->instructor_type, function ($query, $type) {
                $query->where('instructor_type', $type);
            })
            ->when($request->search, function ($query, $search) {
                $query->search($search);
            })
            ->paginate(10)
            ->withQueryString();

        $courses = Course::active()->orderBy('name')->get();

        return Inertia::render('Admin/CohortCourses/Index', [
            'cohortCourses' => $cohortCourses,
            'courses'       => $courses,
            'filters'       => $request->only(['search', 'cohort_id', 'course_id', 'status', 'instructor_type']),
        ]);
    }

    public function create(Request $request)
    {
        $classes = ClassModel::with('cohorts')->orderBy('class_number')->get();
        $courses = Course::active()->orderBy('name')->get();
        $employees = Employee::active()->orderBy('first_name')->get();
        $institutions = EducationalInstitution::orderBy('name')->get();

        return Inertia::render('Admin/CohortCourses/Create', [
            'classes'      => $classes,
            'courses'      => $courses,
            'employees'    => $employees,
            'institutions' => $institutions,
            'preselectedCohortId' => $request->cohort_id,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cohort_id'       => ['required', 'exists:cohorts,id'],
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

        $cohortCourse = CohortCourse::create($validated);

        return redirect()->route('admin.cohort-courses.show', $cohortCourse)
            ->with('success', 'Course assignment created successfully.');
    }

    public function show(CohortCourse $cohortCourse)
    {
        $cohortCourse->load([
            'cohort.class.academicYear',
            'course',
            'employee',
            'institution',
            'enrollments.student',
        ]);

        $cohortCourse->append(['enrolled_count', 'available_seats']);

        return Inertia::render('Admin/CohortCourses/Show', [
            'cohortCourse' => $cohortCourse,
        ]);
    }

    public function edit(CohortCourse $cohortCourse)
    {
        $classes = ClassModel::with('cohorts')->orderBy('class_number')->get();
        $courses = Course::active()->orderBy('name')->get();
        $employees = Employee::active()->orderBy('first_name')->get();
        $institutions = EducationalInstitution::orderBy('name')->get();

        return Inertia::render('Admin/CohortCourses/Edit', [
            'cohortCourse' => $cohortCourse->load(['cohort.class', 'course', 'employee', 'institution']),
            'classes'      => $classes,
            'courses'      => $courses,
            'employees'    => $employees,
            'institutions' => $institutions,
        ]);
    }

    public function update(Request $request, CohortCourse $cohortCourse)
    {
        $validated = $request->validate([
            'cohort_id'       => ['required', 'exists:cohorts,id'],
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

        $cohortCourse->update($validated);

        return redirect()->route('admin.cohort-courses.show', $cohortCourse)
            ->with('success', 'Course assignment updated successfully.');
    }

    public function destroy(Request $request, CohortCourse $cohortCourse)
    {
        if ($cohortCourse->enrollments()->count() > 0) {
            return back()->withErrors([
                'error' => 'Cannot delete a course assignment with existing enrollments.',
            ]);
        }

        $classId = $cohortCourse->cohort?->class_id;
        $cohortCourse->delete();

        if ($request->from === 'class' && $classId) {
            return redirect()->route('admin.classes.show', $classId)
                ->with('success', 'Course removed from cohort successfully.');
        }

        return redirect()->route('admin.cohort-courses.index')
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
