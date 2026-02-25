<?php

use App\Models\Department;
use App\Models\EmployeeRole;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Trainer role is no longer seeded by migration; manage via UI
    }

    public function down(): void
    {
        //
    }
};
