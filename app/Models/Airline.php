<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Airline extends Model
{
    use HasFactory;

    protected $table = 'airlines';

    protected $fillable = [
        'name',
        'iata_code',
        'icao_code',
        'logo',
        'country',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the aircraft for this airline.
     */
    public function aircraft(): HasMany
    {
        return $this->hasMany(Aircraft::class);
    }

    /**
     * Get the flights for this airline.
     */
    public function flights(): HasMany
    {
        return $this->hasMany(Flight::class);
    }

    /**
     * Get the stations where this airline operates.
     */
    public function stations(): BelongsToMany
    {
        return $this->belongsToMany(Station::class, 'airline_station')
            ->withPivot(['is_hub', 'contact_email', 'contact_phone', 'notes'])
            ->withTimestamps();
    }

    /**
     * Get the routes operated by this airline.
     */
    public function routes(): HasMany
    {
        return $this->hasMany(Route::class);
    }

    /**
     * Get the email notifications for this airline.
     */
    public function emailNotifications(): HasMany
    {
        return $this->hasMany(EmailNotification::class);
    }

    public function settings(): HasMany
    {
        return $this->hasMany(Setting::class);
    }

    public function aircraftTypes(): HasMany
    {
        return $this->hasMany(AircraftType::class);
    }

    public function containers()
    {
        return $this->hasMany(Container::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getStandardPassengerWeight($type)
    {
        $settings = $this->getSettings('general');

        return match ($type) {
            'male' => (int) ($settings['standard_male_passenger_weight'] ?? 88),
            'female' => (int) ($settings['standard_female_passenger_weight'] ?? 70),
            'child' => (int) ($settings['standard_child_passenger_weight'] ?? 35),
            'infant' => (int) ($settings['standard_infant_passenger_weight'] ?? 10),
            default => (int) ($settings['standard_passenger_weight'] ?? 84),
        };
    }

    public function getStandardCockpitCrewWeight(): int
    {
        $settings = $this->getSettings('general');

        return (int) ($settings['standard_cockpit_crew_weight'] ?? 85);
    }

    public function getStandardCabinCrewWeight(): int
    {
        $settings = $this->getSettings('general');

        return (int) ($settings['standard_cabin_crew_weight'] ?? 75);
    }

    public function getStandardPantryWeight(): int
    {
        $settings = $this->getSettings('general');

        return (int) ($settings['standard_pantry_weight'] ?? 250);
    }

    public function getSettings($category = null)
    {
        $settings = $this->getSetting('airline_settings', []);

        return $category ? ($settings[$category] ?? []) : $settings;
    }

    public function updateSettings($category, $key, $value)
    {
        $settings = $this->getSettings();
        $settings[$category][$key] = $value;

        return $this->settings()->updateOrCreate(
            ['key' => 'airline_settings'],
            [
                'value' => json_encode($settings),
                'type' => 'json',
                'description' => 'Airline Configuration Settings',
            ]
        );
    }

    public function getSetting($key, $default = null)
    {
        $setting = $this->settings()->where('key', $key)->first();

        return $setting ? $setting->typed_value : $default;
    }
}
