<?php

namespace Database\Factories;

use App\Models\Crew;
use App\Models\Flight;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Crew>
 */
class CrewFactory extends Factory
{
    protected $model = Crew::class;

    protected $static = 1;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $crew = Crew::max('id') ?? 0;

        return [
            'employee_id' => str_pad($crew + 1, 6, '0', STR_PAD_LEFT),
            'name' => $this->faker->name(),
            'position' => $this->faker->randomElement(['captain', 'first_officer', 'cabin_crew']),
            'notes' => $this->faker->optional(0.7)->sentence,
        ];
    }

    public function captain(): self
    {
        return $this->state(fn (array $attributes) => [
            'position' => 'captain',
        ]);
    }

    public function firstOfficer(): self
    {
        return $this->state(fn (array $attributes) => [
            'position' => 'first_officer',
        ]);
    }

    public function cabinCrew(): self
    {
        return $this->state(fn (array $attributes) => [
            'position' => 'cabin_crew',
        ]);
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
