<?php

namespace Database\Factories;

use App\Models\Airline;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Airline>
 */
class AirlineFactory extends Factory
{
    protected $model = Airline::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $airlines = [
            [
                'name' => 'Kenya Airways',
                'iata_code' => 'KQ',
                'icao_code' => 'KQA',
                'country' => 'Kenya',
                'address' => 'Nairobi, Kenya',
                'phone' => '021-12345678',
                'email' => 'info@kenyairways.com',
                'description' => 'Kenya Airways is a Kenyan airline headquartered in Nairobi.',
                'active' => true,
            ],
            [
                'name' => 'Jambojet',
                'iata_code' => 'JM',
                'icao_code' => 'JMA',
                'country' => 'Kenya',
                'address' => 'Nairobi, Kenya',
                'phone' => '021-12345678',
                'email' => 'info@jambojet.com',
                'description' => 'Jambojet is a Kenyan airline headquartered in Nairobi.',
                'active' => true,
            ],
        ];
        $airline = fake()->unique()->randomElement($airlines);

        return $airline;
    }
}
