<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loadsheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flight_id')->constrained()->cascadeOnDelete();
            $table->json('distribution');
            $table->integer('edition')->default(1);
            $table->boolean('final')->default(false);
            $table->string('status', 20)->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('released_by')->nullable()->constrained('users');
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loadsheets');
    }
};
