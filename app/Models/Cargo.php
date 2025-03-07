<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cargo extends Model
{
    use HasFactory;

    protected $table = 'cargo';

    protected $fillable = [
        'flight_id',
        'container_id',
        'awb_number',
        'pieces',
        'weight',
        'type',
        'description',
        'status',
        'special_instructions',
    ];

    protected $casts = [
        'weight' => 'integer',
        'pieces' => 'integer',
    ];

    public function flight(): BelongsTo
    {
        return $this->belongsTo(Flight::class);
    }

    public function container(): BelongsTo
    {
        return $this->belongsTo(Container::class);
    }
}
