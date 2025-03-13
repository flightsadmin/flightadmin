<?php

namespace Database\Factories;

use App\Models\Airline;
use App\Models\AircraftType;
use App\Models\Route;
use App\Models\Schedule;
use Carbon\Carbon;
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
        $departureTime = Carbon::createFromTime(rand(0, 23), rand(0, 59), 0);
        $arrivalTime = (clone $departureTime)->addHours(rand(1, 8))->addMinutes(rand(0, 59));

        $startDate = Carbon::now()->addDays(rand(1, 30));
        $endDate = (clone $startDate)->addMonths(rand(1, 6));

        // Get a random airline if not specified through relationships
        $airline = Airline::inRandomOrder()->first();

        // Generate days of week (1-7 days)
        $daysCount = rand(1, 7);
        $days = array_slice(range(0, 6), 0, $daysCount);
        shuffle($days);

        return [
            'airline_id' => $airline->id,
            'aircraft_type_id' => AircraftType::where('airline_id', $airline->id)->inRandomOrder()->first()?->id,
            'route_id' => null,
            'flight_number' => $airline->iata_code . rand(100, 999),
            'scheduled_departure_time' => $departureTime,
            'scheduled_arrival_time' => $arrivalTime,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'days_of_week' => $days,
            'is_active' => $this->faker->boolean(80),
        ];
    }

    /**
     * Configure the factory to create a schedule for a specific airline.
     */
    public function forAirline(Airline $airline): self
    {
        return $this->state(function (array $attributes) use ($airline) {
            return [
                'airline_id' => $airline->id,
                'aircraft_type_id' => AircraftType::where('airline_id', $airline->id)->inRandomOrder()->first()?->id,
                'flight_number' => $airline->iata_code . rand(100, 999),
            ];
        });
    }

    /**
     * Configure the factory to create a schedule for a specific route.
     */
    public function forRoute(Route $route): self
    {
        return $this->state(function (array $attributes) use ($route) {
            return [
                'airline_id' => $route->airline_id,
                'route_id' => $route->id,
                'flight_number' => $route->airline->iata_code . rand(100, 999),
            ];
        });
    }

    public function active(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => true,
            ];
        });
    }

    public function inactive(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
            ];
        });
    }
}
