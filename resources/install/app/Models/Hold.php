<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Hold extends Model
{
    use HasFactory;

    protected $fillable = [
        'aircraft_type_id',
        'name',
        'code',
        'position',
        'max_weight',
        'index',
        'is_active',
    ];

    protected $casts = [
        'max_weight' => 'integer',
        'index' => 'float',
        'is_active' => 'boolean',
    ];

    public function aircraftType(): BelongsTo
    {
        return $this->belongsTo(AircraftType::class);
    }

    public function positions(): HasMany
    {
        return $this->hasMany(HoldPosition::class)->orderBy('row')->orderBy('side');
    }

    public function containers(): HasManyThrough
    {
        return $this->hasManyThrough(
            Container::class,
            HoldPosition::class,
            'hold_id', // Foreign key on hold_positions table
            'position_id', // Foreign key on containers table
            'id', // Local key on holds table
            'id'  // Local key on hold_positions table
        );
    }

    public function getCurrentWeight($containerPositions, $containers)
    {
        return collect($containerPositions)
            ->filter(function ($containerData) {
                // Check if the container is in this hold
                return $containerData['hold_name'] === $this->name;
            })
            ->sum('weight');
    }

    public function isOverweight($currentWeight)
    {
        return $currentWeight > $this->max_weight;
    }
}
