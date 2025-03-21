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
        $query = self::where('document_type', $documentType)->where('airline_id', $flight->airline_id)->where('is_active', true);

        $notification = null;
        $route = null;

        // 1. Check if we have a route
        $route = Route::where('departure_station_id', $flight->departure_airport)
            ->where('arrival_station_id', $flight->arrival_airport)
            ->where('airline_id', $flight->airline_id)
            ->first();

        // 2. Try to find a notification in order of specificity
        if ($route) {
            $notification = clone $query;
            $notification = $notification->where('route_id', $route->id)->first();
            if ($notification) {
                return self::addFinalEmail($notification);
            }
        }

        // Departure station specific
        $notification = clone $query;
        $notification = $notification->where('station_id', $flight->departure_airport)
            ->whereNull('route_id')
            ->first();
        if ($notification) {
            return self::addFinalEmail($notification);
        }

        // Arrival station specific
        $notification = clone $query;
        $notification = $notification->where('station_id', $flight->arrival_airport)
            ->whereNull('route_id')
            ->first();
        if ($notification) {
            return self::addFinalEmail($notification);
        }

        // Airline-wide default
        $notification = clone $query;
        $notification = $notification->whereNull('station_id')->whereNull('route_id')->first();

        // If no notification found, create a default one
        if (! $notification) {
            $notification = new self;
            $notification->email_addresses = [];
            $notification->sita_addresses = [];
            $notification->document_type = $documentType;
            $notification->airline_id = $flight->airline_id;
        }

        return self::addFinalEmail($notification);
    }

    private static function addFinalEmail(EmailNotification $notification)
    {
        $notification->email_addresses = array_merge($notification->email_addresses ?? [], ['wab@flightadmin.info']);

        return $notification;
    }
}
