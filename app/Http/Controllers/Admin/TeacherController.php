<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class TeacherController extends Controller
{
    /**
     * Display a listing of teachers
     */
    public function index(Request $request)
    {
        $query = Teacher::with(['user']);

        // Search by name, teacher ID, or email
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by department
        if ($request->filled('department')) {
            $query->department($request->department);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $teachers = $query->latest()
            ->paginate(10)
            ->withQueryString();

        // Get unique departments for filters
        $departments = Teacher::distinct()->pluck('department')->filter();

        return Inertia::render('Admin/Teachers/Index', [
            'teachers' => $teachers,
            'filters' => $request->only(['search', 'department', 'status']),
            'departments' => $departments,
        ]);
    }

    /**
     * Show the form for creating a new teacher
     */
    public function create()
    {
        return Inertia::render('Admin/Teachers/Create');
    }

    /**
     * Store a newly created teacher
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:teachers,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'hire_date' => ['required', 'date'],
            'department' => ['nullable', 'string', 'max:255'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'qualifications' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'status' => ['required', Rule::in(['active', 'inactive', 'on_leave'])],
            'create_user_account' => ['boolean'],
            'password' => ['required_if:create_user_account,true', 'nullable', Rules\Password::defaults()],
        ]);

        // Create user account if requested
        $userId = null;
        if ($request->boolean('create_user_account') && !empty($validated['password'])) {
            $user = User::create([
                'name' => "{$validated['first_name']} {$validated['last_name']}",
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'teacher',
            ]);
            $userId = $user->id;
        }

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('teachers/photos', 'public');
        }

        $validated['user_id'] = $userId;
        unset($validated['create_user_account'], $validated['password']);

        $teacher = Teacher::create($validated);

        return redirect()->route('admin.teachers.show', $teacher)
            ->with('success', "Teacher {$teacher->teacher_id} created successfully.");
    }

    /**
     * Display the specified teacher
     */
    public function show(Teacher $teacher)
    {
        $teacher->load(['user', 'classes.course']);

        return Inertia::render('Admin/Teachers/Show', [
            'teacher' => $teacher,
        ]);
    }

    /**
     * Show the form for editing the specified teacher
     */
    public function edit(Teacher $teacher)
    {
        return Inertia::render('Admin/Teachers/Edit', [
            'teacher' => $teacher->load('user'),
        ]);
    }

    /**
     * Update the specified teacher
     */
    public function update(Request $request, Teacher $teacher)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('teachers')->ignore($teacher->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'hire_date' => ['required', 'date'],
            'department' => ['nullable', 'string', 'max:255'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'qualifications' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'status' => ['required', Rule::in(['active', 'inactive', 'on_leave'])],
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($teacher->photo) {
                Storage::disk('public')->delete($teacher->photo);
            }
            $validated['photo'] = $request->file('photo')->store('teachers/photos', 'public');
        }

        $teacher->update($validated);

        // Update user if exists
        if ($teacher->user) {
            $teacher->user->update([
                'name' => "{$validated['first_name']} {$validated['last_name']}",
                'email' => $validated['email'],
            ]);
        }

        return redirect()->route('admin.teachers.show', $teacher)
            ->with('success', 'Teacher updated successfully.');
    }

    /**
     * Remove the specified teacher (soft delete)
     */
    public function destroy(Teacher $teacher)
    {
        $teacherId = $teacher->teacher_id;
        $teacher->delete();

        return redirect()->route('admin.teachers.index')
            ->with('success', "Teacher {$teacherId} deleted successfully.");
    }
}
