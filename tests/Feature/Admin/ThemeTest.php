<?php

namespace Tests\Feature\Admin;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ThemeTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function enableTheme(): void
    {
        Setting::set('feature_theme_enabled', '1', 'boolean');
    }

    private function disableTheme(): void
    {
        Setting::set('feature_theme_enabled', '0', 'boolean');
    }

    // ── Feature flag ─────────────────────────────────────────────────────────

    public function test_theme_disabled_by_default(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('features.theme_enabled', false)
            );
    }

    public function test_toggle_disables_theme(): void
    {
        $user = $this->admin();

        $this->actingAs($user)->post(route('admin.feature-settings.update'), [
            'theme_enabled' => false,
        ])->assertRedirect();

        $this->assertDatabaseHas('settings', [
            'key'   => 'feature_theme_enabled',
            'value' => '0',
        ]);
    }

    public function test_toggle_enables_theme(): void
    {
        $this->disableTheme();
        $user = $this->admin();

        $this->actingAs($user)->post(route('admin.feature-settings.update'), [
            'theme_enabled' => true,
        ])->assertRedirect();

        $this->assertDatabaseHas('settings', [
            'key'   => 'feature_theme_enabled',
            'value' => '1',
        ]);
    }

    public function test_features_prop_reflects_theme_flag(): void
    {
        $user = $this->admin();

        $this->enableTheme();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('features.theme_enabled', true)
            );

        $this->disableTheme();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('features.theme_enabled', false)
            );
    }

    // ── Route guard ──────────────────────────────────────────────────────────

    public function test_theme_page_blocked_when_disabled(): void
    {
        $this->disableTheme();

        $this->actingAs($this->admin())
            ->get(route('admin.theme'))
            ->assertStatus(403);
    }

    public function test_theme_update_blocked_when_disabled(): void
    {
        $this->disableTheme();

        $this->actingAs($this->admin())
            ->post(route('admin.theme.update'), [
                'primary_color'    => '#000000',
                'secondary_color'  => '#000000',
                'accent_color'     => '#000000',
                'background_color' => '#ffffff',
                'text_color'       => '#000000',
            ])->assertStatus(403);
    }

    public function test_theme_page_accessible_when_enabled(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.theme'))
            ->assertOk();
    }
}
