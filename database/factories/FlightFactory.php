<?php

namespace Database\Factories;

use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Flight;
use App\Models\Route;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Flight>
 */
class FlightFactory extends Factory
{
    protected $model = Flight::class;

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

        // Generate scheduled times
        $scheduledDeparture = fake()->dateTimeBetween('+1 day', '+1 month');

        // Calculate scheduled arrival based on flight time from route
        $flightTimeMinutes = $route->flight_time;
        $scheduledArrival = (clone $scheduledDeparture)->modify("+{$flightTimeMinutes} minutes");

        return [
            'airline_id' => $route->airline_id,
            'aircraft_id' => null, // Will be set by the caller
            'route_id' => $route->id,
            'flight_number' => $flightNumber,
            'scheduled_departure_time' => $scheduledDeparture,
            'scheduled_arrival_time' => $scheduledArrival,
            'status' => fake()->randomElement(['scheduled', 'boarding', 'departed', 'arrived', 'delayed', 'cancelled']),
        ];
    }

    /**
     * Configure the factory to create a flight for a specific airline.
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

            // Generate scheduled times
            $scheduledDeparture = fake()->dateTimeBetween('+1 day', '+1 month');

            // Calculate scheduled arrival based on flight time from route
            $flightTimeMinutes = $route->flight_time;
            $scheduledArrival = (clone $scheduledDeparture)->modify("+{$flightTimeMinutes} minutes");

            return [
                'airline_id' => $airline->id,
                'route_id' => $route->id,
                'scheduled_departure_time' => $scheduledDeparture,
                'scheduled_arrival_time' => $scheduledArrival,
            ];
        });
    }

    /**
     * Configure the factory to create a flight for a specific route.
     */
    public function forRoute(Route $route)
    {
        return $this->state(function (array $attributes) use ($route) {
            // Generate scheduled times
            $scheduledDeparture = fake()->dateTimeBetween('+1 day', '+1 month');

            // Calculate scheduled arrival based on flight time from route
            $flightTimeMinutes = $route->flight_time;
            $scheduledArrival = (clone $scheduledDeparture)->modify("+{$flightTimeMinutes} minutes");

            return [
                'airline_id' => $route->airline_id,
                'route_id' => $route->id,
                'scheduled_departure_time' => $scheduledDeparture,
                'scheduled_arrival_time' => $scheduledArrival,
            ];
        });
    }

    /**
     * Configure the factory to create a flight for a specific aircraft.
     */
    public function forAircraft(Aircraft $aircraft)
    {
        return $this->state(function (array $attributes) use ($aircraft) {
            return [
                'aircraft_id' => $aircraft->id,
                'airline_id' => $aircraft->airline_id,
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
