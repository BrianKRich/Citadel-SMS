<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;

class StudentObserver
{
    private array $excluded = ['photo', 'created_at', 'updated_at', 'deleted_at'];

    private function label(Student $student): string
    {
        return "{$student->student_id} ({$student->first_name} {$student->last_name})";
    }

    private function snapshot(Student $student): array
    {
        return collect($student->getAttributes())->except($this->excluded)->toArray();
    }

    private function write(Student $student, string $action, ?array $old, ?array $new): void
    {
        AuditLog::create([
            'user_id'        => Auth::id(),
            'auditable_type' => Student::class,
            'auditable_id'   => $student->id,
            'subject_label'  => $this->label($student),
            'action'         => $action,
            'old_values'     => $old,
            'new_values'     => $new,
        ]);
    }

    public function created(Student $student): void
    {
        $this->write($student, 'created', null, $this->snapshot($student));
    }

    public function updated(Student $student): void
    {
        $dirty = collect($student->getDirty())->except($this->excluded)->toArray();
        if (empty($dirty)) {
            return;
        }
        $old = collect($dirty)->mapWithKeys(fn ($v, $k) => [$k => $student->getOriginal($k)])->toArray();
        $this->write($student, 'updated', $old, $dirty);
    }

    public function deleted(Student $student): void
    {
        $this->write($student, 'deleted', $this->snapshot($student), null);
    }

    public function restored(Student $student): void
    {
        $this->write($student, 'restored', null, null);
    }
}
