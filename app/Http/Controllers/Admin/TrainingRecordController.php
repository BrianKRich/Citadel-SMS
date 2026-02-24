<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Employee;
use App\Models\Setting;
use App\Models\TrainingCourse;
use App\Models\TrainingRecord;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TrainingRecordController extends Controller
{
    private function requireStaffTrainingEnabled(): void
    {
        abort_if(Setting::get('feature_staff_training_enabled', '0') !== '1', 403);
    }

    public function index(Request $request)
    {
        $this->requireStaffTrainingEnabled();
        abort_unless(auth()->user()->isAdmin(), 403);

        $query = TrainingRecord::with(['employee', 'trainingCourse']);

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('training_course_id')) {
            $query->where('training_course_id', $request->training_course_id);
        }

        if ($request->filled('date_from')) {
            $query->where('date_completed', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date_completed', '<=', $request->date_to);
        }

        $records = $query->orderBy('date_completed', 'desc')->paginate(15)->withQueryString();

        return Inertia::render('Admin/Training/Records/Index', [
            'records'  => $records,
            'filters'  => $request->only(['search', 'employee_id', 'training_course_id', 'date_from', 'date_to']),
            'courses'  => TrainingCourse::active()->orderBy('name')->get(['id', 'name']),
            'employees' => Employee::orderBy('last_name')->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'employee_id']),
        ]);
    }

    public function create(Request $request)
    {
        $this->requireStaffTrainingEnabled();
        abort_unless(auth()->user()->isAdmin(), 403);

        $preselected = $request->integer('employee_id') ?: null;

        return Inertia::render('Admin/Training/Records/Create', [
            'courses'             => TrainingCourse::active()->orderBy('name')->get(['id', 'name', 'trainer']),
            'employees'           => Employee::active()->orderBy('last_name')->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'employee_id']),
            'preselectedEmployee' => $preselected ? [$preselected] : [],
        ]);
    }

    public function store(Request $request)
    {
        $this->requireStaffTrainingEnabled();
        abort_unless(auth()->user()->isAdmin(), 403);

        $validated = $request->validate([
            'employee_ids'       => ['required', 'array', 'min:1'],
            'employee_ids.*'     => ['required', 'exists:employees,id'],
            'training_course_id' => ['required', 'exists:training_courses,id'],
            'date_completed'     => ['required', 'date'],
            'trainer_name'       => ['required', 'string', 'max:255'],
            'notes'              => ['nullable', 'string'],
        ]);

        $shared = [
            'training_course_id' => $validated['training_course_id'],
            'date_completed'     => $validated['date_completed'],
            'trainer_name'       => $validated['trainer_name'],
            'notes'              => $validated['notes'] ?? null,
        ];

        $count = count($validated['employee_ids']);
        $lastRecord = null;
        foreach ($validated['employee_ids'] as $employeeId) {
            $lastRecord = TrainingRecord::create(['employee_id' => $employeeId] + $shared);
        }

        if ($count === 1) {
            return redirect()->route('admin.training-records.show', $lastRecord)
                ->with('success', 'Training completion logged successfully.');
        }

        return redirect()->route('admin.training-records.index')
            ->with('success', "{$count} training completions logged successfully.");
    }

    public function show(TrainingRecord $trainingRecord)
    {
        $this->requireStaffTrainingEnabled();
        abort_unless(auth()->user()->isAdmin(), 403);

        $trainingRecord->load(['employee', 'trainingCourse']);

        $documentsEnabled = Setting::get('feature_documents_enabled', '0') === '1';

        return Inertia::render('Admin/Training/Records/Show', [
            'record'           => $trainingRecord,
            'documentsEnabled' => $documentsEnabled,
            'documents'        => $documentsEnabled
                ? Document::with('uploader')
                    ->where('entity_type', 'TrainingRecord')
                    ->where('entity_id', $trainingRecord->id)
                    ->latest()
                    ->get()
                : [],
        ]);
    }

    public function edit(TrainingRecord $trainingRecord)
    {
        $this->requireStaffTrainingEnabled();
        abort_unless(auth()->user()->isAdmin(), 403);

        $trainingRecord->load(['employee', 'trainingCourse']);

        return Inertia::render('Admin/Training/Records/Edit', [
            'record'    => $trainingRecord,
            'courses'   => TrainingCourse::active()->orderBy('name')->get(['id', 'name', 'trainer']),
            'employees' => Employee::active()->orderBy('last_name')->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'employee_id']),
        ]);
    }

    public function update(Request $request, TrainingRecord $trainingRecord)
    {
        $this->requireStaffTrainingEnabled();
        abort_unless(auth()->user()->isAdmin(), 403);

        $validated = $request->validate([
            'employee_id'        => ['required', 'exists:employees,id'],
            'training_course_id' => ['required', 'exists:training_courses,id'],
            'date_completed'     => ['required', 'date'],
            'trainer_name'       => ['required', 'string', 'max:255'],
            'notes'              => ['nullable', 'string'],
        ]);

        $trainingRecord->update($validated);

        return redirect()->route('admin.training-records.show', $trainingRecord)
            ->with('success', 'Training record updated successfully.');
    }

    public function destroy(TrainingRecord $trainingRecord)
    {
        $this->requireStaffTrainingEnabled();
        abort_unless(auth()->user()->isAdmin(), 403);

        $trainingRecord->delete();

        return redirect()->route('admin.training-records.index')
            ->with('success', 'Training record deleted.');
    }
}
