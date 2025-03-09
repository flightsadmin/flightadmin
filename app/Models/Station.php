<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Station extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'country',
        'timezone',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the airlines that operate at this station.
     */
    public function airlines(): BelongsToMany
    {
        return $this->belongsToMany(Airline::class, 'airline_station')
            ->withPivot(['is_hub', 'contact_email', 'contact_phone', 'notes'])
            ->withTimestamps();
    }

    /**
     * Get the routes that originate from this station.
     */
    public function departureRoutes(): HasMany
    {
        return $this->hasMany(Route::class, 'departure_station_id');
    }

    /**
     * Get the routes that arrive at this station.
     */
    public function arrivalRoutes(): HasMany
    {
        return $this->hasMany(Route::class, 'arrival_station_id');
    }

    /**
     * Get the email notifications for this station.
     */
    public function emailNotifications(): HasMany
    {
        return $this->hasMany(EmailNotification::class);
    }
}