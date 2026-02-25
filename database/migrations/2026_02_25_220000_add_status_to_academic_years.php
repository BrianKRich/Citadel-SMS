<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_years', function (Blueprint $table) {
            $table->string('status')->default('forming')->after('is_current');
        });

        // Migrate existing data
        DB::statement("UPDATE academic_years SET status = CASE WHEN is_current = true THEN 'current' ELSE 'forming' END");

        Schema::table('academic_years', function (Blueprint $table) {
            $table->dropColumn('is_current');
        });
    }

    public function down(): void
    {
        Schema::table('academic_years', function (Blueprint $table) {
            $table->boolean('is_current')->default(false)->after('status');
        });

        DB::statement("UPDATE academic_years SET is_current = CASE WHEN status = 'current' THEN true ELSE false END");

        Schema::table('academic_years', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
