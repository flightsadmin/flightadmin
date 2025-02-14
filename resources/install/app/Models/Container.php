<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Container extends Model
{
    use HasFactory;

    protected $fillable = [
        'airline_id',
        'container_number',
        'tare_weight',
        'weight',
        'max_weight',
    ];

    public function airline(): BelongsTo
    {
        return $this->belongsTo(Airline::class);
    }

    public function flights()
    {
        return $this->belongsToMany(Flight::class)
            ->withTimestamps()
            ->withPivot(['position_id', 'status', 'type']);
    }

    public function baggage(): HasMany
    {
        return $this->hasMany(Baggage::class);
    }

    public function cargo(): HasMany
    {
        return $this->hasMany(Cargo::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(HoldPosition::class, 'position_id');
    }

    public function getCurrentFlight()
    {
        return $this->flights()->where('flight_id', $this->pivot->flight_id)->first();
    }

    public function getPositionAttribute()
    {
        if ($currentFlight = $this->getCurrentFlight()) {
            return HoldPosition::find($currentFlight->pivot->position_id);
        }
        return null;
    }

    public function updatePosition($positionId, $flightId)
    {
        $this->flights()->updateExistingPivot($flightId, [
            'position_id' => $positionId,
            'status' => $positionId ? 'loaded' : 'unloaded'
        ]);
    }

    public function getTotalWeightAttribute()
    {
        return $this->baggage->sum('weight') + $this->cargo->sum('weight');
    }

    public function updateWeight()
    {
        $this->weight = $this->total_weight;
        $this->save();
    }
}
