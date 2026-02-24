<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EducationalInstitution;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EducationalInstitutionController extends Controller
{
    public function index(Request $request)
    {
        $institutions = EducationalInstitution::query()
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%");
            })
            ->when($request->type, function ($query, $type) {
                $query->type($type);
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Institutions/Index', [
            'institutions' => $institutions,
            'filters'      => $request->only(['search', 'type']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Institutions/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'type'           => ['required', 'in:technical_college,university'],
            'address'        => ['nullable', 'string'],
            'phone'          => ['nullable', 'string', 'max:50'],
            'contact_person' => ['nullable', 'string', 'max:255'],
        ]);

        $institution = EducationalInstitution::create($validated);

        return redirect()->route('admin.institutions.index')
            ->with('success', "{$institution->name} created successfully.");
    }

    public function edit(EducationalInstitution $institution)
    {
        return Inertia::render('Admin/Institutions/Edit', [
            'institution' => $institution,
        ]);
    }

    public function update(Request $request, EducationalInstitution $institution)
    {
        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'type'           => ['required', 'in:technical_college,university'],
            'address'        => ['nullable', 'string'],
            'phone'          => ['nullable', 'string', 'max:50'],
            'contact_person' => ['nullable', 'string', 'max:255'],
        ]);

        $institution->update($validated);

        return redirect()->route('admin.institutions.index')
            ->with('success', "{$institution->name} updated successfully.");
    }

    public function destroy(EducationalInstitution $institution)
    {
        if ($institution->cohortCourses()->count() > 0) {
            return back()->withErrors([
                'error' => 'Cannot delete an institution that is assigned to course sections.',
            ]);
        }

        $name = $institution->name;
        $institution->delete();

        return redirect()->route('admin.institutions.index')
            ->with('success', "{$name} deleted successfully.");
    }
}
