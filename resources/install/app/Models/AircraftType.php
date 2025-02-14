<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AircraftType extends Model
{
    use HasFactory;

    protected $fillable = [
        'airline_id',
        'code',
        'name',
        'manufacturer',
        'max_passengers',
        'cargo_capacity',
        'max_fuel_capacity',
        'empty_weight',
        'max_zero_fuel_weight',
        'max_takeoff_weight',
        'max_landing_weight',
        'category',
        'max_deck_crew',
        'max_cabin_crew',
    ];

    /**
     * Get the settings for this aircraft type
     */
    public function settings()
    {
        return $this->morphMany(Setting::class, 'settingable');
    }

    /**
     * Get the aircraft of this type
     */
    public function aircraft(): HasMany
    {
        return $this->hasMany(Aircraft::class);
    }

    /**
     * Get the holds for this aircraft type
     */
    public function holds(): HasMany
    {
        return $this->hasMany(Hold::class)->orderBy('position');
    }

    /**
     * Get all available positions in all holds
     */
    public function getAllPositions()
    {
        return $this->holds
            ->where('is_active', true)
            ->flatMap(fn($hold) => $hold->getPositions())
            ->values();
    }

    public function getSetting($key, $default = null)
    {
        return $this->settings()->where('key', $key)->first()?->typed_value ?? $default;
    }

    public function airline(): BelongsTo
    {
        return $this->belongsTo(Airline::class);
    }

    public function airlines(): BelongsToMany
    {
        return $this->belongsToMany(Airline::class)->withTimestamps();
    }

    /**
     * Get all active airlines using this aircraft type
     */
    public function activeAirlines(): BelongsToMany
    {
        return $this->airlines()->wherePivot('is_active', true);
    }

    public function cabinZones(): HasMany
    {
        return $this->hasMany(CabinZone::class)->orderBy('index');
    }

    public function crewSeating(): HasMany
    {
        return $this->hasMany(CrewSeating::class);
    }

    public function crewDistributions(): HasMany
    {
        return $this->hasMany(CrewDistribution::class);
    }

    public function envelopes(): HasMany
    {
        return $this->hasMany(Envelope::class);
    }

    public function getPantryDetails($pantryCode)
    {
        $pantryCode = strtolower($pantryCode);

        return [
            'name' => $this->getSetting("pantry_{$pantryCode}_name", ''),
            'code' => strtoupper($pantryCode),
            'weight' => $this->getSetting("pantry_{$pantryCode}_weight", 0),
            'index' => number_format($this->getSetting("pantry_{$pantryCode}_index", 0), 2),
        ];
    }

    public function getAllPantries()
    {
        return collect($this->settings()
            ->where('key', 'like', 'pantry_%_name')
            ->get())
            ->map(function ($setting) {
                $code = str_replace(['pantry_', '_name'], '', $setting->key);

                return array_merge(
                    ['code' => $code],
                    $this->getPantryDetails($code)
                );
            });
    }

    public function seats(): HasMany
    {
        return $this->hasMany(Seat::class);
    }

    public function getFuelIndex($weight)
    {
        $envelope = $this->envelopes()->where('name', 'FUEL')->first();

        if (!$envelope || !$envelope->points) {
            return 0;
        }

        $points = collect($envelope->points);

        $exactMatch = $points->firstWhere('weight', $weight);
        if ($exactMatch) {
            return $exactMatch['index'];
        }

        return $points->sortBy(function ($point) use ($weight) {
            return abs($point['weight'] - $weight);
        })->first()['index'];
    }

    public function getFuelIndexes($takeoffFuel, $landingFuel)
    {
        return [
            'takeoff' => $this->getFuelIndex($takeoffFuel),
            'landing' => $this->getFuelIndex($landingFuel),
        ];
    }

    public function calculateMac($weight, $index)
    {
        $K = $this->getSetting('k_constant', 50);
        $C = $this->getSetting('c_constant', 1000);
        $MAC = $this->getSetting('length_of_mac', 4.194);
        $LEMAC = $this->getSetting('lemac_at', 17.8015);
        $REF_STA = $this->getSetting('ref_sta_at', 18.850);

        // Formula: %MAC = ((C * (I-K))/W + Ref.Sta - LEMAC) / (MAC/100)
        return (
            ((($C * ($index - $K)) / $weight + $REF_STA - $LEMAC) / ($MAC / 100))
        );
    }

    public function getZfwMac($weight, $index)
    {
        return $this->calculateMac($weight, $index);
    }

    public function getTowMac($weight, $index)
    {
        return $this->calculateMac($weight, $index);
    }

    public function getLdwMac($weight, $index)
    {
        return $this->calculateMac($weight, $index);
    }

    public function getCrewIndex($deckCrewCount, $cabinCrewCount, $deckCrewWeight, $cabinCrewWeight)
    {
        $deckCrewSeating = $this->crewSeating()->where('position', 'deck_crew')->first();

        $deckCrewIndex = $deckCrewCount * $deckCrewWeight * $deckCrewSeating->index_per_kg;

        $distribution = $this->crewDistributions()->where('crew_count', $cabinCrewCount)->first();

        if (!$distribution) {
            return $deckCrewIndex;
        }

        $cabinCrewIndex = 0;
        $cabinSeating = $this->crewSeating()->where('position', 'cabin_crew')->get();
        foreach ($distribution->distribution as $index => $crewCount) {
            if ($crewCount > 0 && isset($cabinSeating[$index])) {
                $seating = $cabinSeating[$index];
                $locationIndex = $crewCount * $cabinCrewWeight * $seating->index_per_kg;
                $cabinCrewIndex += $locationIndex;
            }
        }
        return $deckCrewIndex + $cabinCrewIndex;
    }

    public function getCrewIndexes($crewMembers)
    {
        $crewMembers = explode('/', $crewMembers);
        $deckCrewCount = (int) $crewMembers[0];
        $cabinCrewCount = (int) $crewMembers[1];

        $deckCrewWeight = $this->getSetting('standard_cockpit_crew_weight', 85);
        $cabinCrewWeight = $this->getSetting('standard_cabin_crew_weight', 75);

        return [
            'index' => $this->getCrewIndex($deckCrewCount, $cabinCrewCount, $deckCrewWeight, $cabinCrewWeight),
            'weight' => ($deckCrewCount * $deckCrewWeight) + ($cabinCrewCount * $cabinCrewWeight),
        ];
    }
}
