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
        Schema::create('cargo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flight_id')->constrained()->cascadeOnDelete();
            $table->foreignId('container_id')->nullable()->constrained()->nullOnDelete();
            $table->string('awb_number')->unique(); // Air Waybill number
            $table->integer('pieces')->nullable();
            $table->integer('weight')->nullable();
            $table->string('type')->nullable(); // general, dangerous_goods, perishable, etc.
            $table->string('status')->default('accepted'); // accepted, loaded, offloaded
            $table->text('description')->nullable();
            $table->text('special_instructions')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cargo');
    }
};
