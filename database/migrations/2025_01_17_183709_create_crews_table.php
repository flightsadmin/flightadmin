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
        Schema::create('crews', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('position');
            $table->string('employee_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('crew_flight', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crew_id')->constrained()->onDelete('cascade');
            $table->foreignId('flight_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crews');
    }
};
