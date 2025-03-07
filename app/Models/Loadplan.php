<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loadplan extends Model
{
    use HasFactory;

    protected $fillable = [
        'flight_id',
        'status', // draft, released, archived
        'version',
        'loading', // JSON field storing container positions
        'last_modified_by',
        'released_at',
        'released_by',
    ];

    protected $casts = [
        'loading' => 'array',
        'released_at' => 'datetime',
    ];

    public function flight()
    {
        return $this->belongsTo(Flight::class);
    }

    public function modifier()
    {
        return $this->belongsTo(User::class, 'last_modified_by');
    }

    public function releaser()
    {
        return $this->belongsTo(User::class, 'released_by');
    }
}
