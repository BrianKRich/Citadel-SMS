<?php

namespace Database\Seeders;

use App\Models\Enrollment;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceSeeder extends Seeder
{
    // Day name → Carbon dayOfWeek integer (0 = Sun)
    private const DAY_MAP = [
        'Sunday'    => 0,
        'Monday'    => 1,
        'Tuesday'   => 2,
        'Wednesday' => 3,
        'Thursday'  => 4,
        'Friday'    => 5,
        'Saturday'  => 6,
    ];

    public function run(): void
    {
        // Enable the feature flag so attendance UI is visible after seeding
        Setting::set('feature_attendance_enabled', '1', 'boolean');

        $admin = User::where('role', 'admin')->first() ?? User::first();
        $now   = now();

        // Alpha cohort window
        $termStart = Carbon::parse('2025-09-01');
        $termEnd   = Carbon::parse('2026-02-15');

        // Load all enrolled enrollments with their cohort-course schedule
        $enrollments = Enrollment::with('cohortCourse')
            ->enrolled()
            ->get();

        // Assign a random attendance profile to each student once
        $studentProfiles = [];

        $records = [];

        foreach ($enrollments as $enrollment) {
            $schedule = $enrollment->cohortCourse?->schedule ?? [];

            // Collect unique meeting day numbers for this cohort-course
            $meetingDays = collect($schedule)
                ->pluck('day')
                ->unique()
                ->map(fn ($d) => self::DAY_MAP[$d] ?? null)
                ->filter()
                ->values()
                ->toArray();

            if (empty($meetingDays)) {
                continue;
            }

            // Assign profile per student (consistent across all their courses)
            if (! isset($studentProfiles[$enrollment->student_id])) {
                $studentProfiles[$enrollment->student_id] = $this->randomProfile();
            }
            $profile = $studentProfiles[$enrollment->student_id];

            // Walk the cohort window, generate a record for each meeting day
            $date = $termStart->copy();
            while ($date->lte($termEnd)) {
                if (in_array($date->dayOfWeek, $meetingDays)) {
                    $records[] = [
                        'student_id'       => $enrollment->student_id,
                        'cohort_course_id' => $enrollment->cohort_course_id,
                        'date'             => $date->toDateString(),
                        'status'           => $this->pickStatus($profile),
                        'notes'            => null,
                        'marked_by'        => $admin?->id,
                        'created_at'       => $now,
                        'updated_at'       => $now,
                    ];
                }
                $date->addDay();
            }
        }

        // Bulk insert in chunks to stay memory-efficient
        foreach (array_chunk($records, 500) as $chunk) {
            DB::table('attendance_records')->insert($chunk);
        }

        $total = count($records);
        $this->command->info("  Created {$total} attendance records for Alpha cohort.");
    }

    private function randomProfile(): string
    {
        // Distribution: 60% average, 25% high, 15% low
        $r = random_int(1, 100);
        return match (true) {
            $r <= 25  => 'high',
            $r <= 85  => 'average',
            default   => 'low',
        };
    }

    private function pickStatus(string $profile): string
    {
        $r = random_int(1, 100);
        return match ($profile) {
            'high' => match (true) {
                $r <= 88 => 'present',
                $r <= 93 => 'late',
                $r <= 97 => 'excused',
                default  => 'absent',
            },
            'low' => match (true) {
                $r <= 55 => 'present',
                $r <= 65 => 'late',
                $r <= 80 => 'excused',
                default  => 'absent',
            },
            default => match (true) {  // average
                $r <= 75 => 'present',
                $r <= 85 => 'late',
                $r <= 93 => 'excused',
                default  => 'absent',
            },
        };
    }
}
