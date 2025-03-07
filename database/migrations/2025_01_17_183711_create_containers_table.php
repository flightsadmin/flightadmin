<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('containers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('airline_id')->constrained()->cascadeOnDelete();
            $table->string('container_number')->unique();
            $table->integer('tare_weight')->default(60);
            $table->integer('max_weight')->default(2000);
            $table->timestamps();
        });

        Schema::create('container_flight', function (Blueprint $table) {
            $table->id();
            $table->foreignId('container_id')->constrained()->cascadeOnDelete();
            $table->foreignId('flight_id')->constrained()->cascadeOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('hold_positions')->nullOnDelete();
            $table->string('type')->default('baggage');
            $table->string('status')->default('unloaded');
            $table->integer('weight')->default(0);
            $table->integer('pieces')->default(0);
            $table->timestamps();

            $table->unique(['container_id', 'flight_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('container_flight');
        Schema::dropIfExists('containers');
    }
};
