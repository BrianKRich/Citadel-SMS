<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\SavesCustomFieldValues;
use App\Http\Controllers\Controller;
use App\Models\ClassModel;
use App\Models\CustomField;
use App\Models\Department;
use App\Models\Document;
use App\Models\Employee;
use App\Models\EmployeeRole;
use App\Models\Setting;
use App\Models\TrainingRecord;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class EmployeeController extends Controller
{
    use SavesCustomFieldValues;

    public function index(Request $request)
    {
        $query = Employee::with(['user', 'department', 'role', 'phoneNumbers']);

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('department_id')) {
            $query->department($request->department_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $employees = $query->latest()
            ->paginate(10)
            ->withQueryString();

        $departments = Department::orderBy('name')->get();

        return Inertia::render('Admin/Employees/Index', [
            'employees' => $employees,
            'departments' => $departments,
            'filters' => $request->only(['search', 'department_id', 'status']),
            'trashedCount' => Employee::onlyTrashed()->count(),
        ]);
    }

    public function create()
    {
        $departments = Department::with('roles')->orderBy('name')->get();
        $allRoles = EmployeeRole::with('department')->orderBy('name')->get();

        return Inertia::render('Admin/Employees/Create', [
            'departments' => $departments,
            'allRoles' => $allRoles,
            'customFields' => CustomField::forEntity('Employee')->active()->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:employees,email', 'unique:users,email'],
            'department_id' => ['required', 'exists:departments,id'],
            'role_id' => ['required', 'exists:employee_roles,id'],
            'secondary_role_id' => ['nullable', 'exists:employee_roles,id'],
            'phone_area_code' => ['nullable', 'string', 'digits:3'],
            'phone' => ['nullable', 'string', 'digits:7'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'hire_date' => ['required', 'date'],
            'qualifications' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'status' => ['required', Rule::in(['active', 'inactive', 'on_leave'])],
            'password' => ['required', Rules\Password::defaults()],
        ]);

        $phone = $validated['phone'] ?? null;
        $phoneAreaCode = $validated['phone_area_code'] ?? null;
        unset($validated['phone'], $validated['phone_area_code']);

        $user = User::create([
            'name' => "{$validated['first_name']} {$validated['last_name']}",
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'employee',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('employees/photos', 'public');
        }

        $validated['user_id'] = $user->id;
        unset($validated['password']);

        $employee = Employee::create($validated);

        if ($phone) {
            $employee->phoneNumbers()->create(['area_code' => $phoneAreaCode, 'number' => $phone, 'type' => 'primary', 'is_primary' => true]);
        }

        $this->saveCustomFieldValues($request, 'Employee', $employee->id);

        return redirect()->route('admin.employees.show', $employee)
            ->with('success', "Employee {$employee->employee_id} created successfully.");
    }

    public function show(Employee $employee)
    {
        $employee->load([
            'user', 'department', 'role', 'secondaryRole.department', 'phoneNumbers',
            'classCourses' => fn($q) => $q->withCount('enrollments')->with(['course', 'class']),
        ]);

        $documentsEnabled      = Setting::get('feature_documents_enabled', '0') === '1';
        $trainingEnabled       = Setting::get('feature_staff_training_enabled', '0') === '1';

        return Inertia::render('Admin/Employees/Show', [
            'employee'         => $employee,
            'customFields'     => CustomField::forEntityWithValues('Employee', $employee->id),
            'documentsEnabled' => $documentsEnabled,
            'documents'        => $documentsEnabled
                ? Document::with('uploader')
                    ->where('entity_type', 'Employee')
                    ->where('entity_id', $employee->id)
                    ->latest()
                    ->get()
                : [],
            'trainingEnabled'  => $trainingEnabled,
            'trainingRecords'  => $trainingEnabled
                ? TrainingRecord::with('trainingCourse')
                    ->where('employee_id', $employee->id)
                    ->orderBy('date_completed', 'desc')
                    ->limit(10)
                    ->get()
                : [],
        ]);
    }

    public function edit(Employee $employee)
    {
        $departments = Department::with('roles')->orderBy('name')->get();
        $allRoles = EmployeeRole::with('department')->orderBy('name')->get();

        return Inertia::render('Admin/Employees/Edit', [
            'employee' => $employee->load(['user', 'department', 'role', 'secondaryRole', 'phoneNumbers']),
            'departments' => $departments,
            'allRoles' => $allRoles,
            'customFields' => CustomField::forEntity('Employee')->orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('employees')->ignore($employee->id)],
            'department_id' => ['required', 'exists:departments,id'],
            'role_id' => ['required', 'exists:employee_roles,id'],
            'secondary_role_id' => ['nullable', 'exists:employee_roles,id'],
            'phone_area_code' => ['nullable', 'string', 'digits:3'],
            'phone' => ['nullable', 'string', 'digits:7'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'hire_date' => ['required', 'date'],
            'qualifications' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'status' => ['required', Rule::in(['active', 'inactive', 'on_leave'])],
        ]);

        $phone = $validated['phone'] ?? null;
        $phoneAreaCode = $validated['phone_area_code'] ?? null;
        unset($validated['phone'], $validated['phone_area_code']);

        if ($request->hasFile('photo')) {
            if ($employee->photo) {
                Storage::disk('public')->delete($employee->photo);
            }
            $validated['photo'] = $request->file('photo')->store('employees/photos', 'public');
        }

        $roleChanged       = $employee->role_id !== (int) $validated['role_id'];
        $departmentChanged = $employee->department_id !== (int) $validated['department_id'];

        $employee->update($validated);

        if ($roleChanged || $departmentChanged) {
            $employee->classCourses()->update(['employee_id' => null]);
        }

        $employee->phoneNumbers()->delete();
        if ($phone) {
            $employee->phoneNumbers()->create(['area_code' => $phoneAreaCode, 'number' => $phone, 'type' => 'primary', 'is_primary' => true]);
        }

        if ($employee->user) {
            $employee->user->update([
                'name' => "{$validated['first_name']} {$validated['last_name']}",
                'email' => $validated['email'],
            ]);
        }

        $this->saveCustomFieldValues($request, 'Employee', $employee->id);

        return redirect()->route('admin.employees.show', $employee)
            ->with('success', 'Employee updated successfully.');
    }

    public function removeClass(Employee $employee, ClassModel $class)
    {
        abort_unless($class->employee_id === $employee->id, 404);
        $class->update(['employee_id' => null]);

        return redirect()->route('admin.employees.show', $employee)
            ->with('success', "Removed from \"{$class->course->name} — {$class->section_name}\".");
    }

    public function destroy(Employee $employee)
    {
        $employeeId = $employee->employee_id;
        $employee->delete();

        return redirect()->route('admin.employees.index')
            ->with('success', "Employee {$employeeId} deleted successfully.");
    }

    /**
     * Display soft-deleted employees
     */
    public function trashed()
    {
        $employees = Employee::onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Employees/Trashed', [
            'employees' => $employees,
        ]);
    }

    /**
     * Restore a soft-deleted employee
     */
    public function restore(Employee $employee)
    {
        $employee->restore();

        return redirect()->route('admin.employees.trashed')
            ->with('success', "Employee {$employee->employee_id} restored successfully.");
    }

    /**
     * Permanently delete an employee
     */
    public function forceDelete(Employee $employee)
    {
        if ($employee->photo) {
            Storage::disk('public')->delete($employee->photo);
        }
        $employee->forceDelete();

        return redirect()->route('admin.employees.trashed')
            ->with('success', 'Employee permanently deleted.');
    }
}
