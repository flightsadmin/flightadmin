<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Route extends Model
{
    use HasFactory;

    protected $fillable = [
        'airline_id',
        'departure_station_id',
        'arrival_station_id',
        'flight_time',
        'distance',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'flight_time' => 'integer', // in minutes
        'distance' => 'integer', // in kilometers
        'is_active' => 'boolean',
    ];

    /**
     * Get the airline that operates this route.
     */
    public function airline(): BelongsTo
    {
        return $this->belongsTo(Airline::class);
    }

    /**
     * Get the departure station for this route.
     */
    public function departureStation(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'departure_station_id');
    }

    /**
     * Get the arrival station for this route.
     */
    public function arrivalStation(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'arrival_station_id');
    }

    /**
     * Get the schedules for this route.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * Get the email notifications for this route.
     */
    public function emailNotifications(): HasMany
    {
        return $this->hasMany(EmailNotification::class);
    }
}