<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loadsheet extends Model
{
    protected $fillable = [
        'flight_id',
        'payload_distribution',
        'edition',
        'final',
        'created_by',
        'released_by',
        'released_at',
    ];

    protected $casts = [
        'payload_distribution' => 'array',
        'final' => 'boolean',
        'released_at' => 'datetime',
    ];

    public function flight(): BelongsTo
    {
        return $this->belongsTo(Flight::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function releaser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by');
    }
}
