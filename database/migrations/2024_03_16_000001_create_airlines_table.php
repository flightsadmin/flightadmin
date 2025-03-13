<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('airlines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('iata_code')->unique();
            $table->string('icao_code')->unique();
            $table->string('country');
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('stations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique();
            $table->string('name');
            $table->string('country')->nullable();
            $table->string('timezone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('airline_station', function (Blueprint $table) {
            $table->id();
            $table->foreignId('airline_id')->constrained()->onDelete('cascade');
            $table->foreignId('station_id')->constrained()->onDelete('cascade');
            $table->boolean('is_hub')->default(false);
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->timestamps();

            $table->unique(['airline_id', 'station_id']);
        });

        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('airline_id')->constrained()->onDelete('cascade');
            $table->foreignId('departure_station_id')->constrained('stations')->onDelete('cascade');
            $table->foreignId('arrival_station_id')->constrained('stations')->onDelete('cascade');
            $table->integer('flight_time')->nullable();
            $table->integer('distance')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['airline_id', 'departure_station_id', 'arrival_station_id']);
        });

        Schema::create('email_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('airline_id')->constrained()->onDelete('cascade');
            $table->foreignId('station_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('route_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('document_type');
            $table->json('email_addresses')->nullable();
            $table->json('sita_addresses')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['airline_id', 'station_id', 'route_id', 'document_type'], 'unique_notification_config');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('airlines');
        Schema::dropIfExists('stations');
        Schema::dropIfExists('airline_station');
        Schema::dropIfExists('routes');
        Schema::dropIfExists('email_notifications');
    }
};
