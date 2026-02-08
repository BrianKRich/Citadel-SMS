<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ThemeController extends Controller
{
    public function index()
    {
        $theme = [
            'primary_color' => Setting::get('theme_primary_color', '#6366f1'),
            'secondary_color' => Setting::get('theme_secondary_color', '#8b5cf6'),
            'accent_color' => Setting::get('theme_accent_color', '#ec4899'),
            'background_color' => Setting::get('theme_background_color', '#ffffff'),
            'text_color' => Setting::get('theme_text_color', '#1f2937'),
        ];

        return Inertia::render('Admin/Theme', [
            'theme' => $theme
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'primary_color' => 'required|string',
            'secondary_color' => 'required|string',
            'accent_color' => 'required|string',
            'background_color' => 'required|string',
            'text_color' => 'required|string',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set('theme_' . $key, $value);
        }

        return redirect()->back()->with('success', 'Theme updated successfully!');
    }

    public function getTheme()
    {
        return response()->json([
            'primary_color' => Setting::get('theme_primary_color', '#6366f1'),
            'secondary_color' => Setting::get('theme_secondary_color', '#8b5cf6'),
            'accent_color' => Setting::get('theme_accent_color', '#ec4899'),
            'background_color' => Setting::get('theme_background_color', '#ffffff'),
            'text_color' => Setting::get('theme_text_color', '#1f2937'),
        ]);
    }
}
