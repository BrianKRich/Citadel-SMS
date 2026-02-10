<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class EmployeeController extends Controller
{
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
        ]);
    }

    public function create()
    {
        $departments = Department::with('roles')->orderBy('name')->get();

        return Inertia::render('Admin/Employees/Create', [
            'departments' => $departments,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:employees,email'],
            'department_id' => ['required', 'exists:departments,id'],
            'role_id' => ['required', 'exists:employee_roles,id'],
            'phone_area_code' => ['nullable', 'string', 'digits:3'],
            'phone' => ['nullable', 'string', 'digits:7'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'hire_date' => ['required', 'date'],
            'qualifications' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'status' => ['required', Rule::in(['active', 'inactive', 'on_leave'])],
            'create_user_account' => ['boolean'],
            'password' => ['required_if:create_user_account,true', 'nullable', Rules\Password::defaults()],
        ]);

        $phone = $validated['phone'] ?? null;
        $phoneAreaCode = $validated['phone_area_code'] ?? null;
        unset($validated['phone'], $validated['phone_area_code']);

        $userId = null;
        if ($request->boolean('create_user_account') && !empty($validated['password'])) {
            $user = User::create([
                'name' => "{$validated['first_name']} {$validated['last_name']}",
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'employee',
            ]);
            $userId = $user->id;
        }

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('employees/photos', 'public');
        }

        $validated['user_id'] = $userId;
        unset($validated['create_user_account'], $validated['password']);

        $employee = Employee::create($validated);

        if ($phone) {
            $employee->phoneNumbers()->create(['area_code' => $phoneAreaCode, 'number' => $phone, 'type' => 'primary', 'is_primary' => true]);
        }

        return redirect()->route('admin.employees.show', $employee)
            ->with('success', "Employee {$employee->employee_id} created successfully.");
    }

    public function show(Employee $employee)
    {
        $employee->load(['user', 'department', 'role', 'classes.course', 'phoneNumbers']);

        return Inertia::render('Admin/Employees/Show', [
            'employee' => $employee,
        ]);
    }

    public function edit(Employee $employee)
    {
        $departments = Department::with('roles')->orderBy('name')->get();

        return Inertia::render('Admin/Employees/Edit', [
            'employee' => $employee->load(['user', 'department', 'role']),
            'departments' => $departments,
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

        $employee->update($validated);

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

        return redirect()->route('admin.employees.show', $employee)
            ->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        $employeeId = $employee->employee_id;
        $employee->delete();

        return redirect()->route('admin.employees.index')
            ->with('success', "Employee {$employeeId} deleted successfully.");
    }
}
