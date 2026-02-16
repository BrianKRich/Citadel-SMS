<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GradingScale;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GradingScaleController extends Controller
{
    public function index()
    {
        $scales = GradingScale::latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/GradingScales/Index', [
            'scales' => $scales,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/GradingScales/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'scale' => ['required', 'array', 'min:1'],
            'scale.*.letter' => ['required', 'string', 'max:5'],
            'scale.*.min_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'scale.*.gpa_points' => ['required', 'numeric', 'min:0', 'max:5'],
        ]);

        GradingScale::create($validated);

        return redirect()->route('admin.grading-scales.index')
            ->with('success', 'Grading scale created successfully.');
    }

    public function edit(GradingScale $gradingScale)
    {
        return Inertia::render('Admin/GradingScales/Edit', [
            'scale' => $gradingScale,
        ]);
    }

    public function update(Request $request, GradingScale $gradingScale)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'scale' => ['required', 'array', 'min:1'],
            'scale.*.letter' => ['required', 'string', 'max:5'],
            'scale.*.min_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'scale.*.gpa_points' => ['required', 'numeric', 'min:0', 'max:5'],
        ]);

        $gradingScale->update($validated);

        return redirect()->route('admin.grading-scales.index')
            ->with('success', 'Grading scale updated successfully.');
    }

    public function destroy(GradingScale $gradingScale)
    {
        if ($gradingScale->is_default) {
            return back()->with('error', 'Cannot delete the default grading scale.');
        }

        $gradingScale->delete();

        return redirect()->route('admin.grading-scales.index')
            ->with('success', 'Grading scale deleted successfully.');
    }

    public function setDefault(GradingScale $gradingScale)
    {
        $gradingScale->setDefault();

        return back()->with('success', "{$gradingScale->name} is now the default grading scale.");
    }
}
