<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->decimal('weighted_average', 5, 2)->nullable()->after('status');
            $table->renameColumn('final_grade', 'final_letter_grade');
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->renameColumn('final_letter_grade', 'final_grade');
            $table->dropColumn('weighted_average');
        });
    }
};
