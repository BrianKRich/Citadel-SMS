<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class DepartmentController extends Controller
{
    private function requireEnabled(): void
    {
        abort_if(Setting::get('feature_academy_setup_enabled', '0') !== '1', 403);
    }

    public function index(Request $request)
    {
        $this->requireEnabled();
        abort_unless(auth()->user()->isAdmin(), 403);

        $query = Department::withCount('roles', 'employees')->orderBy('name');

        if ($request->filled('search')) {
            $query->where('name', 'ilike', '%' . $request->search . '%');
        }

        $departments = $query->paginate(15)->withQueryString();

        return Inertia::render('Admin/Departments/Index', [
            'departments' => $departments,
            'filters'     => $request->only(['search']),
        ]);
    }

    public function create()
    {
        $this->requireEnabled();
        abort_unless(auth()->user()->isAdmin(), 403);

        return Inertia::render('Admin/Departments/Create');
    }

    public function store(Request $request)
    {
        $this->requireEnabled();
        abort_unless(auth()->user()->isAdmin(), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:departments,name'],
        ]);

        Department::create($validated);

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department created successfully.');
    }

    public function edit(Department $department)
    {
        $this->requireEnabled();
        abort_unless(auth()->user()->isAdmin(), 403);

        return Inertia::render('Admin/Departments/Edit', [
            'department' => $department,
        ]);
    }

    public function update(Request $request, Department $department)
    {
        $this->requireEnabled();
        abort_unless(auth()->user()->isAdmin(), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('departments', 'name')->ignore($department->id)],
        ]);

        $department->update($validated);

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        $this->requireEnabled();
        abort_unless(auth()->user()->isAdmin(), 403);

        if ($department->employees()->exists()) {
            return back()->with('error', 'Cannot delete a department that has employees assigned to it.');
        }

        $department->delete();

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department deleted.');
    }
}
