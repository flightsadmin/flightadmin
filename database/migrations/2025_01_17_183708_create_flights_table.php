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
        Schema::create('flights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('airline_id')->constrained()->onDelete('cascade');
            $table->foreignId('aircraft_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('schedule_id')->nullable()->constrained()->nullOnDelete();
            $table->string('flight_number');
            $table->string('departure_airport');
            $table->string('arrival_airport');
            $table->dateTime('scheduled_departure_time');
            $table->dateTime('scheduled_arrival_time');
            $table->dateTime('actual_departure_time')->nullable();
            $table->dateTime('actual_arrival_time')->nullable();
            $table->string('status', 20)->default('scheduled');
            $table->timestamps();

            $table->unique(['airline_id', 'flight_number', 'scheduled_departure_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flights');
    }
};
