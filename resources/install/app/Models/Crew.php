<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Crew extends Model
{
    use HasFactory;

    protected $table = 'crews';

    protected $fillable = [
        'employee_id',
        'name',
        'position',
        'email',
        'notes',
    ];

    public function flights()
    {
        return $this->belongsToMany(Flight::class, 'crew_flight')->withTimestamps();
    }
}
