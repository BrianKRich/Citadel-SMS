<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('restrict');
            $table->foreignId('teacher_id')->constrained()->onDelete('restrict');
            $table->foreignId('academic_year_id')->constrained()->onDelete('restrict');
            $table->foreignId('term_id')->constrained()->onDelete('restrict');
            $table->string('section_name'); // e.g., "A", "B", "Morning"
            $table->string('room')->nullable();
            $table->json('schedule')->nullable(); // Weekly schedule as JSON
            $table->integer('max_students')->default(30);
            $table->enum('status', ['open', 'closed', 'in_progress', 'completed'])->default('open');
            $table->timestamps();

            // Indexes for performance
            $table->index(['course_id', 'term_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
