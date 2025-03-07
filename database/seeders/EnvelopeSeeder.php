<?php

namespace Database\Seeders;

use App\Models\AircraftType;
use App\Models\Envelope;
use Illuminate\Database\Seeder;

class EnvelopeSeeder extends Seeder
{
    public function run(): void
    {
        $aircraftTypes = AircraftType::all();

        foreach ($aircraftTypes as $type) {
            Envelope::updateOrCreate(
                [
                    'aircraft_type_id' => $type->id,
                    'name' => 'ZFW',
                ],
                [
                    'points' => [
                        ['weight' => 40600, 'index' => 40.70],
                        ['weight' => 43941, 'index' => 39.02],
                        ['weight' => 48658, 'index' => 37.05],
                        ['weight' => 53398, 'index' => 37.56],
                        ['weight' => 53872, 'index' => 37.56],
                        ['weight' => 54346, 'index' => 37.43],
                        ['weight' => 55611, 'index' => 37.01],
                        ['weight' => 60143, 'index' => 37.50],
                        ['weight' => 64300, 'index' => 36.11],
                        ['weight' => 64300, 'index' => 83.40],
                        ['weight' => 50080, 'index' => 69.31],
                        ['weight' => 49606, 'index' => 69.84],
                        ['weight' => 49132, 'index' => 68.10],
                        ['weight' => 47049, 'index' => 65.39],
                        ['weight' => 45340, 'index' => 60.86],
                        ['weight' => 44866, 'index' => 61.39],
                        ['weight' => 44392, 'index' => 60.26],
                        ['weight' => 42970, 'index' => 59.66],
                        ['weight' => 42022, 'index' => 58.80],
                        ['weight' => 40600, 'index' => 58.20],
                    ],
                    'is_active' => true,
                ]
            );

            Envelope::updateOrCreate(
                [
                    'aircraft_type_id' => $type->id,
                    'name' => 'TOW',
                ],
                [
                    'points' => [
                        ['weight' => 40600, 'index' => 39.02],
                        ['weight' => 45279, 'index' => 36.66],
                        ['weight' => 53000, 'index' => 33.43],
                        ['weight' => 63000, 'index' => 34.52],
                        ['weight' => 72000, 'index' => 31.50],
                        ['weight' => 73500, 'index' => 37.16],
                        ['weight' => 79000, 'index' => 62.28],
                        ['weight' => 79000, 'index' => 80.83],
                        ['weight' => 74708, 'index' => 87.90],
                        ['weight' => 73326, 'index' => 90.18],
                        ['weight' => 67400, 'index' => 86.45],
                        ['weight' => 51000, 'index' => 70.21],
                        ['weight' => 50000, 'index' => 69.22],
                        ['weight' => 47038, 'index' => 65.36],
                        ['weight' => 45249, 'index' => 60.62],
                        ['weight' => 42735, 'index' => 59.56],
                        ['weight' => 40600, 'index' => 56.62],
                    ],
                    'is_active' => true,
                ]
            );

            Envelope::updateOrCreate(
                [
                    'aircraft_type_id' => $type->id,
                    'name' => 'FUEL',
                ],
                [
                    'points' => [
                        ['weight' => 0001, 'index' => +0.00],
                        ['weight' => 3500, 'index' => +0.99],
                        ['weight' => 4000, 'index' => +0.47],
                        ['weight' => 4500, 'index' => -0.01],
                        ['weight' => 5000, 'index' => -0.48],
                        ['weight' => 5500, 'index' => -0.91],
                        ['weight' => 6000, 'index' => -1.32],
                        ['weight' => 6500, 'index' => -1.7],
                        ['weight' => 7000, 'index' => -2.06],
                        ['weight' => 7500, 'index' => -2.39],
                        ['weight' => 8000, 'index' => -2.71],
                        ['weight' => 8500, 'index' => -2.99],
                        ['weight' => 9000, 'index' => -3.16],
                        ['weight' => 9500, 'index' => -3.19],
                        ['weight' => 10000, 'index' => -3.05],
                        ['weight' => 10500, 'index' => -2.8],
                        ['weight' => 11000, 'index' => -2.44],
                        ['weight' => 11500, 'index' => -1.96],
                        ['weight' => 12000, 'index' => -1.4],
                        ['weight' => 12500, 'index' => -1.6],
                        ['weight' => 13000, 'index' => -2.23],
                        ['weight' => 13500, 'index' => -2.94],
                        ['weight' => 14000, 'index' => -3.7],
                        ['weight' => 14500, 'index' => -4.48],
                        ['weight' => 15000, 'index' => -5.26],
                        ['weight' => 15500, 'index' => -6.04],
                        ['weight' => 16000, 'index' => -6.83],
                        ['weight' => 16500, 'index' => -7.61],
                        ['weight' => 17000, 'index' => -8.39],
                        ['weight' => 17500, 'index' => -9.17],
                        ['weight' => 18000, 'index' => -9.96],
                        ['weight' => 18500, 'index' => -10.83],
                        ['weight' => 18632, 'index' => -11.08],
                    ],
                    'is_active' => false,
                ]
            );
        }
    }
}
