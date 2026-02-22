<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;

class EmployeeObserver
{
    private array $excluded = ['photo', 'created_at', 'updated_at', 'deleted_at'];

    private function label(Employee $employee): string
    {
        return "{$employee->employee_id} ({$employee->first_name} {$employee->last_name})";
    }

    private function snapshot(Employee $employee): array
    {
        return collect($employee->getAttributes())->except($this->excluded)->toArray();
    }

    private function write(Employee $employee, string $action, ?array $old, ?array $new): void
    {
        AuditLog::create([
            'user_id'        => Auth::id(),
            'auditable_type' => Employee::class,
            'auditable_id'   => $employee->id,
            'subject_label'  => $this->label($employee),
            'action'         => $action,
            'old_values'     => $old,
            'new_values'     => $new,
        ]);
    }

    public function created(Employee $employee): void
    {
        $this->write($employee, 'created', null, $this->snapshot($employee));
    }

    public function updated(Employee $employee): void
    {
        $dirty = collect($employee->getDirty())->except($this->excluded)->toArray();
        if (empty($dirty)) {
            return;
        }
        $old = collect($dirty)->mapWithKeys(fn ($v, $k) => [$k => $employee->getOriginal($k)])->toArray();
        $this->write($employee, 'updated', $old, $dirty);
    }

    public function deleted(Employee $employee): void
    {
        $this->write($employee, 'deleted', $this->snapshot($employee), null);
    }

    public function restored(Employee $employee): void
    {
        $this->write($employee, 'restored', null, null);
    }
}
