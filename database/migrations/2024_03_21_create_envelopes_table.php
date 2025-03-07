<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('envelopes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aircraft_type_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->json('points');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('envelopes');
    }
};
