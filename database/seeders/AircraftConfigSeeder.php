<?php

namespace Database\Seeders;

use App\Models\AircraftType;
use App\Models\Airline;
use App\Models\CabinZone;
use App\Models\Hold;
use Illuminate\Database\Seeder;

class AircraftConfigSeeder extends Seeder
{
    public function run(): void
    {
        $aircraftType = AircraftType::with(['aircraft.flights.passengers', 'cabinZones', 'seats'])->inRandomOrder()->first();
        $airline = Airline::inRandomOrder()->first();

        $macSettings = [
            'k_constant' => 50,
            'c_constant' => 1000,
            'length_of_mac' => 4.194,
            'lemac_at' => 17.8015,
            'ref_sta_at' => 18.850,
        ];

        $aircraftType->settings()->updateOrCreate(
            ['key' => 'mac_settings', 'airline_id' => $airline->id],
            [
                'value' => json_encode($macSettings),
                'type' => 'json',
                'description' => 'MAC Calculation Settings',
            ]
        );

        $pantries = [
            'A' => ['name' => 'Pantry A', 'code' => 'A', 'weight' => 497, 'index' => +1.59],
            'E' => ['name' => 'Pantry E', 'code' => 'E', 'weight' => 45, 'index' => +0.18],
            'EMPTY' => ['name' => 'Empty', 'code' => 'EMPTY', 'weight' => 0, 'index' => 0],
        ];

        $aircraftType->settings()->updateOrCreate(
            ['key' => 'pantries', 'airline_id' => $airline->id],
            ['value' => json_encode($pantries), 'type' => 'json', 'description' => 'Aircraft Type Pantry Configurations']
        );

        $settings = [
            'general' => [
                'standard_passenger_weight' => 84,
                'standard_male_passenger_weight' => 88,
                'standard_female_passenger_weight' => 70,
                'standard_child_passenger_weight' => 35,
                'standard_infant_passenger_weight' => 10,
                'standard_cockpit_crew_weight' => 85,
                'standard_cabin_crew_weight' => 75,
                'standard_baggage_weight' => 13,
                'standard_fuel_density' => 0.89,
            ],
            'operations' => [
                'checkin_open_time' => 180,
                'checkin_close_time' => 45,
                'boarding_open_time' => 60,
                'boarding_close_time' => 15,
            ],
            'cargo' => [
                'dangerous_goods_allowed' => false,
                'live_animals_allowed' => false,
                'max_cargo_piece_weight' => 150,
                'max_baggage_piece_weight' => 32,
            ],
            'notifications' => [
                'enable_email_notifications' => true,
                'enable_sms_notifications' => false,
                'notification_email' => '',
                'notification_phone' => '',
            ],
        ];

        $airline->settings()->updateOrCreate(
            ['key' => 'airline_settings', 'airline_id' => $airline->id],
            [
                'value' => json_encode($settings),
                'type' => 'json',
                'description' => 'Airline Configuration Settings',
            ]
        );

        $zones = [
            ['name' => 'A', 'max_capacity' => 54, 'arm' => -6.971, 'index' => -0.00697],
            ['name' => 'B', 'max_capacity' => 60, 'arm' => +0.281, 'index' => +0.00028],
            ['name' => 'C', 'max_capacity' => 66, 'arm' => +8.271, 'index' => +0.00827],
        ];

        $lastRowNumber = 0;
        foreach ($zones as $key => $zoneData) {
            $zone = CabinZone::updateOrCreate(
                [
                    'aircraft_type_id' => $aircraftType->id,
                    'name' => $zoneData['name'],
                ],
                $zoneData
            );

            $zone->seats()->delete();
            $rows = ceil($zone->max_capacity / 10);
            $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K'];

            $seats = [];
            for ($row = 1; $row <= $rows; $row++) {
                $actualRow = $lastRowNumber + $row;
                foreach ($columns as $column) {
                    $seats[] = [
                        'aircraft_type_id' => $aircraftType->id,
                        'cabin_zone_id' => $zone->id,
                        'row' => $actualRow,
                        'column' => $column,
                        'designation' => $actualRow.$column,
                        'type' => 'economy',
                        'is_exit' => in_array($actualRow, [5, 13]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            $zone->seats()->createMany($seats);
            $lastRowNumber += $rows;
        }

        foreach ($aircraftType->aircraft as $aircraft) {
            foreach ($aircraft->flights as $flight) {
                $exitRowSeats = $aircraftType->seats()
                    ->where('is_exit', true)
                    ->get();

                foreach ($exitRowSeats as $seat) {
                    if (! $flight->seats()->where('seat_id', $seat->id)->exists()) {
                        $flight->seats()->attach($seat->id, [
                            'is_blocked' => true,
                            'blocked_reason' => 'Exit Row',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                $availableSeats = $aircraftType->seats()
                    ->whereNotIn('id', $exitRowSeats->pluck('id'))
                    ->whereDoesntHave('passenger', function ($query) use ($flight) {
                        $query->where('flight_id', $flight->id);
                    })->get()->pluck('id')->toArray();

                $flight->passengers()->whereNull('seat_id')->each(function ($passenger) use (&$availableSeats, $flight) {
                    if (empty($availableSeats)) {
                        return false;
                    }

                    $randomIndex = array_rand($availableSeats);
                    $seatId = $availableSeats[$randomIndex];
                    unset($availableSeats[$randomIndex]);

                    if (! $flight->seats()->where('seat_id', $seatId)->exists()) {
                        $flight->seats()->attach($seatId, [
                            'is_blocked' => false,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    $passenger->update(['seat_id' => $seatId]);
                });
            }
        }

        $holds = [
            [
                'name' => 'Forward Hold',
                'code' => 'FH',
                'position' => 1,
                'max_weight' => 3402,
                'index' => -0.00642,
                'positions' => [
                    ['code' => '11L', 'row' => 11, 'side' => 'L', 'max_weight' => 1134, 'index' => -0.00811],
                    ['code' => '11R', 'row' => 11, 'side' => 'R', 'max_weight' => 1134, 'index' => -0.00811],
                    ['code' => '12L', 'row' => 12, 'side' => 'L', 'max_weight' => 1134, 'index' => -0.00811],
                    ['code' => '12R', 'row' => 12, 'side' => 'R', 'max_weight' => 1134, 'index' => -0.00811],
                    ['code' => '13L', 'row' => 13, 'side' => 'L', 'max_weight' => 1134, 'index' => -0.00811],
                    ['code' => '13R', 'row' => 13, 'side' => 'R', 'max_weight' => 1134, 'index' => -0.00811],
                    ['code' => '14L', 'row' => 14, 'side' => 'L', 'max_weight' => 1134, 'index' => -0.00811],
                    ['code' => '14R', 'row' => 14, 'side' => 'R', 'max_weight' => 1134, 'index' => -0.00811],
                ],
            ],
            [
                'name' => 'Aft Hold',
                'code' => 'AH',
                'position' => 2,
                'max_weight' => 2426,
                'index' => +0.00401,
                'positions' => [
                    ['code' => '41L', 'row' => 41, 'side' => 'L', 'max_weight' => 1134, 'index' => +0.00324],
                    ['code' => '41R', 'row' => 41, 'side' => 'R', 'max_weight' => 1134, 'index' => +0.00324],
                    ['code' => '42L', 'row' => 42, 'side' => 'L', 'max_weight' => 1134, 'index' => +0.00324],
                    ['code' => '42R', 'row' => 42, 'side' => 'R', 'max_weight' => 1134, 'index' => +0.00324],
                    ['code' => '43L', 'row' => 43, 'side' => 'L', 'max_weight' => 1134, 'index' => +0.00324],
                    ['code' => '43R', 'row' => 43, 'side' => 'R', 'max_weight' => 1134, 'index' => +0.00324],
                    ['code' => '44L', 'row' => 44, 'side' => 'L', 'max_weight' => 1134, 'index' => +0.00324],
                    ['code' => '44R', 'row' => 44, 'side' => 'R', 'max_weight' => 1134, 'index' => +0.00324],
                ],
            ],
            [
                'name' => 'Bulk Hold',
                'code' => 'BH',
                'position' => 3,
                'max_weight' => 1497,
                'index' => +0.01048,
                'positions' => [
                    ['code' => '51', 'row' => 51, 'side' => null, 'max_weight' => 1134, 'index' => +0.01133],
                    ['code' => '52', 'row' => 52, 'side' => null, 'max_weight' => 1134, 'index' => +0.01133],
                    ['code' => '53', 'row' => 53, 'side' => null, 'max_weight' => 1134, 'index' => +0.01133],
                ],
            ],
        ];

        foreach ($holds as $holdData) {
            $positions = $holdData['positions'];
            unset($holdData['positions']);

            $hold = Hold::updateOrCreate(
                [
                    'aircraft_type_id' => $aircraftType->id,
                    'code' => $holdData['code'],
                ],
                $holdData
            );
            $hold->positions()->delete();
            $hold->positions()->createMany($positions);
        }
    }
}
