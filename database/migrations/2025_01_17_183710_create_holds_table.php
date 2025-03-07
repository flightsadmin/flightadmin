<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aircraft_type_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('code', 2)->nullable();
            $table->integer('position')->nullable();
            $table->integer('max_weight')->nullable();
            $table->decimal('index', 8, 5)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('hold_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hold_id')->constrained()->cascadeOnDelete();
            $table->string('code')->nullable();
            $table->integer('row')->nullable();
            $table->string('side', 1)->nullable();
            $table->integer('max_weight')->nullable();
            $table->decimal('index', 8, 5)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holds');
        Schema::dropIfExists('hold_positions');
    }
};
