<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\SavesCustomFieldValues;
use App\Http\Controllers\Controller;
use App\Models\CustomField;
use App\Models\Department;
use App\Models\Document;
use App\Models\Employee;
use App\Models\Setting;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class StudentController extends Controller
{
    use SavesCustomFieldValues;
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
            'trashedCount' => Student::onlyTrashed()->count(),
        ]);
    }

    /**
     * Show the form for creating a new student
     */
    public function create()
    {
        return Inertia::render('Admin/Students/Create', [
            'customFields' => CustomField::forEntity('Student')->active()->orderBy('sort_order')->get(),
        ]);
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
            'ssn' => ['nullable', 'string', 'regex:/^\d{3}-?\d{2}-?\d{4}$/'],
            'enrollment_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['active', 'inactive', 'graduated', 'withdrawn', 'suspended'])],
            'photo' => ['nullable', 'image', 'max:2048'], // 2MB max
            'notes' => ['nullable', 'string'],
        ]);

        // Normalize SSN to digits-only
        if (!empty($validated['ssn'])) {
            $validated['ssn'] = preg_replace('/\D/', '', $validated['ssn']);
        }

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

        $this->saveCustomFieldValues($request, 'Student', $student->id);

        return redirect()->route('admin.students.show', $student)
            ->with('success', "Student {$student->student_id} created successfully.");
    }

    /**
     * Display the specified student
     */
    public function show(Student $student)
    {
        $student->load(['guardians', 'user', 'enrollments.classCourse.course', 'enrollments.classCourse.class', 'phoneNumbers']);

        $isAdmin  = auth()->user()->isAdmin();
        $employee = Employee::where('user_id', auth()->id())->first();

        $notesQuery = $student->notes()->with(['employee', 'department'])->latest();
        if (!$isAdmin && $employee) {
            $notesQuery->where('department_id', $employee->department_id);
        }

        $documentsEnabled = Setting::get('feature_documents_enabled', '0') === '1';

        return Inertia::render('Admin/Students/Show', [
            'student'          => $student,
            'customFields'     => CustomField::forEntityWithValues('Student', $student->id),
            'notes'            => $notesQuery->get(),
            'canAddNote'       => $isAdmin || $employee !== null,
            'userDeptId'       => $employee?->department_id,
            'isAdmin'          => $isAdmin,
            // Admins without an employee record must pick a department when adding a note
            'departments'      => ($isAdmin && !$employee) ? Department::orderBy('name')->get() : [],
            'documentsEnabled' => $documentsEnabled,
            'documents'        => $documentsEnabled
                ? Document::with('uploader')
                    ->where('entity_type', 'Student')
                    ->where('entity_id', $student->id)
                    ->latest()
                    ->get()
                : [],
        ]);
    }

    /**
     * Show the form for editing the specified student
     */
    public function edit(Student $student)
    {
        return Inertia::render('Admin/Students/Edit', [
            'student' => $student,
            'customFields' => CustomField::forEntity('Student')->orderBy('sort_order')->get(),
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
            'ssn' => ['nullable', 'string', 'regex:/^\d{3}-?\d{2}-?\d{4}$/'],
            'enrollment_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['active', 'inactive', 'graduated', 'withdrawn', 'suspended'])],
            'photo' => ['nullable', 'image', 'max:2048'],
            'notes' => ['nullable', 'string'],
        ]);

        // Normalize SSN to digits-only; if blank, remove from update so existing value is preserved
        if (!empty($validated['ssn'])) {
            $validated['ssn'] = preg_replace('/\D/', '', $validated['ssn']);
        } else {
            unset($validated['ssn']);
        }

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

        $this->saveCustomFieldValues($request, 'Student', $student->id);

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

    /**
     * Display soft-deleted students
     */
    public function trashed()
    {
        $students = Student::onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Students/Trashed', [
            'students' => $students,
        ]);
    }

    /**
     * Restore a soft-deleted student
     */
    public function restore(Student $student)
    {
        $student->restore();

        return redirect()->route('admin.students.trashed')
            ->with('success', "Student {$student->student_id} restored successfully.");
    }

    /**
     * Permanently delete a student
     */
    public function forceDelete(Student $student)
    {
        if ($student->photo) {
            Storage::disk('public')->delete($student->photo);
        }
        $student->forceDelete();

        return redirect()->route('admin.students.trashed')
            ->with('success', 'Student permanently deleted.');
    }
}
