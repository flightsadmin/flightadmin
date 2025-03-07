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
        Schema::create('cabin_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aircraft_type_id')->constrained()->onDelete('cascade');
            $table->string('name')->nullable();
            $table->float('max_capacity')->nullable();
            $table->decimal('index', 8, 5)->nullable();
            $table->decimal('arm', 8, 5)->nullable();
            $table->timestamps();
        });

        Schema::create('seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aircraft_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cabin_zone_id')->constrained()->cascadeOnDelete();
            $table->integer('row');
            $table->string('column', 1);
            $table->string('designation', 4);
            $table->string('type')->default('economy');
            $table->boolean('is_exit')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['aircraft_type_id', 'designation']);
        });

        Schema::create('flight_seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flight_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seat_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_blocked')->default(false);
            $table->string('blocked_reason')->nullable();
            $table->timestamps();

            $table->unique(['flight_id', 'seat_id']);
        });

        Schema::create('passengers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flight_id')->constrained()->onDelete('cascade');
            $table->foreignId('seat_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->enum('type', ['male', 'female', 'child', 'infant'])->nullable();
            $table->string('pnr')->nullable();
            $table->string('ticket_number')->nullable();
            $table->string('acceptance_status')->default('booked');
            $table->string('boarding_status')->default('boarding');
            $table->json('special_requirements')->nullable();
            $table->json('documents')->nullable();
            $table->timestamps();

            $table->unique(['flight_id', 'seat_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cabin_zones');
        Schema::dropIfExists('seats');
        Schema::dropIfExists('flight_seats');
        Schema::dropIfExists('passengers');
    }
};
