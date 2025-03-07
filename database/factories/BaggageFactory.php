<?php

namespace Database\Factories;

use App\Models\Baggage;
use App\Models\Flight;
use App\Models\Passenger;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Baggage>
 */
class BaggageFactory extends Factory
{
    protected $model = Baggage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $tagNumber = 1;

        return [
            'flight_id' => null,
            'passenger_id' => null,
            'tag_number' => function (array $attributes) use (&$tagNumber) {
                $flight = Flight::find($attributes['flight_id']);
                if ($flight) {
                    return strtoupper($flight->airline->iata_code.sprintf('%06d', $tagNumber++));
                }

                return sprintf('TT%06d', $tagNumber++);
            },
            'container_id' => null,
            'weight' => fake()->numberBetween(5, 32),
            'status' => 'checked',
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function forFlight(Flight $flight)
    {
        return $this->state(function (array $attributes) use ($flight) {
            return [
                'flight_id' => $flight->id,
            ];
        });
    }

    public function forPassenger(Passenger $passenger)
    {
        return $this->state(function (array $attributes) use ($passenger) {
            return [
                'flight_id' => $passenger->flight_id,
                'passenger_id' => $passenger->id,
            ];
        });
    }
}
