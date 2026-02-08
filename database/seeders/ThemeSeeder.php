<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ThemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultTheme = [
            'theme_primary_color' => '#6366f1',
            'theme_secondary_color' => '#8b5cf6',
            'theme_accent_color' => '#ec4899',
            'theme_background_color' => '#ffffff',
            'theme_text_color' => '#1f2937',
        ];

        foreach ($defaultTheme as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'type' => 'string']
            );
        }
    }
}
