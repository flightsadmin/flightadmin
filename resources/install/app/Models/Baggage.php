<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Baggage extends Model
{
    use HasFactory;

    protected $table = 'baggage';

    protected $fillable = [
        'flight_id',
        'passenger_id',
        'container_id',
        'tag_number',
        'weight',
        'status',
        'notes',
    ];

    protected $casts = [
        'weight' => 'integer',
    ];

    public function passenger(): BelongsTo
    {
        return $this->belongsTo(Passenger::class);
    }

    public function container(): BelongsTo
    {
        return $this->belongsTo(Container::class);
    }

    public function flight(): BelongsTo
    {
        return $this->belongsTo(Flight::class);
    }

    protected static function booted()
    {
        static::created(function ($baggage) {
            $baggage->container?->updateWeight();
        });

        static::updated(function ($baggage) {
            $baggage->container?->updateWeight();
        });

        static::deleted(function ($baggage) {
            $baggage->container?->updateWeight();
        });
    }
}
