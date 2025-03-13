<?php

namespace Database\Factories;

use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Flight;
use App\Models\Route;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Flight>
 */
class FlightFactory extends Factory
{
    protected $model = Flight::class;

    public function definition(): array
    {
        $departureTime = Carbon::now()->addDays(rand(1, 30))->setTime(rand(0, 23), rand(0, 59), 0);
        $arrivalTime = (clone $departureTime)->addHours(rand(1, 8))->addMinutes(rand(0, 59));

        // Get a random airline if not specified through relationships
        $airline = Airline::inRandomOrder()->first();

        return [
            'airline_id' => $airline->id,
            'aircraft_id' => null,
            'route_id' => null,
            'flight_number' => $airline->iata_code.rand(100, 999),
            'scheduled_departure_time' => $departureTime,
            'scheduled_arrival_time' => $arrivalTime,
            'actual_departure_time' => null,
            'actual_arrival_time' => null,
            'status' => $this->faker->randomElement(['scheduled', 'boarding', 'departed', 'arrived', 'cancelled']),
        ];
    }

    public function forAirline(Airline $airline): self
    {
        return $this->state(function (array $attributes) use ($airline) {
            return [
                'airline_id' => $airline->id,
                'flight_number' => $airline->iata_code.rand(100, 999),
            ];
        });
    }

    public function forRoute(Route $route): self
    {
        return $this->state(function (array $attributes) use ($route) {
            return [
                'airline_id' => $route->airline_id,
                'route_id' => $route->id,
                'flight_number' => $route->airline->iata_code.rand(100, 999),
            ];
        });
    }

    public function forAircraft(Aircraft $aircraft): self
    {
        return $this->state(function (array $attributes) use ($aircraft) {
            return [
                'airline_id' => $aircraft->airline_id,
                'aircraft_id' => $aircraft->id,
                'flight_number' => $aircraft->airline->iata_code.rand(100, 999),
            ];
        });
    }

    public function scheduled(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'scheduled',
            ];
        });
    }

    public function departed(): self
    {
        return $this->state(function (array $attributes) {
            $departureTime = Carbon::now()->subHours(rand(1, 5));

            return [
                'status' => 'departed',
                'scheduled_departure_time' => $departureTime,
                'actual_departure_time' => (clone $departureTime)->addMinutes(rand(-30, 60)),
            ];
        });
    }

    public function arrived(): self
    {
        return $this->state(function (array $attributes) {
            $departureTime = Carbon::now()->subHours(rand(6, 12));
            $arrivalTime = (clone $departureTime)->addHours(rand(1, 8));

            return [
                'status' => 'arrived',
                'scheduled_departure_time' => $departureTime,
                'scheduled_arrival_time' => $arrivalTime,
                'actual_departure_time' => (clone $departureTime)->addMinutes(rand(-30, 60)),
                'actual_arrival_time' => (clone $arrivalTime)->addMinutes(rand(-30, 60)),
            ];
        });
    }

    public function cancelled(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'cancelled',
            ];
        });
    }
}
