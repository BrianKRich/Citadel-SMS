<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->boolean('is_system')->default(false)->after('name');
        });

        // Mark the six foundational departments as system-protected
        DB::table('departments')
            ->whereIn('name', ['Education', 'Administration', 'Counseling', 'Cadre', 'Health Services', 'Operations'])
            ->update(['is_system' => true]);
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('is_system');
        });
    }
};
