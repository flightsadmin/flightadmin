<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Airline extends Model
{
    use HasFactory;

    protected $table = 'airlines';

    protected $fillable = [
        'name',
        'iata_code',
        'icao_code',
        'country',
        'address',
        'phone',
        'email',
        'description',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public const STANDARD_PASSENGER_WEIGHT = 'standard_passenger_weight';

    public const STANDARD_MALE_PASSENGER_WEIGHT = 'standard_male_passenger_weight';

    public const STANDARD_FEMALE_PASSENGER_WEIGHT = 'standard_female_passenger_weight';

    public const STANDARD_CHILD_PASSENGER_WEIGHT = 'standard_child_passenger_weight';

    public const STANDARD_INFANT_PASSENGER_WEIGHT = 'standard_infant_passenger_weight';

    public const STANDARD_COCKPIT_CREW_WEIGHT = 'standard_cockpit_crew_weight';

    public const STANDARD_CABIN_CREW_WEIGHT = 'standard_cabin_crew_weight';

    public const STANDARD_PANTRY_WEIGHT = 'standard_pantry_weight';

    public function aircraft(): HasMany
    {
        return $this->hasMany(Aircraft::class);
    }

    public function flights(): HasMany
    {
        return $this->hasMany(Flight::class);
    }

    public function settings(): HasMany
    {
        return $this->hasMany(Setting::class);
    }

    public function aircraftTypes(): HasMany
    {
        return $this->hasMany(AircraftType::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function getStandardPassengerWeight($type)
    {
        return match ($type) {
            'male' => (int) ($this->settings()->where('key', self::STANDARD_MALE_PASSENGER_WEIGHT)->first()->value ?? 88),
            'female' => (int) ($this->settings()->where('key', self::STANDARD_FEMALE_PASSENGER_WEIGHT)->first()->value ?? 70),
            'child' => (int) ($this->settings()->where('key', self::STANDARD_CHILD_PASSENGER_WEIGHT)->first()->value ?? 35),
            'infant' => (int) ($this->settings()->where('key', self::STANDARD_INFANT_PASSENGER_WEIGHT)->first()->value ?? 10),
            default => (int) ($this->settings()->where('key', self::STANDARD_PASSENGER_WEIGHT)->first()->value ?? 84),
        };
    }

    public function getStandardCockpitCrewWeight(): int
    {
        return (int) ($this->settings()->where('key', self::STANDARD_COCKPIT_CREW_WEIGHT)->first()->value ?? 85);
    }

    public function getStandardCabinCrewWeight(): int
    {
        return (int) ($this->settings()->where('key', self::STANDARD_CABIN_CREW_WEIGHT)->first()->value ?? 75);
    }

    public function getStandardPantryWeight(): int
    {
        return (int) ($this->settings()->where('key', self::STANDARD_PANTRY_WEIGHT)->first()->value ?? 250);
    }
}
