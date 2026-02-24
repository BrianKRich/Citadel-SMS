<?php

use App\Models\Department;
use App\Models\EmployeeRole;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Department::all()->each(function ($dept) {
            if (!EmployeeRole::where('department_id', $dept->id)->where('name', 'Trainer')->exists()) {
                EmployeeRole::create(['department_id' => $dept->id, 'name' => 'Trainer']);
            }
        });
    }

    public function down(): void
    {
        EmployeeRole::where('name', 'Trainer')->delete();
    }
};
