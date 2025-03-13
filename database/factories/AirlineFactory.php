<?php

namespace Database\Factories;

use App\Models\Airline;
use Illuminate\Database\Eloquent\Factories\Factory;

class AirlineFactory extends Factory
{
    protected $model = Airline::class;

    public function definition(): array
    {
        $airlines = [
            [
                'name' => 'Kenya Airways',
                'iata_code' => 'KQ',
                'icao_code' => 'KQA',
                'country' => 'Kenya',
                'address' => 'Nairobi, Kenya',
                'phone' => '+254 20 327 4747',
                'email' => 'info@kenyaairways.test',
                'description' => 'Kenya Airways is the flag carrier airline of Kenya.',
                'active' => true,
            ],
            [
                'name' => 'Jambojet',
                'iata_code' => 'JM',
                'icao_code' => 'JMA',
                'country' => 'Kenya',
                'address' => 'Nairobi, Kenya',
                'phone' => '+254 711 024 545',
                'email' => 'info@jambojet.test',
                'description' => 'Jambojet is a low-cost carrier based in Kenya.',
                'active' => true,
            ],
            [
                'name' => 'Ethiopian Airlines',
                'iata_code' => 'ET',
                'icao_code' => 'ETH',
                'country' => 'Ethiopia',
                'address' => 'Addis Ababa, Ethiopia',
                'phone' => '+251 11 517 8000',
                'email' => 'info@ethiopianairlines.test',
                'description' => 'Ethiopian Airlines is the flag carrier of Ethiopia.',
                'active' => true,
            ],
            [
                'name' => 'South African Airways',
                'iata_code' => 'SA',
                'icao_code' => 'SAA',
                'country' => 'South Africa',
                'address' => 'Johannesburg, South Africa',
                'phone' => '+27 11 978 1000',
                'email' => 'info@flysaa.test',
                'description' => 'South African Airways is the national flag carrier of South Africa.',
                'active' => true,
            ],
            [
                'name' => 'RwandAir',
                'iata_code' => 'WB',
                'icao_code' => 'RWD',
                'country' => 'Rwanda',
                'address' => 'Kigali, Rwanda',
                'phone' => '+250 788 177 000',
                'email' => 'info@rwandair.test',
                'description' => 'RwandAir is the flag carrier airline of Rwanda.',
                'active' => true,
            ],
            [
                'name' => 'Air Tanzania',
                'iata_code' => 'TC',
                'icao_code' => 'ATC',
                'country' => 'Tanzania',
                'address' => 'Dar es Salaam, Tanzania',
                'phone' => '+255 22 211 8411',
                'email' => 'info@airtanzania.test',
                'description' => 'Air Tanzania is the flag carrier of Tanzania.',
                'active' => true,
            ],
            [
                'name' => 'EgyptAir',
                'iata_code' => 'MS',
                'icao_code' => 'MSR',
                'country' => 'Egypt',
                'address' => 'Cairo, Egypt',
                'phone' => '+20 2 2696 0000',
                'email' => 'info@egyptair.test',
                'description' => 'EgyptAir is the flag carrier of Egypt.',
                'active' => true,
            ],
            [
                'name' => 'Royal Air Maroc',
                'iata_code' => 'AT',
                'icao_code' => 'RAM',
                'country' => 'Morocco',
                'address' => 'Casablanca, Morocco',
                'phone' => '+212 5 2291 4747',
                'email' => 'info@royalairmaroc.test',
                'description' => 'Royal Air Maroc is the Moroccan national carrier.',
                'active' => true,
            ],
        ];

        $airline = fake()->unique()->randomElement($airlines);

        return $airline;
    }
}
