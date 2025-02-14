<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Aircraft extends Model
{
    use HasFactory;

    protected $table = 'aircraft';

    protected $fillable = [
        'airline_id',
        'aircraft_type_id',
        'registration_number',
        'basic_weight',
        'basic_index',
        'active',
        'remarks',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function airline(): BelongsTo
    {
        return $this->belongsTo(Airline::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(AircraftType::class, 'aircraft_type_id');
    }

    public function flights(): HasMany
    {
        return $this->hasMany(Flight::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function getRemainingFuelCapacity($currentFuel): float
    {
        return $this->type->max_fuel_capacity - $currentFuel;
    }

    public function isWithinWeightLimits($totalWeight): bool
    {
        return $totalWeight <= $this->type->max_takeoff_weight;
    }

    public function getAllPositions()
    {
        return $this->type->getAllPositions();
    }
}
