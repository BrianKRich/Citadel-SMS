<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\SavesCustomFieldValues;
use App\Http\Controllers\Controller;
use App\Models\ClassCourse;
use App\Models\ClassModel;
use App\Models\CustomField;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EnrollmentController extends Controller
{
    use SavesCustomFieldValues;

    public function index(Request $request)
    {
        $enrollments = Enrollment::query()
            ->with([
                'student',
                'classCourse.course',
                'classCourse.employee' => fn($q) => $q->withTrashed(),
                'classCourse.class',
            ])
            ->when($request->student_id, function ($query, $studentId) {
                $query->student($studentId);
            })
            ->when($request->class_course_id, function ($query, $ccId) {
                $query->classCourse($ccId);
            })
            ->when($request->status, function ($query, $status) {
                $query->status($status);
            }, function ($query) {
                $query->where('status', '!=', 'dropped');
            })
            ->when($request->search, function ($query, $search) {
                $query->whereHas('student', function ($q) use ($search) {
                    $q->where('student_id', 'like', "%{$search}%")
                      ->orWhere('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $students = Student::active()->orderBy('first_name')->get();
        $classes  = ClassModel::orderBy('class_number')->get();

        return Inertia::render('Admin/Enrollment/Index', [
            'enrollments' => $enrollments,
            'students'    => $students,
            'classes'     => $classes,
            'filters'     => $request->only(['search', 'student_id', 'class_course_id', 'status']),
        ]);
    }

    public function create(Request $request)
    {
        $students = Student::active()->orderBy('first_name')->get();
        $classes  = ClassModel::orderBy('class_number')->get();

        $preselectedClassCourseId = $request->class_course_id ? (int) $request->class_course_id : null;

        // Available class-courses; always include the preselected one even if not "available"
        $classCourses = ClassCourse::query()
            ->where(function ($query) use ($preselectedClassCourseId) {
                $query->available();
                if ($preselectedClassCourseId) {
                    $query->orWhere('id', $preselectedClassCourseId);
                }
            })
            ->with(['course', 'class', 'employee'])
            ->when($request->class_id, function ($query, $classId) {
                $query->forClass($classId);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Admin/Enrollment/Create', [
            'students'               => $students,
            'classes'                => $classes,
            'classCourses'           => $classCourses,
            'selectedClassId'        => $request->class_id,
            'selectedClassCourseId'  => $preselectedClassCourseId,
            'customFields'           => CustomField::forEntity('Enrollment')->active()->orderBy('sort_order')->get(),
        ]);
    }

    public function enroll(Request $request)
    {
        $validated = $request->validate([
            'student_id'      => ['required', 'exists:students,id'],
            'class_course_id' => ['required', 'exists:class_courses,id'],
            'enrollment_date' => ['nullable', 'date'],
        ]);

        $student     = Student::findOrFail($validated['student_id']);
        $classCourse = ClassCourse::findOrFail($validated['class_course_id']);

        // Check if student is already enrolled
        $existingEnrollment = Enrollment::where('student_id', $student->id)
            ->where('class_course_id', $classCourse->id)
            ->first();

        if ($existingEnrollment) {
            return back()->withErrors([
                'error' => 'Student is already enrolled in this course.',
            ])->withInput();
        }

        // Check if course is full
        if ($classCourse->isFull()) {
            return back()->withErrors([
                'error' => 'Course is full. Cannot enroll more students.',
            ])->withInput();
        }

        // Check if course is open
        if ($classCourse->status !== 'open') {
            return back()->withErrors([
                'error' => 'Course is not open for enrollment.',
            ])->withInput();
        }

        $enrollment = Enrollment::create([
            'student_id'      => $student->id,
            'class_course_id' => $classCourse->id,
            'enrollment_date' => $validated['enrollment_date'] ?? now(),
            'status'          => 'enrolled',
        ]);

        $this->saveCustomFieldValues($request, 'Enrollment', $enrollment->id);

        return redirect()->route('admin.enrollment.index')
            ->with('success', "Successfully enrolled {$student->full_name} in {$classCourse->course->name}.");
    }

    public function drop(Enrollment $enrollment)
    {
        $enrollment->update(['status' => 'dropped']);

        return redirect()->route('admin.enrollment.index')
            ->with('success', 'Student dropped from course successfully.');
    }

    public function studentSchedule(Student $student)
    {
        $enrollments = $student->enrollments()
            ->with([
                'classCourse.course',
                'classCourse.employee' => fn($q) => $q->withTrashed(),
                'classCourse.class',
            ])
            ->enrolled()
            ->get();

        return Inertia::render('Admin/Enrollment/StudentSchedule', [
            'student'     => $student,
            'enrollments' => $enrollments,
        ]);
    }
}
