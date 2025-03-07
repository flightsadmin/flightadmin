<?php

namespace Database\Factories;

use App\Models\AircraftType;
use App\Models\Hold;
use Illuminate\Database\Eloquent\Factories\Factory;

class HoldFactory extends Factory
{
    protected $model = Hold::class;

    public function definition(): array
    {
        $holds = [
            'FH' => [
                'name' => 'Forward Hold',
                'code' => 'FH',
                'position' => 1,
                'index' => $this->faker->randomFloat(4, 0, 1),
            ],
            'AH' => [
                'name' => 'Aft Hold',
                'code' => 'AH',
                'position' => 2,
                'index' => $this->faker->randomFloat(4, 0, 1),
            ],
            'BH' => [
                'name' => 'Bulk Hold',
                'code' => 'BH',
                'position' => 3,
                'index' => $this->faker->randomFloat(4, 0, 1),
            ],
        ];

        $code = $this->faker->randomElement(array_keys($holds));

        return [
            'aircraft_type_id' => null,
            'name' => $holds[$code]['name'],
            'code' => $holds[$code]['code'],
            'position' => $holds[$code]['position'],
            'max_weight' => $this->faker->numberBetween(2000, 5000),
            'index' => $holds[$code]['index'],
            'is_active' => fake()->boolean(90),
        ];
    }

    public function forAircraftType(AircraftType $aircraftType)
    {
        return $this->state(function (array $attributes) use ($aircraftType) {
            return [
                'aircraft_type_id' => $aircraftType->id,
            ];
        });
    }
}
