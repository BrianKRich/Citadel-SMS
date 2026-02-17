<?php

namespace Database\Seeders;

use App\Models\ClassModel;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Database\Seeder;

class EnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $students = Student::all();
        $fallClasses = ClassModel::where('status', 'in_progress')->get();

        // Track remaining capacity per class
        $capacity = $fallClasses->pluck('max_students', 'id')->toArray();

        foreach ($students as $student) {
            // Pick 5 random Fall classes that still have capacity
            $available = $fallClasses->filter(fn ($c) => ($capacity[$c->id] ?? 0) > 0);

            $selected = $available->random(min(5, $available->count()));

            foreach ($selected as $class) {
                Enrollment::create([
                    'student_id' => $student->id,
                    'class_id' => $class->id,
                    'enrollment_date' => '2025-08-15',
                    'status' => 'enrolled',
                ]);

                $capacity[$class->id]--;
            }
        }
    }
}
