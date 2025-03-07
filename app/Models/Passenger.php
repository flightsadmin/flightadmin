<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Passenger extends Model
{
    use HasFactory;

    protected $table = 'passengers';

    protected $fillable = [
        'flight_id',
        'seat_id',
        'name',
        'type',
        'pnr',
        'ticket_number',
        'acceptance_status',
        'boarding_status',
        'special_requirements',
        'documents',
    ];

    protected $casts = [
        'special_requirements' => 'array',
        'documents' => 'array',
    ];

    protected $attributes = [
        'special_requirements' => '{"wchr": false, "wchs": false, "wchc": false, "exst": false, "stcr": false, "deaf": false, "blind": false, "dpna": false, "meda": false, "infant": false}',
        'documents' => '{"travel_documents": [], "visas": []}',
    ];

    public function flight(): BelongsTo
    {
        return $this->belongsTo(Flight::class);
    }

    public function baggage(): HasMany
    {
        return $this->hasMany(Baggage::class);
    }

    public function seat(): BelongsTo
    {
        return $this->belongsTo(Seat::class);
    }

    public function getFlightSeatAttribute()
    {
        return $this->seat?->designation ?? 'No Seat';
    }
}
