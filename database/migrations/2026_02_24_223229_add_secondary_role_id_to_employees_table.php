<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('secondary_role_id')
                  ->nullable()
                  ->after('role_id')
                  ->constrained('employee_roles')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['secondary_role_id']);
            $table->dropColumn('secondary_role_id');
        });
    }
};
