<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CabinZone extends Model
{
    use HasFactory;

    protected $fillable = [
        'aircraft_type_id',
        'name',
        'max_capacity',
        'index',
        'arm',
    ];

    protected $casts = [
        'max_capacity' => 'integer',
        'index' => 'float',
        'arm' => 'float',
    ];

    public function aircraftType(): BelongsTo
    {
        return $this->belongsTo(AircraftType::class);
    }

    public function seats(): HasMany
    {
        return $this->hasMany(Seat::class);
    }
}
