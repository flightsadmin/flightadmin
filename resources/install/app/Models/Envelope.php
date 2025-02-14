<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Envelope extends Model
{
    protected $fillable = [
        'aircraft_type_id',
        'name',
        'points',
        'is_active',
    ];

    protected $casts = [
        'points' => 'array',
        'is_active' => 'boolean',
    ];

    public function aircraftType(): BelongsTo
    {
        return $this->belongsTo(AircraftType::class);
    }
}
