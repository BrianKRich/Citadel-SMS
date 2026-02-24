<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\StudentNote;
use Illuminate\Support\Facades\Auth;

class StudentNoteObserver
{
    private function label(StudentNote $note): string
    {
        $note->loadMissing('student');
        $studentId = $note->student?->student_id ?? "student #{$note->student_id}";
        return "Note: {$note->title} on {$studentId}";
    }

    private function write(StudentNote $note, string $action, ?array $old, ?array $new): void
    {
        $note->loadMissing('student');

        AuditLog::create([
            'user_id'        => Auth::id(),
            'auditable_type' => StudentNote::class,
            'auditable_id'   => $note->id,
            'subject_label'  => $this->label($note),
            'action'         => $action,
            'old_values'     => $old,
            'new_values'     => $new,
        ]);
    }

    public function created(StudentNote $note): void
    {
        $this->write($note, 'created', null, [
            'title'         => $note->title,
            'body'          => $note->body,
            'department_id' => $note->department_id,
        ]);
    }

    public function updated(StudentNote $note): void
    {
        $tracked = ['title', 'body'];
        $dirty = collect($note->getDirty())->only($tracked)->toArray();
        if (empty($dirty)) {
            return;
        }
        $old = collect($dirty)->mapWithKeys(fn ($v, $k) => [$k => $note->getOriginal($k)])->toArray();
        $this->write($note, 'updated', $old, $dirty);
    }

    public function deleted(StudentNote $note): void
    {
        $this->write($note, 'deleted', [
            'title'         => $note->title,
            'body'          => $note->body,
            'department_id' => $note->department_id,
        ], null);
    }
}
