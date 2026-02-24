<?php

use App\Models\Department;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $ops = Department::firstOrCreate(['name' => 'Operations']);

        $roles = [
            'Director',
            'Deputy Director',
            'Commandant',
            'Administrative Assistant',
            'Site Administrator',
        ];

        foreach ($roles as $roleName) {
            $ops->roles()->firstOrCreate(['name' => $roleName]);
        }
    }

    public function down(): void
    {
        $ops = Department::where('name', 'Operations')->first();
        if ($ops) {
            $ops->roles()->delete();
            $ops->delete();
        }
    }
};
