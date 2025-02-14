<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    use HasFactory;

    protected $table = 'flights';

    protected $fillable = [
        'flight_number',
        'airline_id',
        'aircraft_id',
        'departure_airport',
        'arrival_airport',
        'scheduled_departure_time',
        'scheduled_arrival_time',
        'status',
    ];

    protected $casts = [
        'scheduled_departure_time' => 'datetime',
        'scheduled_arrival_time' => 'datetime',
    ];

    // Relationships
    public function baggage()
    {
        return $this->hasMany(Baggage::class);
    }

    public function crews()
    {
        return $this->belongsToMany(Crew::class, 'crew_flight')->withTimestamps();
    }

    public function passengers()
    {
        return $this->hasMany(Passenger::class);
    }

    public function containers()
    {
        return $this->belongsToMany(Container::class)
            ->withPivot(['type', 'pieces', 'status', 'position_id'])
            ->withTimestamps();
    }

    public function airline()
    {
        return $this->belongsTo(Airline::class);
    }

    public function aircraft()
    {
        return $this->belongsTo(Aircraft::class);
    }

    public function crew()
    {
        return $this->belongsToMany(Crew::class, 'crew_flight')->withTimestamps();
    }

    public function cargo()
    {
        return $this->hasMany(Cargo::class);
    }

    public function fuel()
    {
        return $this->hasOne(Fuel::class);
    }

    public function loadsheets()
    {
        return $this->hasMany(Loadsheet::class);
    }

    public function getTotalPassengerWeight(): float
    {
        return $this->passengers()->count() * $this->airline->getStandardPassengerWeight();
    }

    public function getTotalBaggageWeight(): float
    {
        return $this->baggage()->sum('weight');
    }

    public function getTotalCargoWeight(): float
    {
        return $this->cargo()->sum('weight');
    }

    public function getTotalCrewWeight(): float
    {
        return $this->crew()->count() * $this->airline->getStandardCockpitCrewWeight();
    }

    public function calculateTotalWeight(): float
    {
        return $this->aircraft->empty_weight +
            $this->getTotalPassengerWeight() +
            $this->getTotalBaggageWeight() +
            $this->getTotalCargoWeight() +
            $this->getTotalCrewWeight() +
            ($this->fuel ? $this->fuel->block_fuel : 0);
    }

    public function isWithinWeightLimits(): bool
    {
        return $this->aircraft->isWithinWeightLimits($this->calculateTotalWeight());
    }

    public function loadAllCounts()
    {
        return $this->loadCount([
            'baggage',
            'cargo',
            'passengers',
            'crew',
            'containers',
        ]);
    }

    public function loadplans()
    {
        return $this->hasMany(Loadplan::class);
    }

    public function latestLoadplan()
    {
        return $this->hasOne(Loadplan::class)->latestOfMany();
    }
}
