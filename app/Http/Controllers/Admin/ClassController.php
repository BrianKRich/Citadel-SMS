<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClassController extends Controller
{
    public function index(Request $request)
    {
        $classes = ClassModel::query()
            ->with(['academicYear'])
            ->when($request->academic_year_id, function ($query, $yearId) {
                $query->where('academic_year_id', $yearId);
            })
            ->when($request->status, function ($query, $status) {
                $query->status($status);
            })
            ->when($request->search, function ($query, $search) {
                $query->search($search);
            })
            ->paginate(10)
            ->withQueryString();

        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();

        return Inertia::render('Admin/Classes/Index', [
            'classes'       => $classes,
            'academicYears' => $academicYears,
            'filters'       => $request->only(['search', 'academic_year_id', 'status']),
        ]);
    }

    public function create()
    {
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();

        return Inertia::render('Admin/Classes/Create', [
            'academicYears' => $academicYears,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'name'             => ['nullable', 'string', 'max:255'],
            'class_number'     => ['required', 'string', 'max:255'],
            'ngb_number'       => ['required', 'string', 'max:255', 'unique:classes,ngb_number'],
            'status'           => ['required', 'in:forming,active,completed'],
            'start_date'       => ['nullable', 'date'],
            'end_date'         => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $class = ClassModel::create($validated);

        return redirect()->route('admin.classes.show', $class)
            ->with('success', "Class {$class->class_number} created successfully.");
    }

    public function show(ClassModel $class)
    {
        $class->load('academicYear');

        return Inertia::render('Admin/Classes/Show', [
            'class' => $class,
        ]);
    }

    public function edit(ClassModel $class)
    {
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();

        return Inertia::render('Admin/Classes/Edit', [
            'class'         => $class->load('academicYear'),
            'academicYears' => $academicYears,
        ]);
    }

    public function update(Request $request, ClassModel $class)
    {
        $validated = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'name'             => ['nullable', 'string', 'max:255'],
            'class_number'     => ['required', 'string', 'max:255'],
            'ngb_number'       => ['required', 'string', 'max:255', 'unique:classes,ngb_number,' . $class->id],
            'status'           => ['required', 'in:forming,active,completed'],
            'start_date'       => ['nullable', 'date'],
            'end_date'         => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $class->update($validated);

        return redirect()->route('admin.classes.show', $class)
            ->with('success', 'Class updated successfully.');
    }

    public function destroy(ClassModel $class)
    {
        $hasEnrollments = $class->classCourses()
            ->whereHas('enrollments')
            ->exists();

        if ($hasEnrollments) {
            return back()->withErrors([
                'error' => 'Cannot delete class with existing enrollments. Please remove all enrollments first.',
            ]);
        }

        $class->delete();

        return redirect()->route('admin.classes.index')
            ->with('success', 'Class deleted successfully.');
    }

}

