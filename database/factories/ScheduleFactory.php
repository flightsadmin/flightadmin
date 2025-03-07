<?php

namespace Database\Factories;

use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduleFactory extends Factory
{
    protected $model = Schedule::class;

    public function definition(): array
    {
        $airline = Airline::inRandomOrder()->first() ?? Airline::factory()->create();

        // Aircraft is optional, so we'll make it null 20% of the time
        $aircraft = fake()->boolean(80)
            ? (Aircraft::where('airline_id', $airline->id)->inRandomOrder()->first() ?? Aircraft::factory()->create(['airline_id' => $airline->id]))
            : null;

        // Generate random IATA airport codes
        $airports = ['JFK', 'LAX', 'ORD', 'DFW', 'DEN', 'ATL', 'SFO', 'SEA', 'MIA', 'LAS', 'BOS', 'CLT', 'MCO', 'PHX', 'IAH', 'LHR', 'CDG', 'FRA', 'AMS', 'DXB'];
        $departureAirport = fake()->randomElement($airports);

        // Make sure arrival airport is different from departure
        do {
            $arrivalAirport = fake()->randomElement($airports);
        } while ($arrivalAirport === $departureAirport);

        // Generate a random flight number
        $flightNumber = strtoupper($airline->iata_code) . str_pad(fake()->numberBetween(1, 999), 4, '0', STR_PAD_LEFT);

        // Generate random departure and arrival times
        $departureTime = fake()->time('H:i:s');
        $arrivalTime = Carbon::parse($departureTime)->addHours(fake()->numberBetween(1, 12))->format('H:i:s');

        // Generate start and end dates
        $startDate = fake()->dateTimeBetween('-2 days', '+1 month');
        $endDate = fake()->dateTimeBetween($startDate, '+6 months');

        // Generate random days of the week (0 = Sunday, 6 = Saturday)
        $daysOfWeek = fake()->randomElements([0, 1, 2, 3, 4, 5, 6], fake()->numberBetween(1, 7));
        sort($daysOfWeek);

        return [
            'airline_id' => $airline->id,
            'aircraft_id' => $aircraft?->id,
            'flight_number' => $flightNumber,
            'departure_airport' => $departureAirport,
            'arrival_airport' => $arrivalAirport,
            'departure_time' => $departureTime,
            'arrival_time' => $arrivalTime,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'days_of_week' => $daysOfWeek,
            'is_active' => fake()->boolean(80), // 80% chance of being active
        ];
    }

    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => true,
            ];
        });
    }

    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
            ];
        });
    }

    public function onDays(array $days)
    {
        return $this->state(function (array $attributes) use ($days) {
            return [
                'days_of_week' => $days,
            ];
        });
    }

    public function weekdaysOnly()
    {
        return $this->state(function (array $attributes) {
            return [
                'days_of_week' => [1, 2, 3, 4, 5],
            ];
        });
    }

    public function weekendsOnly()
    {
        return $this->state(function (array $attributes) {
            return [
                'days_of_week' => [0, 6],
            ];
        });
    }

    public function daily()
    {
        return $this->state(function (array $attributes) {
            return [
                'days_of_week' => [0, 1, 2, 3, 4, 5, 6],
            ];
        });
    }

    public function forAirline($airline)
    {
        $airlineId = $airline instanceof Airline ? $airline->id : $airline;

        return $this->state(function (array $attributes) use ($airlineId) {
            return [
                'airline_id' => $airlineId,
            ];
        });
    }

    public function withAircraft($aircraft)
    {
        $aircraftId = $aircraft instanceof Aircraft ? $aircraft->id : $aircraft;

        return $this->state(function (array $attributes) use ($aircraftId) {
            return [
                'aircraft_id' => $aircraftId,
            ];
        });
    }

    public function withoutAircraft()
    {
        return $this->state(function (array $attributes) {
            return [
                'aircraft_id' => null,
            ];
        });
    }
}