<?php

namespace Database\Factories;

use App\Models\Hold;
use App\Models\HoldPosition;
use Illuminate\Database\Eloquent\Factories\Factory;

class HoldPositionFactory extends Factory
{
    protected $model = HoldPosition::class;

    public function definition(): array
    {
        $row = fake()->numberBetween(1, 4);
        $side = fake()->randomElement(['L', 'R']);

        return [
            'hold_id' => null,
            'code' => $row.($side ?? ''),
            'row' => $row,
            'side' => $side,
            'max_weight' => fake()->numberBetween(1000, 2000),
            'index' => fake()->randomFloat(4, 0, 1),
            'is_active' => true,
        ];
    }

    public function forHold(Hold $hold)
    {
        return $this->state(function (array $attributes) use ($hold) {
            return [
                'hold_id' => $hold->id,
            ];
        });
    }
}
