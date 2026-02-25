<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\EmployeeRole;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class EmployeeRoleController extends Controller
{
    private function requireEnabled(): void
    {
        abort_if(Setting::get('feature_academy_setup_enabled', '0') !== '1', 403);
    }

    public function index(Request $request)
    {
        $this->requireEnabled();
        abort_unless(auth()->user()->isAdmin(), 403);

        $query = EmployeeRole::with('department')->withCount('employees')->orderBy('name');

        if ($request->filled('search')) {
            $query->where('name', 'ilike', '%' . $request->search . '%');
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $roles       = $query->paginate(15)->withQueryString();
        $departments = Department::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/EmployeeRoles/Index', [
            'roles'       => $roles,
            'departments' => $departments,
            'filters'     => $request->only(['search', 'department_id']),
        ]);
    }

    public function create()
    {
        $this->requireEnabled();
        abort_unless(auth()->user()->isAdmin(), 403);

        $departments = Department::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/EmployeeRoles/Create', [
            'departments' => $departments,
        ]);
    }

    public function store(Request $request)
    {
        $this->requireEnabled();
        abort_unless(auth()->user()->isAdmin(), 403);

        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'name'          => [
                'required', 'string', 'max:255',
                Rule::unique('employee_roles', 'name')->where('department_id', $request->department_id),
            ],
        ]);

        EmployeeRole::create($validated);

        return redirect()->route('admin.employee-roles.index')
            ->with('success', 'Employee role created successfully.');
    }

    public function edit(EmployeeRole $employeeRole)
    {
        $this->requireEnabled();
        abort_unless(auth()->user()->isAdmin(), 403);

        $departments = Department::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/EmployeeRoles/Edit', [
            'role'        => $employeeRole->load('department'),
            'departments' => $departments,
        ]);
    }

    public function update(Request $request, EmployeeRole $employeeRole)
    {
        $this->requireEnabled();
        abort_unless(auth()->user()->isAdmin(), 403);

        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'name'          => [
                'required', 'string', 'max:255',
                Rule::unique('employee_roles', 'name')
                    ->where('department_id', $request->department_id)
                    ->ignore($employeeRole->id),
            ],
        ]);

        $employeeRole->update($validated);

        return redirect()->route('admin.employee-roles.index')
            ->with('success', 'Employee role updated successfully.');
    }

    public function destroy(EmployeeRole $employeeRole)
    {
        $this->requireEnabled();
        abort_unless(auth()->user()->isAdmin(), 403);

        if ($employeeRole->employees()->exists()) {
            return back()->with('error', 'Cannot delete a role that has employees assigned to it.');
        }

        $employeeRole->delete();

        return redirect()->route('admin.employee-roles.index')
            ->with('success', 'Employee role deleted.');
    }
}
