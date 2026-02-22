<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Grade;
use Illuminate\Support\Facades\Auth;

class GradeObserver
{
    private array $excluded = ['created_at', 'updated_at'];

    private function label(Grade $grade): string
    {
        return "Grade #{$grade->id} (Assessment #{$grade->assessment_id}, Enrollment #{$grade->enrollment_id})";
    }

    private function snapshot(Grade $grade): array
    {
        return collect($grade->getAttributes())->except($this->excluded)->toArray();
    }

    private function write(Grade $grade, string $action, ?array $old, ?array $new): void
    {
        AuditLog::create([
            'user_id'        => Auth::id(),
            'auditable_type' => Grade::class,
            'auditable_id'   => $grade->id,
            'subject_label'  => $this->label($grade),
            'action'         => $action,
            'old_values'     => $old,
            'new_values'     => $new,
        ]);
    }

    public function created(Grade $grade): void
    {
        $this->write($grade, 'created', null, $this->snapshot($grade));
    }

    public function updated(Grade $grade): void
    {
        $dirty = collect($grade->getDirty())->except($this->excluded)->toArray();
        if (empty($dirty)) {
            return;
        }
        $old = collect($dirty)->mapWithKeys(fn ($v, $k) => [$k => $grade->getOriginal($k)])->toArray();
        $this->write($grade, 'updated', $old, $dirty);
    }

    public function deleted(Grade $grade): void
    {
        $this->write($grade, 'deleted', $this->snapshot($grade), null);
    }
}
