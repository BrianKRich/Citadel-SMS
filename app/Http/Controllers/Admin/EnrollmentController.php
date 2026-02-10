<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassModel;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Term;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EnrollmentController extends Controller
{
    /**
     * Display a listing of enrollments
     */
    public function index(Request $request)
    {
        $enrollments = Enrollment::query()
            ->with(['student', 'class.course', 'class.teacher', 'class.term'])
            ->when($request->student_id, function ($query, $studentId) {
                $query->student($studentId);
            })
            ->when($request->class_id, function ($query, $classId) {
                $query->class($classId);
            })
            ->when($request->term_id, function ($query, $termId) {
                $query->term($termId);
            })
            ->when($request->status, function ($query, $status) {
                $query->status($status);
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
        $terms = Term::with('academicYear')->orderBy('start_date', 'desc')->get();

        return Inertia::render('Admin/Enrollment/Index', [
            'enrollments' => $enrollments,
            'students' => $students,
            'terms' => $terms,
            'filters' => $request->only(['search', 'student_id', 'class_id', 'term_id', 'status']),
        ]);
    }

    /**
     * Show the form for enrolling a student
     */
    public function create(Request $request)
    {
        $students = Student::active()->orderBy('first_name')->get();
        $terms = Term::with('academicYear')->orderBy('start_date', 'desc')->get();

        // Get available classes (open and not full)
        $classes = ClassModel::query()
            ->available()
            ->with(['course', 'teacher', 'term'])
            ->when($request->term_id, function ($query, $termId) {
                $query->where('term_id', $termId);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Admin/Enrollment/Create', [
            'students' => $students,
            'terms' => $terms,
            'classes' => $classes,
            'selectedTermId' => $request->term_id,
        ]);
    }

    /**
     * Enroll a student in a class
     */
    public function enroll(Request $request)
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'enrollment_date' => ['nullable', 'date'],
        ]);

        $student = Student::findOrFail($validated['student_id']);
        $class = ClassModel::findOrFail($validated['class_id']);

        // Check if student is already enrolled
        $existingEnrollment = Enrollment::where('student_id', $student->id)
            ->where('class_id', $class->id)
            ->first();

        if ($existingEnrollment) {
            return back()->withErrors([
                'error' => 'Student is already enrolled in this class.'
            ])->withInput();
        }

        // Check if class is full
        if ($class->isFull()) {
            return back()->withErrors([
                'error' => 'Class is full. Cannot enroll more students.'
            ])->withInput();
        }

        // Check if class is open
        if ($class->status !== 'open') {
            return back()->withErrors([
                'error' => 'Class is not open for enrollment.'
            ])->withInput();
        }

        // Check for schedule conflicts with student's other enrolled classes
        $conflict = $this->checkStudentScheduleConflict($student->id, $class);
        if ($conflict) {
            return back()->withErrors([
                'error' => "Schedule conflict with {$conflict->course->name} ({$conflict->section_name})"
            ])->withInput();
        }

        // Create enrollment
        Enrollment::create([
            'student_id' => $student->id,
            'class_id' => $class->id,
            'enrollment_date' => $validated['enrollment_date'] ?? now(),
            'status' => 'enrolled',
        ]);

        return redirect()->route('admin.enrollment.index')
            ->with('success', "Successfully enrolled {$student->full_name} in {$class->course->name}.");
    }

    /**
     * Drop a student from a class
     */
    public function drop(Enrollment $enrollment)
    {
        // Check if enrollment has grades
        // Note: This will be implemented in Phase 3
        // For now, we can always drop

        $enrollment->update([
            'status' => 'dropped',
        ]);

        return redirect()->route('admin.enrollment.index')
            ->with('success', 'Student dropped from class successfully.');
    }

    /**
     * Show student's schedule
     */
    public function studentSchedule(Student $student)
    {
        $enrollments = $student->enrollments()
            ->with(['class.course', 'class.teacher', 'class.term'])
            ->enrolled()
            ->get();

        return Inertia::render('Admin/Enrollment/StudentSchedule', [
            'student' => $student,
            'enrollments' => $enrollments,
        ]);
    }

    /**
     * Check for schedule conflicts with student's enrolled classes
     */
    private function checkStudentScheduleConflict($studentId, ClassModel $newClass)
    {
        if (empty($newClass->schedule)) {
            return null;
        }

        $studentClasses = ClassModel::query()
            ->whereHas('enrollments', function ($query) use ($studentId) {
                $query->where('student_id', $studentId)
                      ->where('status', 'enrolled');
            })
            ->where('term_id', $newClass->term_id)
            ->where('id', '!=', $newClass->id)
            ->with('course')
            ->get();

        foreach ($studentClasses as $existingClass) {
            if (empty($existingClass->schedule)) {
                continue;
            }

            foreach ($newClass->schedule as $newSlot) {
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
