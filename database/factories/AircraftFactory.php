<?php

namespace Database\Factories;

use App\Models\Aircraft;
use App\Models\Airline;
use Illuminate\Database\Eloquent\Factories\Factory;

class AircraftFactory extends Factory
{
    protected $model = Aircraft::class;

    public function definition(): array
    {
        $registrationNumbers = [
            [
                'registration_number' => strtoupper(fake()->unique()->lexify('??-????')),
                'basic_weight' => 42782,
                'basic_index' => 50.41,
            ],
            [
                'registration_number' => strtoupper(fake()->unique()->lexify('??-????')),
                'basic_weight' => 43516,
                'basic_index' => 51.38,
            ],
        ];
        $registrationNumber = fake()->randomElement($registrationNumbers);

        return [
            'airline_id' => null,
            'aircraft_type_id' => null,
            'registration_number' => $registrationNumber['registration_number'],
            'basic_weight' => $registrationNumber['basic_weight'],
            'basic_index' => $registrationNumber['basic_index'],
            'active' => fake()->boolean(90),
            'remarks' => fake()->sentence(),
        ];
    }

    public function forAirline(Airline $airline)
    {
        return $this->state(function (array $attributes) use ($airline) {
            return [
                'airline_id' => $airline->id,
            ];
        });
    }
}
