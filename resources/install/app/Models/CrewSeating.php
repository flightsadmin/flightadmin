<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrewSeating extends Model
{
    protected $table = 'crew_seating';

    protected $fillable = [
        'position',
        'location',
        'max_number',
        'arm',
        'index_per_kg',
    ];

    public function aircraftType(): BelongsTo
    {
        return $this->belongsTo(AircraftType::class);
    }
}
