<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'users_today' => User::whereDate('created_at', today())->count(),
            'users_this_week' => User::where('created_at', '>=', now()->subWeek())->count(),
            'users_this_month' => User::where('created_at', '>=', now()->subMonth())->count(),
            'total_assessments' => Assessment::count(),
            'grades_this_week' => Grade::where('graded_at', '>=', now()->subWeek())->count(),
            'average_gpa' => round((float) Enrollment::whereNotNull('grade_points')->avg('grade_points'), 2),
        ];

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
        ]);
    }

    public function featureSettings()
    {
        return Inertia::render('Admin/FeatureSettings');
    }

    public function updateFeatureSettings(Request $request)
    {
        $validated = $request->validate([
            'attendance_enabled'      => ['sometimes', 'boolean'],
            'theme_enabled'           => ['sometimes', 'boolean'],
            'recent_activity_enabled' => ['sometimes', 'boolean'],
            'grades_enabled'          => ['sometimes', 'boolean'],
            'report_cards_enabled'    => ['sometimes', 'boolean'],
        ]);

        if (array_key_exists('attendance_enabled', $validated)) {
            Setting::set('feature_attendance_enabled', $validated['attendance_enabled'] ? '1' : '0', 'boolean');
        }

        if (array_key_exists('theme_enabled', $validated)) {
            Setting::set('feature_theme_enabled', $validated['theme_enabled'] ? '1' : '0', 'boolean');
        }

        if (array_key_exists('recent_activity_enabled', $validated)) {
            Setting::set('feature_recent_activity_enabled', $validated['recent_activity_enabled'] ? '1' : '0', 'boolean');
        }

        if (array_key_exists('grades_enabled', $validated)) {
            Setting::set('feature_grades_enabled', $validated['grades_enabled'] ? '1' : '0', 'boolean');
        }

        if (array_key_exists('report_cards_enabled', $validated)) {
            Setting::set('feature_report_cards_enabled', $validated['report_cards_enabled'] ? '1' : '0', 'boolean');
        }

        return back()->with('success', 'Feature settings updated.');
    }
}
