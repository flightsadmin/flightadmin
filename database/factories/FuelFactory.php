<?php

namespace Database\Factories;

use App\Models\Flight;
use App\Models\Fuel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Fuel>
 */
class FuelFactory extends Factory
{
    protected $model = Fuel::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $crew = fake()->randomElement(['2/4', '2/5', '3/4', '3/5']);
        $pantry = fake()->randomElement(['A', 'E', 'EMPTY']);
        $blockFuel = fake()->numberBetween(10000, 18900);
        $taxiFuel = fake()->numberBetween(200, 400);
        $takeOffFuel = $blockFuel - $taxiFuel;
        $tripFuel = $blockFuel * 0.6;

        return [
            'flight_id' => null,
            'block_fuel' => $blockFuel,
            'take_off_fuel' => $takeOffFuel,
            'taxi_fuel' => $taxiFuel,
            'trip_fuel' => $tripFuel,
            'crew' => $crew,
            'pantry' => $pantry,
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
}
