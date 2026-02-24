<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\TrainingCourse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TrainingCourseController extends Controller
{
    private function requireStaffTrainingEnabled(): void
    {
        abort_if(Setting::get('feature_staff_training_enabled', '0') !== '1', 403);
    }

    public function index(Request $request)
    {
        $this->requireStaffTrainingEnabled();
        abort_unless(auth()->user()->isAdmin(), 403);

        $query = TrainingCourse::query();

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $courses = $query->orderBy('name')->paginate(10)->withQueryString();

        return Inertia::render('Admin/Training/Courses/Index', [
            'courses' => $courses,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create()
    {
        $this->requireStaffTrainingEnabled();
        abort_unless(auth()->user()->isAdmin(), 403);

        return Inertia::render('Admin/Training/Courses/Create');
    }

    public function store(Request $request)
    {
        $this->requireStaffTrainingEnabled();
        abort_unless(auth()->user()->isAdmin(), 403);

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'trainer'     => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['boolean'],
        ]);

        TrainingCourse::create($validated);

        return redirect()->route('admin.training-courses.index')
            ->with('success', 'Training course created successfully.');
    }

    public function edit(TrainingCourse $trainingCourse)
    {
        $this->requireStaffTrainingEnabled();
        abort_unless(auth()->user()->isAdmin(), 403);

        return Inertia::render('Admin/Training/Courses/Edit', [
            'course' => $trainingCourse,
        ]);
    }

    public function update(Request $request, TrainingCourse $trainingCourse)
    {
        $this->requireStaffTrainingEnabled();
        abort_unless(auth()->user()->isAdmin(), 403);

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'trainer'     => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['boolean'],
        ]);

        $trainingCourse->update($validated);

        return redirect()->route('admin.training-courses.index')
            ->with('success', 'Training course updated successfully.');
    }

    public function destroy(TrainingCourse $trainingCourse)
    {
        $this->requireStaffTrainingEnabled();
        abort_unless(auth()->user()->isAdmin(), 403);

        if ($trainingCourse->trainingRecords()->exists()) {
            return back()->with('error', 'Cannot delete a course with existing training records.');
        }

        $trainingCourse->delete();

        return redirect()->route('admin.training-courses.index')
            ->with('success', 'Training course deleted.');
    }
}
