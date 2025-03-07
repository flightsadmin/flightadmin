<?php

namespace Database\Factories;

use App\Models\Airline;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AircraftType>
 */
class AircraftTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = [
            'code' => 'A320',
            'name' => 'Airbus A320',
            'manufacturer' => 'Airbus',
            'max_passengers' => 180,
            'max_deck_crew' => 4,
            'max_cabin_crew' => 8,
            'mtow' => 79000,
            'mzfw' => 64300,
            'mlw' => 67400,
            'empty' => 42600,
            'cargo' => 14600,
            'fuel' => 18900,
            'category' => 'Narrow-body',
        ];

        return [
            'airline_id' => null,
            'code' => $type['code'],
            'name' => $type['name'],
            'manufacturer' => $type['manufacturer'],
            'max_passengers' => $type['max_passengers'],
            'cargo_capacity' => $type['cargo'],
            'max_fuel_capacity' => $type['fuel'],
            'empty_weight' => $type['empty'],
            'max_zero_fuel_weight' => $type['mzfw'],
            'max_takeoff_weight' => $type['mtow'],
            'max_landing_weight' => $type['mlw'],
            'category' => $type['category'],
            'max_deck_crew' => $type['max_deck_crew'],
            'max_cabin_crew' => $type['max_cabin_crew'],
        ];
    }

    public function forAirline(Airline $airline): self
    {
        return $this->state(function (array $attributes) use ($airline) {
            return [
                'airline_id' => $airline->id,
            ];
        });
    }
}
