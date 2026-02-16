<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('assessment_category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('max_score', 8, 2);
            $table->date('due_date')->nullable();
            $table->decimal('weight', 5, 2)->nullable();
            $table->boolean('is_extra_credit')->default(false);
            $table->string('status')->default('draft');
            $table->timestamps();

            $table->index('class_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
