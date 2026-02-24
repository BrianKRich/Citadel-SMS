<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Student;
use App\Models\StudentNote;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentNoteController extends Controller
{
    private function resolveEmployee(): ?Employee
    {
        return Employee::where('user_id', auth()->id())->first();
    }

    /**
     * Standalone notes index page
     */
    public function index(Request $request)
    {
        $isAdmin  = auth()->user()->isAdmin();
        $employee = $this->resolveEmployee();

        $hasFilter = $request->anyFilled(['search', 'department_id', 'date_from', 'date_to']);

        $notes = null;

        if ($hasFilter) {
            $query = StudentNote::with(['student', 'employee', 'department'])->latest();

            if (!$isAdmin && $employee) {
                $query->where('department_id', $employee->department_id);
            } elseif (!$isAdmin && !$employee) {
                abort(403);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('student', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhereRaw("(first_name || ' ' || last_name) like ?", ["%{$search}%"])
                        ->orWhere('student_id', 'like', "%{$search}%");
                });
            }

            if ($isAdmin && $request->filled('department_id')) {
                $query->where('department_id', $request->department_id);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $notes = $query->paginate(20)->withQueryString();
        }

        return Inertia::render('Admin/StudentNotes/Index', [
            'notes'       => $notes,
            'filters'     => $request->only(['search', 'department_id', 'date_from', 'date_to']),
            'departments' => $isAdmin ? Department::orderBy('name')->get() : [],
            'isAdmin'     => $isAdmin,
            'searched'    => $hasFilter,
        ]);
    }

    /**
     * Store a new note for a student
     */
    public function store(Request $request, Student $student)
    {
        $isAdmin  = auth()->user()->isAdmin();
        $employee = $this->resolveEmployee();

        if (!$isAdmin && !$employee) {
            abort(403, 'You must be associated with an employee record to add notes.');
        }

        // Admin with no employee record: require department_id from the form
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'body'  => ['required', 'string'],
        ];
        if ($isAdmin && !$employee) {
            $rules['department_id'] = ['required', 'exists:departments,id'];
        }

        $validated = $request->validate($rules);

        $student->notes()->create([
            'employee_id'   => $employee?->id,
            'department_id' => $employee ? $employee->department_id : $validated['department_id'],
            'title'         => $validated['title'],
            'body'          => $validated['body'],
        ]);

        return redirect()->back()->with('success', 'Note added successfully.');
    }

    /**
     * Update an existing note
     */
    public function update(Request $request, Student $student, StudentNote $note)
    {
        $isAdmin  = auth()->user()->isAdmin();
        $employee = $this->resolveEmployee();

        if (!$isAdmin && (!$employee || $note->department_id !== $employee->department_id)) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body'  => ['required', 'string'],
        ]);

        $note->update($validated);

        return redirect()->back()->with('success', 'Note updated successfully.');
    }

    /**
     * Delete a note
     */
    public function destroy(Student $student, StudentNote $note)
    {
        $isAdmin  = auth()->user()->isAdmin();
        $employee = $this->resolveEmployee();

        if (!$isAdmin && (!$employee || $note->department_id !== $employee->department_id)) {
            abort(403);
        }

        $note->delete();

        return redirect()->back()->with('success', 'Note deleted successfully.');
    }
}
