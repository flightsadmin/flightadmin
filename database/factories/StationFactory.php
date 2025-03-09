<?php

namespace Database\Factories;

use App\Models\Station;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Station>
 */
class StationFactory extends Factory
{
    protected $model = Station::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $stations = [
            // East Africa
            [
                'code' => 'NBO',
                'name' => 'Jomo Kenyatta International Airport',
                'country' => 'Kenya',
                'timezone' => 'Africa/Nairobi',
            ],
            [
                'code' => 'MBA',
                'name' => 'Moi International Airport',
                'country' => 'Kenya',
                'timezone' => 'Africa/Nairobi',
            ],
            [
                'code' => 'KIS',
                'name' => 'Kisumu International Airport',
                'country' => 'Kenya',
                'timezone' => 'Africa/Nairobi',
            ],
            [
                'code' => 'EBB',
                'name' => 'Entebbe International Airport',
                'country' => 'Uganda',
                'timezone' => 'Africa/Kampala',
            ],
            [
                'code' => 'DAR',
                'name' => 'Julius Nyerere International Airport',
                'country' => 'Tanzania',
                'timezone' => 'Africa/Dar_es_Salaam',
            ],
            [
                'code' => 'ZNZ',
                'name' => 'Abeid Amani Karume International Airport',
                'country' => 'Tanzania',
                'timezone' => 'Africa/Dar_es_Salaam',
            ],
            [
                'code' => 'KGL',
                'name' => 'Kigali International Airport',
                'country' => 'Rwanda',
                'timezone' => 'Africa/Kigali',
            ],
            [
                'code' => 'BJM',
                'name' => 'Bujumbura International Airport',
                'country' => 'Burundi',
                'timezone' => 'Africa/Bujumbura',
            ],
            [
                'code' => 'ADD',
                'name' => 'Addis Ababa Bole International Airport',
                'country' => 'Ethiopia',
                'timezone' => 'Africa/Addis_Ababa',
            ],

            // Southern Africa
            [
                'code' => 'JNB',
                'name' => 'O.R. Tambo International Airport',
                'country' => 'South Africa',
                'timezone' => 'Africa/Johannesburg',
            ],
            [
                'code' => 'CPT',
                'name' => 'Cape Town International Airport',
                'country' => 'South Africa',
                'timezone' => 'Africa/Johannesburg',
            ],
            [
                'code' => 'DUR',
                'name' => 'King Shaka International Airport',
                'country' => 'South Africa',
                'timezone' => 'Africa/Johannesburg',
            ],
            [
                'code' => 'HRE',
                'name' => 'Robert Gabriel Mugabe International Airport',
                'country' => 'Zimbabwe',
                'timezone' => 'Africa/Harare',
            ],

            // West Africa
            [
                'code' => 'ACC',
                'name' => 'Kotoka International Airport',
                'country' => 'Ghana',
                'timezone' => 'Africa/Accra',
            ],
            [
                'code' => 'LOS',
                'name' => 'Murtala Muhammed International Airport',
                'country' => 'Nigeria',
                'timezone' => 'Africa/Lagos',
            ],
            [
                'code' => 'ABJ',
                'name' => 'Port Bouet Airport',
                'country' => 'Ivory Coast',
                'timezone' => 'Africa/Abidjan',
            ],
            [
                'code' => 'DKR',
                'name' => 'Blaise Diagne International Airport',
                'country' => 'Senegal',
                'timezone' => 'Africa/Dakar',
            ],

            // North Africa
            [
                'code' => 'CAI',
                'name' => 'Cairo International Airport',
                'country' => 'Egypt',
                'timezone' => 'Africa/Cairo',
            ],
            [
                'code' => 'CMN',
                'name' => 'Mohammed V International Airport',
                'country' => 'Morocco',
                'timezone' => 'Africa/Casablanca',
            ],
            [
                'code' => 'ALG',
                'name' => 'Houari Boumediene Airport',
                'country' => 'Algeria',
                'timezone' => 'Africa/Algiers',
            ],
            [
                'code' => 'TUN',
                'name' => 'Tunis Carthage International Airport',
                'country' => 'Tunisia',
                'timezone' => 'Africa/Tunis',
            ],

            // International Connections
            [
                'code' => 'DXB',
                'name' => 'Dubai International Airport',
                'country' => 'United Arab Emirates',
                'timezone' => 'Asia/Dubai',
            ],
            [
                'code' => 'DOH',
                'name' => 'Hamad International Airport',
                'country' => 'Qatar',
                'timezone' => 'Asia/Qatar',
            ],
            [
                'code' => 'LHR',
                'name' => 'London Heathrow Airport',
                'country' => 'United Kingdom',
                'timezone' => 'Europe/London',
            ],
            [
                'code' => 'CDG',
                'name' => 'Charles de Gaulle Airport',
                'country' => 'France',
                'timezone' => 'Europe/Paris',
            ],
            [
                'code' => 'AMS',
                'name' => 'Amsterdam Schiphol Airport',
                'country' => 'Netherlands',
                'timezone' => 'Europe/Amsterdam',
            ],
            [
                'code' => 'JFK',
                'name' => 'John F. Kennedy International Airport',
                'country' => 'United States',
                'timezone' => 'America/New_York',
            ],
        ];

        $station = fake()->unique()->randomElement($stations);

        return [
            'code' => $station['code'],
            'name' => $station['name'],
            'country' => $station['country'],
            'timezone' => $station['timezone'],
            'is_active' => true,
        ];
    }
}