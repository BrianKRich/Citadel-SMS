<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AcademicYearController extends Controller
{
    public function index()
    {
        $academicYears = AcademicYear::withCount('classes')
            ->latest()
            ->paginate(10);

        return Inertia::render('Admin/AcademicYears/Index', [
            'academic_years' => $academicYears,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/AcademicYears/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after:start_date'],
            'is_current' => ['boolean'],
        ]);

        $academicYear = AcademicYear::create([
            'name'       => $validated['name'],
            'start_date' => $validated['start_date'],
            'end_date'   => $validated['end_date'],
            'is_current' => $validated['is_current'] ?? false,
        ]);

        if ($validated['is_current'] ?? false) {
            $academicYear->setCurrent();
        }

        return redirect()->route('admin.academic-years.show', $academicYear)
            ->with('success', "Academic Year {$academicYear->name} created successfully.");
    }

    public function show(AcademicYear $academicYear)
    {
        $academicYear->load(['classes.cohorts']);

        return Inertia::render('Admin/AcademicYears/Show', [
            'academicYear' => $academicYear,
        ]);
    }

    public function edit(AcademicYear $academicYear)
    {
        return Inertia::render('Admin/AcademicYears/Edit', [
            'academicYear' => $academicYear,
        ]);
    }

    public function update(Request $request, AcademicYear $academicYear)
    {
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after:start_date'],
            'is_current' => ['boolean'],
        ]);

        $academicYear->update($validated);

        if ($validated['is_current'] ?? false) {
            $academicYear->setCurrent();
        }

        return redirect()->route('admin.academic-years.show', $academicYear)
            ->with('success', 'Academic Year updated successfully.');
    }

    public function destroy(AcademicYear $academicYear)
    {
        $classCount = $academicYear->classes()->count();
        if ($classCount > 0) {
            return back()->with('error', "Cannot delete \"{$academicYear->name}\" — {$classCount} " . str('class')->plural($classCount) . " are assigned to it. Delete those classes first.");
        }

        $name = $academicYear->name;
        $academicYear->delete();

        return redirect()->route('admin.academic-years.index')
            ->with('success', "Academic Year {$name} deleted successfully.");
    }

    public function setCurrent(AcademicYear $academicYear)
    {
        $academicYear->setCurrent();

        return back()->with('success', "{$academicYear->name} set as current academic year.");
    }
}
