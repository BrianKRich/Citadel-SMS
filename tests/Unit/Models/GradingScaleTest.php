<?php

namespace Tests\Unit\Models;

use App\Models\GradingScale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradingScaleTest extends TestCase
{
    use RefreshDatabase;

    private array $standardScale = [
        ['letter' => 'A', 'min_percentage' => 90, 'gpa_points' => 4.0],
        ['letter' => 'B', 'min_percentage' => 80, 'gpa_points' => 3.0],
        ['letter' => 'C', 'min_percentage' => 70, 'gpa_points' => 2.0],
        ['letter' => 'D', 'min_percentage' => 60, 'gpa_points' => 1.0],
        ['letter' => 'F', 'min_percentage' => 0,  'gpa_points' => 0.0],
    ];

    private function makeScale(array $overrides = []): GradingScale
    {
        return GradingScale::factory()->create(array_merge(
            ['scale' => $this->standardScale],
            $overrides
        ));
    }

    public function test_get_letter_grade_returns_a_for_90_percent(): void
    {
        $scale = $this->makeScale();

        $this->assertSame('A', $scale->getLetterGrade(90.0));
    }

    public function test_get_letter_grade_returns_b_for_89_percent(): void
    {
        $scale = $this->makeScale();

        $this->assertSame('B', $scale->getLetterGrade(89.0));
    }

    public function test_get_letter_grade_returns_c_for_70_percent(): void
    {
        $scale = $this->makeScale();

        $this->assertSame('C', $scale->getLetterGrade(70.0));
    }

    public function test_get_letter_grade_returns_d_for_60_percent(): void
    {
        $scale = $this->makeScale();

        $this->assertSame('D', $scale->getLetterGrade(60.0));
    }

    public function test_get_letter_grade_returns_f_for_59_percent(): void
    {
        $scale = $this->makeScale();

        $this->assertSame('F', $scale->getLetterGrade(59.0));
    }

    public function test_get_gpa_points_returns_correct_value(): void
    {
        $scale = $this->makeScale();

        $this->assertSame(4.0, $scale->getGpaPoints('A'));
        $this->assertSame(3.0, $scale->getGpaPoints('B'));
        $this->assertSame(0.0, $scale->getGpaPoints('F'));
    }

    public function test_set_default_unsets_other_scales(): void
    {
        $first = $this->makeScale(['name' => 'First Scale', 'is_default' => true]);
        $second = $this->makeScale(['name' => 'Second Scale', 'is_default' => false]);

        $second->setDefault();

        $this->assertFalse($first->fresh()->is_default);
        $this->assertTrue($second->fresh()->is_default);
    }
}
