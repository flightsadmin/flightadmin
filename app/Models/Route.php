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
        'flight_time' => 'integer',
        'distance' => 'integer',
        'is_active' => 'boolean',
    ];

    public function airline(): BelongsTo
    {
        return $this->belongsTo(Airline::class);
    }

    public function departureStation(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'departure_station_id');
    }

    public function arrivalStation(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'arrival_station_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function emailNotifications(): HasMany
    {
        return $this->hasMany(EmailNotification::class);
    }

    public function flights()
    {
        return $this->hasMany(Flight::class);
    }
}
