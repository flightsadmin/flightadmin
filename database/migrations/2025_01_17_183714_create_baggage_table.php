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
        Schema::create('baggage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flight_id')->constrained()->onDelete('cascade');
            $table->foreignId('passenger_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('container_id')->nullable()->constrained()->nullOnDelete();
            $table->string('tag_number')->unique();
            $table->integer('weight');
            $table->string('status')->default('checked');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('baggage');
    }
};
