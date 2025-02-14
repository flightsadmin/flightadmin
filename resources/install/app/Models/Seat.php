<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function passenger(): HasOne
    {
        return $this->hasOne(Passenger::class);
    }

    public function scopeAvailable($query)
    {
        return $query->whereDoesntHave('passenger')
            ->where('is_blocked', false);
    }
}
