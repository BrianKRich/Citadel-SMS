<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->unique(); // e.g., "EMP-2026-001"
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->foreignId('department_id')->constrained()->restrictOnDelete();
            $table->foreignId('role_id')->constrained('employee_roles')->restrictOnDelete();
            $table->date('date_of_birth')->nullable();
            $table->date('hire_date');
            $table->text('qualifications')->nullable();
            $table->string('photo')->nullable();
            $table->enum('status', ['active', 'inactive', 'on_leave'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
