<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'airline_id',
        'station_id',
        'route_id',
        'document_type',
        'email_addresses',
        'sita_addresses',
        'is_active',
    ];

    protected $casts = [
        'email_addresses' => 'array',
        'sita_addresses' => 'array',
        'is_active' => 'boolean',
    ];

    public function airline(): BelongsTo
    {
        return $this->belongsTo(Airline::class);
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public static function getRecipientsForFlight(Flight $flight, string $documentType)
    {
        // Start with a base query for the document type and airline
        $query = self::where('document_type', $documentType)
            ->where('airline_id', $flight->airline_id)
            ->where('is_active', true);

        // Try to find the most specific match in this order:
        // 1. Airline + Route + Station
        // 2. Airline + Route
        // 3. Airline + Departure Station
        // 4. Airline + Arrival Station
        // 5. Airline only

        // Check if we have a route-specific configuration
        $route = Route::where('departure_station_id', $flight->departure_airport)
            ->where('arrival_station_id', $flight->arrival_airport)
            ->where('airline_id', $flight->airline_id)
            ->first();

        if ($route) {
            $routeSpecific = clone $query;
            $routeSpecific->where('route_id', $route->id);
            $notification = $routeSpecific->first();

            if ($notification) {
                return $notification;
            }
        }

        // Check for departure station specific
        $departureSpecific = clone $query;
        $departureSpecific->where('station_id', $flight->departure_airport)
            ->whereNull('route_id');
        $notification = $departureSpecific->first();

        if ($notification) {
            return $notification;
        }

        // Check for arrival station specific
        $arrivalSpecific = clone $query;
        $arrivalSpecific->where('station_id', $flight->arrival_airport)
            ->whereNull('route_id');
        $notification = $arrivalSpecific->first();

        if ($notification) {
            return $notification;
        }

        // Finally, check for airline-wide default
        $airlineDefault = clone $query;
        $airlineDefault->whereNull('station_id')
            ->whereNull('route_id');
        $notification = $airlineDefault->first();

        return $notification;
    }
}
