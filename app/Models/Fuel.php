<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fuel extends Model
{
    use HasFactory;

    protected $table = 'fuel';

    protected $fillable = [
        'flight_id',
        'block_fuel',
        'take_off_fuel',
        'taxi_fuel',
        'trip_fuel',
        'crew',
        'pantry',
    ];

    protected $casts = [
        'taxi_fuel' => 'integer',
        'trip_fuel' => 'integer',
    ];

    public function flight(): BelongsTo
    {
        return $this->belongsTo(Flight::class);
    }
}
