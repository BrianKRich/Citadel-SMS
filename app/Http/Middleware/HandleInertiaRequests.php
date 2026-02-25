<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'features' => [
                'attendance_enabled'      => Setting::get('feature_attendance_enabled', '0') === '1',
                'theme_enabled'           => Setting::get('feature_theme_enabled', '0') === '1',
                'recent_activity_enabled' => Setting::get('feature_recent_activity_enabled', '0') === '1',
                'grades_enabled'          => Setting::get('feature_grades_enabled', '0') === '1',
                'report_cards_enabled'    => Setting::get('feature_report_cards_enabled', '0') === '1',
                'documents_enabled'       => Setting::get('feature_documents_enabled', '0') === '1',
                'staff_training_enabled'  => Setting::get('feature_staff_training_enabled', '0') === '1',
                'academy_setup_enabled'   => Setting::get('feature_academy_setup_enabled', '0') === '1',
            ],
        ];
    }
}
