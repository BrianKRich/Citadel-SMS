<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class StudentController extends Controller
{
    /**
     * Display a listing of students with search and filtering
     */
    public function index(Request $request)
    {
        $query = Student::with(['guardians', 'user', 'phoneNumbers']);

        // Search by name, student ID, or email
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->status($request->status);
        }

        // Get paginated results
        $students = $query->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Students/Index', [
            'students' => $students,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new student
     */
    public function create()
    {
        return Inertia::render('Admin/Students/Create');
    }

    /**
     * Store a newly created student
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'gender' => ['required', Rule::in(['male', 'female', 'other'])],
            'email' => ['nullable', 'email', 'unique:students,email'],
            'phone_area_code' => ['nullable', 'string', 'digits:3'],
            'phone' => ['nullable', 'string', 'digits:7'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:255'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_phone_area_code' => ['nullable', 'string', 'digits:3'],
            'emergency_contact_phone' => ['nullable', 'string', 'digits:7'],
            'enrollment_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['active', 'inactive', 'graduated', 'withdrawn', 'suspended'])],
            'photo' => ['nullable', 'image', 'max:2048'], // 2MB max
            'notes' => ['nullable', 'string'],
        ]);

        $phone = $validated['phone'] ?? null;
        $phoneAreaCode = $validated['phone_area_code'] ?? null;
        $emergencyPhone = $validated['emergency_contact_phone'] ?? null;
        $emergencyAreaCode = $validated['emergency_phone_area_code'] ?? null;
        unset($validated['phone'], $validated['phone_area_code'], $validated['emergency_contact_phone'], $validated['emergency_phone_area_code']);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('students/photos', 'public');
        }

        $student = Student::create($validated);

        if ($phone) {
            $student->phoneNumbers()->create(['area_code' => $phoneAreaCode, 'number' => $phone, 'type' => 'primary', 'is_primary' => true]);
        }
        if ($emergencyPhone) {
            $student->phoneNumbers()->create(['area_code' => $emergencyAreaCode, 'number' => $emergencyPhone, 'type' => 'emergency']);
        }

        return redirect()->route('admin.students.show', $student)
            ->with('success', "Student {$student->student_id} created successfully.");
    }

    /**
     * Display the specified student
     */
    public function show(Student $student)
    {
        $student->load(['guardians', 'user', 'enrollments.class.course', 'phoneNumbers']);

        return Inertia::render('Admin/Students/Show', [
            'student' => $student,
        ]);
    }

    /**
     * Show the form for editing the specified student
     */
    public function edit(Student $student)
    {
        return Inertia::render('Admin/Students/Edit', [
            'student' => $student,
        ]);
    }

    /**
     * Update the specified student
     */
    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'gender' => ['required', Rule::in(['male', 'female', 'other'])],
            'email' => ['nullable', 'email', Rule::unique('students')->ignore($student->id)],
            'phone_area_code' => ['nullable', 'string', 'digits:3'],
            'phone' => ['nullable', 'string', 'digits:7'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:255'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_phone_area_code' => ['nullable', 'string', 'digits:3'],
            'emergency_contact_phone' => ['nullable', 'string', 'digits:7'],
            'enrollment_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['active', 'inactive', 'graduated', 'withdrawn', 'suspended'])],
            'photo' => ['nullable', 'image', 'max:2048'],
            'notes' => ['nullable', 'string'],
        ]);

        $phone = $validated['phone'] ?? null;
        $phoneAreaCode = $validated['phone_area_code'] ?? null;
        $emergencyPhone = $validated['emergency_contact_phone'] ?? null;
        $emergencyAreaCode = $validated['emergency_phone_area_code'] ?? null;
        unset($validated['phone'], $validated['phone_area_code'], $validated['emergency_contact_phone'], $validated['emergency_phone_area_code']);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($student->photo) {
                Storage::disk('public')->delete($student->photo);
            }
            $validated['photo'] = $request->file('photo')->store('students/photos', 'public');
        }

        $student->update($validated);

        $student->phoneNumbers()->delete();
        if ($phone) {
            $student->phoneNumbers()->create(['area_code' => $phoneAreaCode, 'number' => $phone, 'type' => 'primary', 'is_primary' => true]);
        }
        if ($emergencyPhone) {
            $student->phoneNumbers()->create(['area_code' => $emergencyAreaCode, 'number' => $emergencyPhone, 'type' => 'emergency']);
        }

        return redirect()->route('admin.students.show', $student)
            ->with('success', 'Student updated successfully.');
    }

    /**
     * Remove the specified student (soft delete)
     */
    public function destroy(Student $student)
    {
        $studentId = $student->student_id;
        $student->delete();

        return redirect()->route('admin.students.index')
            ->with('success', "Student {$studentId} deleted successfully.");
    }
}
