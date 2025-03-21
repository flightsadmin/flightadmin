<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Station extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'country',
        'timezone',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function airlines()
    {
        return $this->belongsToMany(Airline::class)
            ->withPivot('is_hub', 'contact_email', 'contact_phone')
            ->withTimestamps();
    }

    public function departureRoutes(): HasMany
    {
        return $this->hasMany(Route::class, 'departure_station_id');
    }

    public function arrivalRoutes(): HasMany
    {
        return $this->hasMany(Route::class, 'arrival_station_id');
    }

    public function emailNotifications(): HasMany
    {
        return $this->hasMany(EmailNotification::class);
    }
}
