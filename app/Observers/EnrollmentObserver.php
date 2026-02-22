<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Auth;

class EnrollmentObserver
{
    // Allow-list: only these fields are meaningful enrollment changes.
    // Excludes weighted_average/final_letter_grade/grade_points which are
    // written by GradeCalculationService — those are noise, not intent.
    private array $tracked = ['student_id', 'class_id', 'enrollment_date', 'status'];

    private function label(Enrollment $enrollment): string
    {
        return "Enrollment #{$enrollment->id} (Student #{$enrollment->student_id}, Class #{$enrollment->class_id})";
    }

    private function snapshot(Enrollment $enrollment): array
    {
        return collect($enrollment->getAttributes())->only($this->tracked)->toArray();
    }

    private function write(Enrollment $enrollment, string $action, ?array $old, ?array $new): void
    {
        AuditLog::create([
            'user_id'        => Auth::id(),
            'auditable_type' => Enrollment::class,
            'auditable_id'   => $enrollment->id,
            'subject_label'  => $this->label($enrollment),
            'action'         => $action,
            'old_values'     => $old,
            'new_values'     => $new,
        ]);
    }

    public function created(Enrollment $enrollment): void
    {
        $this->write($enrollment, 'created', null, $this->snapshot($enrollment));
    }

    public function updated(Enrollment $enrollment): void
    {
        $dirty = collect($enrollment->getDirty())->only($this->tracked)->toArray();
        if (empty($dirty)) {
            return;
        }
        $old = collect($dirty)->mapWithKeys(fn ($v, $k) => [$k => $enrollment->getOriginal($k)])->toArray();
        $this->write($enrollment, 'updated', $old, $dirty);
    }

    public function deleted(Enrollment $enrollment): void
    {
        $this->write($enrollment, 'deleted', $this->snapshot($enrollment), null);
    }
}
