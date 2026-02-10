<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guardian;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class GuardianController extends Controller
{
    /**
     * Display a listing of guardians
     */
    public function index(Request $request)
    {
        $query = Guardian::with(['students']);

        // Search by name, email, or phone
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $guardians = $query->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Guardians/Index', [
            'guardians' => $guardians,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new guardian
     */
    public function create()
    {
        $students = Student::active()->get(['id', 'student_id', 'first_name', 'last_name']);

        return Inertia::render('Admin/Guardians/Create', [
            'students' => $students,
        ]);
    }

    /**
     * Store a newly created guardian
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'relationship' => ['required', Rule::in(['mother', 'father', 'guardian', 'grandparent', 'other'])],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'students' => ['nullable', 'array'],
            'students.*.id' => ['exists:students,id'],
            'students.*.is_primary' => ['boolean'],
        ]);

        $guardian = Guardian::create($validated);

        // Attach students if provided
        if (isset($validated['students'])) {
            foreach ($validated['students'] as $studentData) {
                $guardian->students()->attach($studentData['id'], [
                    'is_primary' => $studentData['is_primary'] ?? false,
                ]);
            }
        }

        return redirect()->route('admin.guardians.show', $guardian)
            ->with('success', 'Guardian created successfully.');
    }

    /**
     * Display the specified guardian
     */
    public function show(Guardian $guardian)
    {
        $guardian->load(['students', 'user']);

        return Inertia::render('Admin/Guardians/Show', [
            'guardian' => $guardian,
        ]);
    }

    /**
     * Show the form for editing the specified guardian
     */
    public function edit(Guardian $guardian)
    {
        $guardian->load(['students']);
        $allStudents = Student::active()->get(['id', 'student_id', 'first_name', 'last_name']);

        return Inertia::render('Admin/Guardians/Edit', [
            'guardian' => $guardian,
            'students' => $allStudents,
        ]);
    }

    /**
     * Update the specified guardian
     */
    public function update(Request $request, Guardian $guardian)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'relationship' => ['required', Rule::in(['mother', 'father', 'guardian', 'grandparent', 'other'])],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'students' => ['nullable', 'array'],
            'students.*.id' => ['exists:students,id'],
            'students.*.is_primary' => ['boolean'],
        ]);

        $guardian->update($validated);

        // Sync students if provided
        if (isset($validated['students'])) {
            $syncData = [];
            foreach ($validated['students'] as $studentData) {
                $syncData[$studentData['id']] = [
                    'is_primary' => $studentData['is_primary'] ?? false,
                ];
            }
            $guardian->students()->sync($syncData);
        }

        return redirect()->route('admin.guardians.show', $guardian)
            ->with('success', 'Guardian updated successfully.');
    }

    /**
     * Remove the specified guardian
     */
    public function destroy(Guardian $guardian)
    {
        $guardianName = $guardian->full_name;
        $guardian->delete();

        return redirect()->route('admin.guardians.index')
            ->with('success', "Guardian {$guardianName} deleted successfully.");
    }
}
