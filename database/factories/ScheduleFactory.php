<?php

namespace Database\Factories;

use App\Models\Airline;
use App\Models\AircraftType;
use App\Models\Route;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Schedule>
 */
class ScheduleFactory extends Factory
{
    protected $model = Schedule::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Try to get a valid route from the database
        $route = $this->getRandomRoute();

        // If no routes exist, we need to create one
        if (!$route) {
            $airline = Airline::inRandomOrder()->first();
            if (!$airline) {
                $airline = Airline::factory()->create();
            }

            // Create a route if none exists
            $route = Route::factory()->create([
                'airline_id' => $airline->id
            ]);
        }

        // Generate a random flight number
        $flightNumber = $route->airline->iata_code . fake()->numberBetween(100, 999);

        // Generate random days of week (1-7, where 1 is Monday)
        $daysOfWeek = [];
        for ($i = 1; $i <= 7; $i++) {
            if (fake()->boolean(70)) { // 70% chance to include each day
                $daysOfWeek[] = $i;
            }
        }

        // Ensure at least one day is selected
        if (empty($daysOfWeek)) {
            $daysOfWeek[] = fake()->numberBetween(1, 7);
        }

        // Generate start and end dates
        $startDate = fake()->dateTimeBetween('now', '+1 month');
        $endDate = fake()->dateTimeBetween('+1 months', '+3 months');

        // Generate departure and arrival times
        $departureTime = fake()->dateTimeBetween('08:00', '20:00');

        // Calculate arrival time based on flight time from route
        $flightTimeMinutes = $route->flight_time;
        $arrivalTime = (clone $departureTime)->modify("+{$flightTimeMinutes} minutes");

        // Get a random aircraft type for this airline
        $aircraftType = AircraftType::where('airline_id', $route->airline_id)->inRandomOrder()->first();

        // If no aircraft type exists, create one
        if (!$aircraftType) {
            $aircraftType = AircraftType::factory()->create([
                'airline_id' => $route->airline_id
            ]);
        }

        return [
            'airline_id' => $route->airline_id,
            'route_id' => $route->id,
            'flight_number' => $flightNumber,
            'days_of_week' => $daysOfWeek,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'scheduled_departure_time' => $departureTime,
            'scheduled_arrival_time' => $arrivalTime,
            'aircraft_type_id' => $aircraftType->id,
            'is_active' => true,
        ];
    }

    /**
     * Configure the factory to create a schedule for a specific airline.
     */
    public function forAirline(Airline $airline)
    {
        return $this->state(function (array $attributes) use ($airline) {
            // Try to get a route for this specific airline
            $route = $this->getRandomRouteForAirline($airline->id);

            // If no routes exist for this airline, create one
            if (!$route) {
                $route = Route::factory()->create([
                    'airline_id' => $airline->id
                ]);
            }

            // Get a random aircraft type for this airline
            $aircraftType = AircraftType::where('airline_id', $airline->id)->inRandomOrder()->first();

            // If no aircraft type exists, create one
            if (!$aircraftType) {
                $aircraftType = AircraftType::factory()->create([
                    'airline_id' => $airline->id
                ]);
            }

            return [
                'airline_id' => $airline->id,
                'route_id' => $route->id,
                'aircraft_type_id' => $aircraftType->id,
            ];
        });
    }

    /**
     * Configure the factory to create a schedule for a specific route.
     */
    public function forRoute(Route $route)
    {
        return $this->state(function (array $attributes) use ($route) {
            // Get a random aircraft type for this airline
            $aircraftType = AircraftType::where('airline_id', $route->airline_id)->inRandomOrder()->first();

            // If no aircraft type exists, create one
            if (!$aircraftType) {
                $aircraftType = AircraftType::factory()->create([
                    'airline_id' => $route->airline_id
                ]);
            }

            // Generate departure and arrival times
            $departureTime = fake()->dateTimeBetween('08:00', '20:00');

            // Calculate arrival time based on flight time from route
            $flightTimeMinutes = $route->flight_time;
            $arrivalTime = (clone $departureTime)->modify("+{$flightTimeMinutes} minutes");

            return [
                'airline_id' => $route->airline_id,
                'route_id' => $route->id,
                'scheduled_departure_time' => $departureTime,
                'scheduled_arrival_time' => $arrivalTime,
                'aircraft_type_id' => $aircraftType->id,
            ];
        });
    }

    /**
     * Get a random route from the database.
     */
    private function getRandomRoute()
    {
        return Route::with(['departureStation', 'arrivalStation'])
            ->inRandomOrder()
            ->first();
    }

    /**
     * Get a random route for a specific airline.
     */
    private function getRandomRouteForAirline($airlineId)
    {
        return Route::with(['departureStation', 'arrivalStation'])
            ->where('airline_id', $airlineId)
            ->inRandomOrder()
            ->first();
    }
}
