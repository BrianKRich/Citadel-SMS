<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\ClassModel;
use App\Models\Enrollment;
use App\Models\Setting;
use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AttendanceController extends Controller
{
    private function requireAttendanceEnabled(): void
    {
        abort_if(Setting::get('feature_attendance_enabled', '0') !== '1', 403);
    }

    public function index(Request $request)
    {
        $this->requireAttendanceEnabled();

        $query = ClassModel::with(['course', 'term', 'employee'])
            ->withCount(['enrollments']);

        if ($search = $request->input('search')) {
            $query->search($search);
        }

        $classes = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Admin/Attendance/Index', [
            'classes' => $classes,
            'filters' => $request->only(['search']),
        ]);
    }

    public function take(Request $request, ClassModel $classModel)
    {
        $this->requireAttendanceEnabled();

        $date = $request->input('date', today()->toDateString());

        $enrollments = Enrollment::with('student')
            ->where('class_id', $classModel->id)
            ->where('status', 'enrolled')
            ->get();

        $existingRecords = AttendanceRecord::where('class_id', $classModel->id)
            ->whereDate('date', $date)
            ->get()
            ->keyBy('student_id');

        $classModel->load(['course', 'term']);

        return Inertia::render('Admin/Attendance/Take', [
            'classModel'      => $classModel,
            'enrollments'     => $enrollments,
            'existingRecords' => $existingRecords,
            'date'            => $date,
        ]);
    }

    public function store(Request $request)
    {
        $this->requireAttendanceEnabled();

        $validated = $request->validate([
            'class_id'             => ['required', 'exists:classes,id'],
            'date'                 => ['required', 'date'],
            'records'              => ['required', 'array', 'min:1'],
            'records.*.student_id' => ['required', 'exists:students,id'],
            'records.*.status'     => ['required', 'in:present,absent,late,excused'],
            'records.*.notes'      => ['nullable', 'string'],
        ]);

        foreach ($validated['records'] as $record) {
            AttendanceRecord::updateOrCreate(
                [
                    'student_id' => $record['student_id'],
                    'class_id'   => $validated['class_id'],
                    'date'       => $validated['date'],
                ],
                [
                    'status'    => $record['status'],
                    'notes'     => $record['notes'] ?? null,
                    'marked_by' => $request->user()->id,
                ]
            );
        }

        return back()->with('success', 'Attendance saved successfully.');
    }

    public function studentHistory(Request $request, Student $student)
    {
        $this->requireAttendanceEnabled();

        $records = AttendanceRecord::with('classModel.course')
            ->where('student_id', $student->id)
            ->orderByDesc('date')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Attendance/StudentHistory', [
            'student' => $student,
            'records' => $records,
        ]);
    }

    public function classSummary(Request $request, ClassModel $classModel)
    {
        $this->requireAttendanceEnabled();

        $classModel->load(['course', 'term']);

        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');

        $enrollments = Enrollment::with('student')
            ->where('class_id', $classModel->id)
            ->where('status', 'enrolled')
            ->get();

        $summaries = $enrollments->map(function ($enrollment) use ($classModel, $dateFrom, $dateTo) {
            $query = AttendanceRecord::where('class_id', $classModel->id)
                ->where('student_id', $enrollment->student_id);

            if ($dateFrom) {
                $query->whereDate('date', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->whereDate('date', '<=', $dateTo);
            }

            $records = $query->get();
            $total   = $records->count();
            $present = $records->where('status', 'present')->count();
            $absent  = $records->where('status', 'absent')->count();
            $late    = $records->where('status', 'late')->count();
            $excused = $records->where('status', 'excused')->count();

            return [
                'student'           => $enrollment->student,
                'total'             => $total,
                'present'           => $present,
                'absent'            => $absent,
                'late'              => $late,
                'excused'           => $excused,
                'attendance_rate'   => $total > 0 ? round(($present + $late) / $total * 100, 1) : null,
            ];
        });

        return Inertia::render('Admin/Attendance/ClassSummary', [
            'classModel' => $classModel,
            'summaries'  => $summaries,
            'filters'    => $request->only(['date_from', 'date_to']),
        ]);
    }
}
