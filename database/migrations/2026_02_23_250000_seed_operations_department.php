<?php

use App\Models\Department;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $ops = Department::firstOrCreate(['name' => 'Operations']);
        $ops->roles()->firstOrCreate(['name' => 'Site Administrator']);
    }

    public function down(): void
    {
        $ops = Department::where('name', 'Operations')->first();
        if ($ops) {
            $ops->roles()->where('name', 'Site Administrator')->delete();
            $ops->delete();
        }
    }
};
