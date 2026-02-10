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
        Schema::create('phone_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('phoneable_type');
            $table->unsignedBigInteger('phoneable_id');
            $table->string('area_code', 3)->nullable();
            $table->string('number', 7);
            $table->string('type', 20); // primary, mobile, home, work, emergency
            $table->string('label', 50)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->index(['phoneable_type', 'phoneable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phone_numbers');
    }
};
