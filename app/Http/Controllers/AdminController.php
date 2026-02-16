<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\Enrollment;
use App\Models\Grade;
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
}
