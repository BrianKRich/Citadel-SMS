<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purge_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->nullOnDelete()->constrained('users');
            $table->string('user_name');          // snapshot — survives user deletion
            $table->string('older_than');         // '30','90','180','365','all'
            $table->unsignedInteger('purged_count');
            $table->text('reason');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purge_logs');
    }
};
