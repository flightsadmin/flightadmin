<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aircraft_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('airline_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('manufacturer');
            $table->integer('max_deck_crew')->default(2);
            $table->integer('max_cabin_crew')->default(2);
            $table->integer('max_passengers')->default(0);
            $table->integer('cargo_capacity')->default(0);
            $table->integer('max_fuel_capacity')->default(0);
            $table->integer('empty_weight')->default(0);
            $table->integer('max_zero_fuel_weight')->default(0);
            $table->integer('max_takeoff_weight')->default(0);
            $table->integer('max_landing_weight')->default(0);
            $table->string('category')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aircraft_types');
    }
};
