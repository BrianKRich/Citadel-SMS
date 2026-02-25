<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AcademyController extends Controller
{
    private function requireEnabled(): void
    {
        abort_if(Setting::get('feature_academy_setup_enabled', '0') !== '1', 403);
    }

    public function index()
    {
        $this->requireEnabled();
        abort_unless(auth()->user()->isAdmin(), 403);

        $academy = [
            'name'         => Setting::get('academy_name', ''),
            'address'      => Setting::get('academy_address', ''),
            'phone'        => Setting::get('academy_phone', ''),
            'director'     => Setting::get('academy_director', ''),
            'year_started' => Setting::get('academy_year_started', ''),
        ];

        return Inertia::render('Admin/Academy', [
            'academy' => $academy,
        ]);
    }

    public function update(Request $request)
    {
        $this->requireEnabled();
        abort_unless(auth()->user()->isAdmin(), 403);

        $validated = $request->validate([
            'name'         => ['nullable', 'string', 'max:255'],
            'address'      => ['nullable', 'string', 'max:500'],
            'phone'        => ['nullable', 'string', 'max:50'],
            'director'     => ['nullable', 'string', 'max:255'],
            'year_started' => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 10)],
        ]);

        Setting::set('academy_name', $validated['name'] ?? '');
        Setting::set('academy_address', $validated['address'] ?? '');
        Setting::set('academy_phone', $validated['phone'] ?? '');
        Setting::set('academy_director', $validated['director'] ?? '');
        Setting::set('academy_year_started', $validated['year_started'] ?? '');

        return back()->with('success', 'Academy information updated successfully.');
    }
}
