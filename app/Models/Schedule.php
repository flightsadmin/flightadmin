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
        'route_id',
        'flight_number',
        'scheduled_departure_time',
        'scheduled_arrival_time',
        'days_of_week',
        'start_date',
        'end_date',
        'aircraft_type_id',
        'status',
    ];

    protected $casts = [
        'days_of_week' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'scheduled_departure_time' => 'datetime',
        'scheduled_arrival_time' => 'datetime',
    ];

    /**
     * Get the airline that owns the schedule.
     */
    public function airline(): BelongsTo
    {
        return $this->belongsTo(Airline::class);
    }

    /**
     * Get the route associated with the schedule.
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    /**
     * Get the aircraft type for this schedule.
     */
    public function aircraftType(): BelongsTo
    {
        return $this->belongsTo(AircraftType::class);
    }

    /**
     * Get the flights for this schedule.
     */
    public function flights(): HasMany
    {
        return $this->hasMany(Flight::class);
    }

    /**
     * Get the departure airport code.
     * 
     * @return string|null
     */
    public function getDepartureAirportAttribute()
    {
        return $this->route ? $this->route->departureStation->code : null;
    }

    /**
     * Get the arrival airport code.
     * 
     * @return string|null
     */
    public function getArrivalAirportAttribute()
    {
        return $this->route ? $this->route->arrivalStation->code : null;
    }

    public function generateFlights(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        if (!$this->route) {
            throw new \Exception('Cannot generate flights without a route');
        }

        $startDate = $startDate ?? $this->start_date;
        $endDate = $endDate ?? $this->end_date;

        $createdFlightIds = [];
        $currentDate = Carbon::parse($startDate);

        while ($currentDate->lte($endDate)) {
            // Check if current day of week is in the schedule
            if (in_array($currentDate->dayOfWeek, $this->days_of_week)) {
                // Create departure and arrival datetime by combining current date with schedule times
                $departureDateTime = Carbon::parse($currentDate->format('Y-m-d') . ' ' . $this->scheduled_departure_time->format('H:i:s'));
                $arrivalDateTime = Carbon::parse($currentDate->format('Y-m-d') . ' ' . $this->scheduled_arrival_time->format('H:i:s'));

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
                        'route_id' => $this->route_id,
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

        return array_map(fn($day) => $dayNames[$day], $this->days_of_week);
    }
}
