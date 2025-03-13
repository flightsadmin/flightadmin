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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('airline_id')->constrained()->onDelete('cascade');
            $table->foreignId('aircraft_type_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('route_id')->nullable()->constrained()->nullOnDelete();
            $table->string('flight_number');
            $table->time('scheduled_departure_time');
            $table->time('scheduled_arrival_time');
            $table->date('start_date');
            $table->date('end_date');
            $table->json('days_of_week');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['airline_id', 'flight_number', 'start_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
