<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrewDistribution extends Model
{
    protected $fillable = [
        'crew_count',
        'distribution',
    ];

    protected $casts = [
        'distribution' => 'array',
    ];

    public function aircraftType(): BelongsTo
    {
        return $this->belongsTo(AircraftType::class);
    }
}
