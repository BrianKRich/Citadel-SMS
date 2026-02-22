<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_fields', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type'); // Student, Employee, Course, Class, Enrollment
            $table->string('label');
            $table->string('name'); // snake_case, auto-generated
            $table->enum('field_type', ['text', 'textarea', 'number', 'date', 'boolean', 'select']);
            $table->json('options')->nullable(); // only for field_type=select
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['entity_type', 'name']);
            $table->index(['entity_type', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_fields');
    }
};
