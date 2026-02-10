<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Term;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AcademicYearController extends Controller
{
    /**
     * Display a listing of academic years
     */
    public function index()
    {
        $academicYears = AcademicYear::with(['terms'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Admin/AcademicYears/Index', [
            'academicYears' => $academicYears,
        ]);
    }

    /**
     * Show the form for creating a new academic year
     */
    public function create()
    {
        return Inertia::render('Admin/AcademicYears/Create');
    }

    /**
     * Store a newly created academic year
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_current' => ['boolean'],
            'terms' => ['nullable', 'array'],
            'terms.*.name' => ['required', 'string', 'max:255'],
            'terms.*.start_date' => ['required', 'date'],
            'terms.*.end_date' => ['required', 'date', 'after:terms.*.start_date'],
            'terms.*.is_current' => ['boolean'],
        ]);

        $academicYear = AcademicYear::create([
            'name' => $validated['name'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'is_current' => $validated['is_current'] ?? false,
        ]);

        // Set as current if requested
        if ($validated['is_current'] ?? false) {
            $academicYear->setCurrent();
        }

        // Create terms if provided
        if (isset($validated['terms'])) {
            foreach ($validated['terms'] as $termData) {
                $term = $academicYear->terms()->create($termData);

                // Set first term as current if academic year is current
                if ($validated['is_current'] ?? false) {
                    if (!isset($currentTermSet)) {
                        $term->setCurrent();
                        $currentTermSet = true;
                    }
                }
            }
        }

        return redirect()->route('admin.academic-years.show', $academicYear)
            ->with('success', "Academic Year {$academicYear->name} created successfully.");
    }

    /**
     * Display the specified academic year
     */
    public function show(AcademicYear $academicYear)
    {
        $academicYear->load(['terms', 'classes.course']);

        return Inertia::render('Admin/AcademicYears/Show', [
            'academicYear' => $academicYear,
        ]);
    }

    /**
     * Show the form for editing the specified academic year
     */
    public function edit(AcademicYear $academicYear)
    {
        $academicYear->load(['terms']);

        return Inertia::render('Admin/AcademicYears/Edit', [
            'academicYear' => $academicYear,
        ]);
    }

    /**
     * Update the specified academic year
     */
    public function update(Request $request, AcademicYear $academicYear)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_current' => ['boolean'],
        ]);

        $academicYear->update($validated);

        // Set as current if requested
        if ($validated['is_current'] ?? false) {
            $academicYear->setCurrent();
        }

        return redirect()->route('admin.academic-years.show', $academicYear)
            ->with('success', 'Academic Year updated successfully.');
    }

    /**
     * Remove the specified academic year
     */
    public function destroy(AcademicYear $academicYear)
    {
        $name = $academicYear->name;
        $academicYear->delete();

        return redirect()->route('admin.academic-years.index')
            ->with('success', "Academic Year {$name} deleted successfully.");
    }

    /**
     * Set the academic year as current
     */
    public function setCurrent(AcademicYear $academicYear)
    {
        $academicYear->setCurrent();

        return back()->with('success', "{$academicYear->name} set as current academic year.");
    }

    /**
     * Store a new term for the academic year
     */
    public function storeTerm(Request $request, AcademicYear $academicYear)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_current' => ['boolean'],
        ]);

        $term = $academicYear->terms()->create($validated);

        // Set as current if requested
        if ($validated['is_current'] ?? false) {
            $term->setCurrent();
        }

        return back()->with('success', "Term {$term->name} created successfully.");
    }

    /**
     * Update a term
     */
    public function updateTerm(Request $request, AcademicYear $academicYear, Term $term)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_current' => ['boolean'],
        ]);

        $term->update($validated);

        // Set as current if requested
        if ($validated['is_current'] ?? false) {
            $term->setCurrent();
        }

        return back()->with('success', 'Term updated successfully.');
    }

    /**
     * Delete a term
     */
    public function destroyTerm(AcademicYear $academicYear, Term $term)
    {
        $name = $term->name;
        $term->delete();

        return back()->with('success', "Term {$name} deleted successfully.");
    }

    /**
     * Set a term as current
     */
    public function setCurrentTerm(AcademicYear $academicYear, Term $term)
    {
        $term->setCurrent();

        return back()->with('success', "{$term->name} set as current term.");
    }
}
