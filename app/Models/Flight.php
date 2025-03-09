<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Flight extends Model
{
    use HasFactory;

    protected $table = 'flights';

    protected $fillable = [
        'flight_number',
        'airline_id',
        'aircraft_id',
        'route_id',
        'scheduled_departure_time',
        'scheduled_arrival_time',
        'actual_departure_time',
        'actual_arrival_time',
        'status',
        'schedule_id',
    ];

    protected $casts = [
        'scheduled_departure_time' => 'datetime',
        'scheduled_arrival_time' => 'datetime',
        'actual_departure_time' => 'datetime',
        'actual_arrival_time' => 'datetime',
    ];

    /**
     * Get the departure airport code.
     * 
     * @return string|null
     */
    public function getDepartureAirportAttribute()
    {
        return $this->route ? $this->route->departureStation->code : null;
    }

    /**
     * Get the arrival airport code.
     * 
     * @return string|null
     */
    public function getArrivalAirportAttribute()
    {
        return $this->route ? $this->route->arrivalStation->code : null;
    }

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
            ->withPivot(['type', 'pieces', 'status', 'position_id', 'weight'])
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

    public function seats()
    {
        return $this->belongsToMany(Seat::class, 'flight_seats')
            ->withPivot('is_blocked', 'blocked_reason')
            ->withTimestamps();
    }

    public function settings(): MorphMany
    {
        return $this->morphMany(Setting::class, 'settingable');
    }

    public function getSetting(string $key, $default = null)
    {
        $setting = $this->settings()->where('key', $key)->first();
        if ($setting) {
            return $setting->typed_value;
        }

        $setting = $this->airline->settings()->where('key', $key)->first();

        return $setting ? $setting->typed_value : $default;
    }

    public function getDefaultSettings(): array
    {
        return [
            'notoc_required' => false,
            'passenger_weights' => 'Standard (88/70/35/0)',
            'trim_settings' => [
                'type' => 'Trim by Zone',
            ],
            'fuel_density' => 0.785,
        ];
    }

    public function getSettings(): array
    {
        $settings = $this->settings()->where('key', 'flight_settings')->first();

        return $settings ? $settings->typed_value : $this->getDefaultSettings();
    }

    public function updateSettings(array $settings): void
    {
        $this->settings()->updateOrCreate(
            [
                'key' => 'flight_settings',
                'airline_id' => $this->airline_id,
            ],
            [
                'value' => json_encode($settings),
                'type' => 'json',
                'description' => 'Flight Configuration Settings',
            ]
        );
    }

    /**
     * Get the flight schedule that generated this flight.
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }

    /**
     * Get the route associated with the flight.
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    /**
     * Get the departure station for this flight.
     */
    public function departureStation()
    {
        return $this->route ? $this->route->departureStation() : null;
    }

    /**
     * Get the arrival station for this flight.
     */
    public function arrivalStation()
    {
        return $this->route ? $this->route->arrivalStation() : null;
    }
}
