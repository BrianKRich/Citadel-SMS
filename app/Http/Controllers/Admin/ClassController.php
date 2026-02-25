<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Cohort;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClassController extends Controller
{
    public function index(Request $request)
    {
        $classes = ClassModel::query()
            ->with(['academicYear', 'cohorts'])
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
            'academic_year_id'   => ['required', 'exists:academic_years,id'],
            'class_number'       => ['required', 'string', 'max:255'],
            'ngb_number'         => ['required', 'string', 'max:255', 'unique:classes,ngb_number'],
            'status'             => ['required', 'in:forming,active,completed'],
            'alpha_start_date'   => ['nullable', 'date'],
            'alpha_end_date'     => ['nullable', 'date', 'after_or_equal:alpha_start_date'],
            'bravo_start_date'   => ['nullable', 'date'],
            'bravo_end_date'     => ['nullable', 'date', 'after_or_equal:bravo_start_date'],
        ]);

        $class = ClassModel::create([
            'academic_year_id' => $validated['academic_year_id'],
            'class_number'     => $validated['class_number'],
            'ngb_number'       => $validated['ngb_number'],
            'status'           => $validated['status'],
        ]);

        // Update cohort dates if provided (cohorts auto-created by boot())
        $class->cohorts()->where('name', 'alpha')->first()?->update([
            'start_date' => $validated['alpha_start_date'] ?? null,
            'end_date'   => $validated['alpha_end_date'] ?? null,
        ]);

        $class->cohorts()->where('name', 'bravo')->first()?->update([
            'start_date' => $validated['bravo_start_date'] ?? null,
            'end_date'   => $validated['bravo_end_date'] ?? null,
        ]);

        return redirect()->route('admin.classes.show', $class)
            ->with('success', "Class {$class->class_number} created successfully.");
    }

    public function show(ClassModel $class)
    {
        $class->load([
            'academicYear',
            'cohorts.cohortCourses.course',
            'cohorts.cohortCourses.employee',
            'cohorts.cohortCourses.institution',
        ]);

        // Append enrollment counts
        foreach ($class->cohorts as $cohort) {
            foreach ($cohort->cohortCourses as $cc) {
                $cc->append(['enrolled_count', 'available_seats']);
            }
        }

        return Inertia::render('Admin/Classes/Show', [
            'class' => $class,
        ]);
    }

    public function edit(ClassModel $class)
    {
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();

        return Inertia::render('Admin/Classes/Edit', [
            'class'         => $class->load(['academicYear', 'cohorts']),
            'academicYears' => $academicYears,
        ]);
    }

    public function update(Request $request, ClassModel $class)
    {
        $validated = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'class_number'     => ['required', 'string', 'max:255'],
            'ngb_number'       => ['required', 'string', 'max:255', 'unique:classes,ngb_number,' . $class->id],
            'status'           => ['required', 'in:forming,active,completed'],
            'alpha_start_date' => ['nullable', 'date'],
            'alpha_end_date'   => ['nullable', 'date', 'after_or_equal:alpha_start_date'],
            'bravo_start_date' => ['nullable', 'date'],
            'bravo_end_date'   => ['nullable', 'date', 'after_or_equal:bravo_start_date'],
        ]);

        $class->update([
            'academic_year_id' => $validated['academic_year_id'],
            'class_number'     => $validated['class_number'],
            'ngb_number'       => $validated['ngb_number'],
            'status'           => $validated['status'],
        ]);

        $class->cohorts()->where('name', 'alpha')->first()?->update([
            'start_date' => $validated['alpha_start_date'] ?? null,
            'end_date'   => $validated['alpha_end_date'] ?? null,
        ]);

        $class->cohorts()->where('name', 'bravo')->first()?->update([
            'start_date' => $validated['bravo_start_date'] ?? null,
            'end_date'   => $validated['bravo_end_date'] ?? null,
        ]);

        return redirect()->route('admin.classes.show', $class)
            ->with('success', 'Class updated successfully.');
    }

    public function destroy(ClassModel $class)
    {
        $hasEnrollments = $class->cohortCourses()
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

    public function updateCohort(Request $request, ClassModel $class, Cohort $cohort)
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $cohort->update($validated);

        return back()->with('success', ucfirst($cohort->name) . ' cohort dates updated.');
    }
}
