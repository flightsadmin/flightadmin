<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'airline_id',
        'aircraft_id',
        'flight_number',
        'departure_airport',
        'arrival_airport',
        'departure_time',
        'arrival_time',
        'start_date',
        'end_date',
        'days_of_week',
        'is_active',
    ];

    protected $casts = [
        'days_of_week' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'departure_time' => 'datetime',
        'arrival_time' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function airline(): BelongsTo
    {
        return $this->belongsTo(Airline::class);
    }

    public function aircraft(): BelongsTo
    {
        return $this->belongsTo(Aircraft::class);
    }

    public function flights(): HasMany
    {
        return $this->hasMany(Flight::class);
    }

    public function generateFlights(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? $this->start_date;
        $endDate = $endDate ?? $this->end_date;

        $createdFlightIds = [];
        $currentDate = Carbon::parse($startDate);

        while ($currentDate->lte($endDate)) {
            // Check if current day of week is in the schedule
            if (in_array($currentDate->dayOfWeek, $this->days_of_week)) {
                // Create departure and arrival datetime by combining current date with schedule times
                $departureDateTime = Carbon::parse($currentDate->format('Y-m-d').' '.$this->departure_time->format('H:i:s'));
                $arrivalDateTime = Carbon::parse($currentDate->format('Y-m-d').' '.$this->arrival_time->format('H:i:s'));

                // If arrival is before departure, it means it's the next day
                if ($arrivalDateTime->lt($departureDateTime)) {
                    $arrivalDateTime->addDay();
                }

                // Create the flight
                $flight = Flight::updateOrCreate(
                    [
                        'airline_id' => $this->airline_id,
                        'flight_number' => $this->flight_number,
                        'scheduled_departure_time' => $departureDateTime,
                    ],
                    [
                        'aircraft_id' => $this->aircraft_id,
                        'departure_airport' => $this->departure_airport,
                        'arrival_airport' => $this->arrival_airport,
                        'scheduled_arrival_time' => $arrivalDateTime,
                        'schedule_id' => $this->id,
                    ]
                );

                $createdFlightIds[] = $flight->id;
            }

            $currentDate->addDay();
        }

        return $createdFlightIds;
    }

    public function getDaysOfWeekNamesAttribute(): array
    {
        $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        return array_map(fn ($day) => $dayNames[$day], $this->days_of_week);
    }
}
