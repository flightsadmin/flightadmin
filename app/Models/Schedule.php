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
        'aircraft_type_id',
        'route_id',
        'flight_number',
        'scheduled_departure_time',
        'scheduled_arrival_time',
        'start_date',
        'end_date',
        'days_of_week',
        'is_active',
    ];

    protected $casts = [
        'scheduled_departure_time' => 'datetime',
        'scheduled_arrival_time' => 'datetime',
        'start_date' => 'date',
        'end_date' => 'date',
        'days_of_week' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the airline that owns the schedule.
     */
    public function airline(): BelongsTo
    {
        return $this->belongsTo(Airline::class);
    }

    /**
     * Get the aircraft type for this schedule.
     */
    public function aircraftType(): BelongsTo
    {
        return $this->belongsTo(AircraftType::class);
    }

    /**
     * Get the route for this schedule.
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    /**
     * Get the flights for this schedule.
     */
    public function flights(): HasMany
    {
        return $this->hasMany(Flight::class);
    }

    /**
     * Generate flights based on this schedule.
     */
    public function generateFlights()
    {
        $createdFlights = [];
        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $dayOfWeek = (int) $currentDate->format('w'); // 0 (Sunday) to 6 (Saturday)

            if (in_array($dayOfWeek, $this->days_of_week)) {
                // Create a flight for this day
                $departureTime = Carbon::parse($currentDate->format('Y-m-d').' '.$this->scheduled_departure_time->format('H:i:s'));
                $arrivalTime = Carbon::parse($currentDate->format('Y-m-d').' '.$this->scheduled_arrival_time->format('H:i:s'));

                // Handle overnight flights
                if ($arrivalTime->lt($departureTime)) {
                    $arrivalTime->addDay();
                }

                // Check if flight already exists
                $existingFlight = Flight::where('schedule_id', $this->id)
                    ->whereDate('scheduled_departure_time', $currentDate)
                    ->first();

                if (! $existingFlight) {
                    // Create flight data without aircraft_type_id since it doesn't exist in the flights table
                    $flightData = [
                        'airline_id' => $this->airline_id,
                        'aircraft_id' => null, // Explicitly set to null since aircraft is not known yet
                        'route_id' => $this->route_id,
                        'schedule_id' => $this->id,
                        'flight_number' => $this->flight_number,
                        'scheduled_departure_time' => $departureTime,
                        'scheduled_arrival_time' => $arrivalTime,
                        'status' => 'scheduled',
                    ];

                    $flight = Flight::create($flightData);
                    $createdFlights[] = $flight->id;
                }
            }

            $currentDate->addDay();
        }

        return $createdFlights;
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

    public function getDaysOfWeekNamesAttribute(): array
    {
        $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        return array_map(fn ($day) => $dayNames[$day], $this->days_of_week);
    }

    public function deleteWithFlights(bool $deleteAllFlights = false): array
    {
        $result = [
            'success' => true,
            'message' => '',
            'deleted_flights' => 0,
            'preserved_flights' => 0,
        ];

        try {
            \DB::beginTransaction();

            // Get flights associated with this schedule
            $flights = $this->flights();

            // If not deleting all flights, only delete future flights
            if (! $deleteAllFlights) {
                $flights = $flights->where('scheduled_departure_time', '>', now());
            }

            // Count flights that will be deleted
            $flightsToDelete = $flights->count();
            $result['deleted_flights'] = $flightsToDelete;

            // Count flights that will be preserved (if not deleting all)
            if (! $deleteAllFlights) {
                $result['preserved_flights'] = $this->flights()->where('scheduled_departure_time', '<=', now())->count();
            }

            // Delete the flights
            $flights->delete();

            // Delete the schedule
            $this->delete();

            // Commit the transaction
            \DB::commit();

            $result['message'] = "Schedule deleted successfully with {$result['deleted_flights']} flights.";
            if ($result['preserved_flights'] > 0) {
                $result['message'] .= " {$result['preserved_flights']} past flights were preserved.";
            }
        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            \DB::rollBack();

            $result['success'] = false;
            $result['message'] = "Failed to delete schedule: {$e->getMessage()}";
        }

        return $result;
    }
}
