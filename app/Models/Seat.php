<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Seat extends Model
{
    protected $fillable = [
        'aircraft_type_id',
        'cabin_zone_id',
        'row',
        'column',
        'designation',
        'type',
        'is_exit',
        'is_blocked',
        'notes',
    ];

    protected $casts = [
        'is_exit' => 'boolean',
        'is_blocked' => 'boolean',
    ];

    public function aircraftType(): BelongsTo
    {
        return $this->belongsTo(AircraftType::class);
    }

    public function cabinZone(): BelongsTo
    {
        return $this->belongsTo(CabinZone::class);
    }

    public function passenger($flightId = null)
    {
        $query = $this->hasOne(Passenger::class);

        if ($flightId) {
            $query->where('flight_id', $flightId);
        } elseif (request()->flight) {
            $query->where('flight_id', request()->flight->id);
        }

        return $query;
    }

    public function flights(): BelongsToMany
    {
        return $this->belongsToMany(Flight::class, 'flight_seats')
            ->withPivot('is_blocked', 'blocked_reason')
            ->withTimestamps();
    }

    public function scopeAvailable($query, $flight)
    {
        return $query->whereDoesntHave('passenger', function ($q) use ($flight) {
            $q->where('flight_id', $flight->id);
        })->whereDoesntHave('flights', function ($q) use ($flight) {
            $q->where('flights.id', $flight->id)
                ->where('flight_seats.is_blocked', true);
        });
    }

    public function isAvailable($flight)
    {
        return ! $this->passenger($flight->id)->exists() &&
            ! $this->flights()
                ->where('flights.id', $flight->id)
                ->where('flight_seats.is_blocked', true)
                ->exists();
    }
}
