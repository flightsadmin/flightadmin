<?php

namespace Database\Factories;

use App\Models\Airline;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContainerFactory extends Factory
{
    public function definition(): array
    {
        $uld = fake()->randomElement(['AKE', 'PMC', 'PLA']);

        return [
            'airline_id' => null,
            'container_number' => $uld.strtoupper(fake()->randomNumber(5)),
            'tare_weight' => fake()->numberBetween(60, 85),
            'max_weight' => 2000,
        ];
    }

    public function forAirline(Airline $airline)
    {
        $uld = fake()->randomElement(['AKE', 'PMC', 'PLA']);

        return $this->state(function (array $attributes) use ($airline, $uld) {
            return [
                'airline_id' => $airline->id,
                'container_number' => $uld.strtoupper(fake()->randomNumber(5)).$airline->iata_code,
            ];
        });
    }
}
