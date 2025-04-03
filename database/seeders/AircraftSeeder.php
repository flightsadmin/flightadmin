<?php

namespace Database\Seeders;

use App\Models\AircraftType;
use App\Models\Airline;
use App\Models\CabinZone;
use App\Models\Hold;
use Illuminate\Database\Seeder;

class AircraftSeeder extends Seeder
{
    public function run(): void
    {
        $airlines = Airline::all();
        $aircraftTypes = AircraftType::with(['aircraft.flights.passengers', 'cabinZones', 'seats'])->get();

        foreach ($aircraftTypes as $aircraftType) {
            foreach ($airlines as $airline) {
                $this->applyAircraftTypeSettings($aircraftType, $airline);
            }

            $this->createCabinZonesAndSeats($aircraftType);

            $this->createHoldsAndPositions($aircraftType);
        }
    }

    private function applyAircraftTypeSettings(AircraftType $aircraftType, Airline $airline): void
    {
        // MAC Settings
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

        // Pantry Settings
        $pantries = [
            'A' => ['name' => 'Pantry A', 'code' => 'A', 'weight' => 497, 'index' => +1.59],
            'E' => ['name' => 'Pantry E', 'code' => 'E', 'weight' => 45, 'index' => +0.18],
            'EMPTY' => ['name' => 'Empty', 'code' => 'EMPTY', 'weight' => 0, 'index' => 0],
        ];

        $aircraftType->settings()->updateOrCreate(
            ['key' => 'pantries', 'airline_id' => $airline->id],
            ['value' => json_encode($pantries), 'type' => 'json', 'description' => 'Aircraft Type Pantry Configurations']
        );

        // Airline Settings
        $settings = [
            'general' => [
                'standard_passenger_weight' => [
                    'type' => 'integer',
                    'description' => 'Standard passenger weight (kg)',
                    'default' => 84,
                ],
                'standard_male_passenger_weight' => [
                    'type' => 'integer',
                    'description' => 'Standard male passenger weight (kg)',
                    'default' => 88,
                ],
                'standard_female_passenger_weight' => [
                    'type' => 'integer',
                    'description' => 'Standard female passenger weight (kg)',
                    'default' => 70,
                ],
                'standard_child_passenger_weight' => [
                    'type' => 'integer',
                    'description' => 'Standard child passenger weight (kg)',
                    'default' => 35,
                ],
                'standard_infant_passenger_weight' => [
                    'type' => 'integer',
                    'description' => 'Standard infant passenger weight (kg)',
                    'default' => 10,
                ],
                'standard_cockpit_crew_weight' => [
                    'type' => 'integer',
                    'description' => 'Standard cockpit crew weight (kg)',
                    'default' => 85,
                ],
                'standard_cabin_crew_weight' => [
                    'type' => 'integer',
                    'description' => 'Standard cabin crew weight (kg)',
                    'default' => 70,
                ],
                'standard_baggage_weight' => [
                    'type' => 'integer',
                    'description' => 'Standard baggage weight (kg)',
                    'default' => 23,
                ],
                'standard_fuel_density' => [
                    'type' => 'float',
                    'description' => 'Standard fuel density (kg/L)',
                    'default' => 0.8,
                ],
            ],
            'operations' => [
                'checkin_open_time' => [
                    'type' => 'integer',
                    'description' => 'Check-in opens before departure (minutes)',
                    'default' => 120,
                ],
                'checkin_close_time' => [
                    'type' => 'integer',
                    'description' => 'Check-in closes before departure (minutes)',
                    'default' => 45,
                ],
                'boarding_open_time' => [
                    'type' => 'integer',
                    'description' => 'Boarding opens before departure (minutes)',
                    'default' => 45,
                ],
                'boarding_close_time' => [
                    'type' => 'integer',
                    'description' => 'Boarding closes before departure (minutes)',
                    'default' => 15,
                ],
            ],
            'cargo' => [
                'dangerous_goods_allowed' => [
                    'type' => 'boolean',
                    'description' => 'Allow dangerous goods',
                    'default' => false,
                ],
                'live_animals_allowed' => [
                    'type' => 'boolean',
                    'description' => 'Allow live animals',
                    'default' => false,
                ],
                'max_cargo_piece_weight' => [
                    'type' => 'integer',
                    'description' => 'Maximum cargo piece weight (kg)',
                    'default' => 150,
                ],
                'max_baggage_piece_weight' => [
                    'type' => 'integer',
                    'description' => 'Maximum baggage piece weight (kg)',
                    'default' => 32,
                ],
            ],
            'notifications' => [
                'enable_email_notifications' => [
                    'type' => 'boolean',
                    'description' => 'Enable email notifications',
                    'default' => true,
                ],
                'enable_sms_notifications' => [
                    'type' => 'boolean',
                    'description' => 'Enable SMS notifications',
                    'default' => false,
                ],
                'notification_email' => [
                    'type' => 'string',
                    'description' => 'Notification email address',
                    'default' => '',
                ],
                'notification_phone' => [
                    'type' => 'string',
                    'description' => 'Notification phone number',
                    'default' => '',
                ],
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
    }

    private function createCabinZonesAndSeats(AircraftType $aircraftType): void
    {
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
                        'designation' => $actualRow . $column,
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

        // Update seat assignments for existing flights
        foreach ($aircraftType->aircraft as $aircraft) {
            foreach ($aircraft->flights as $flight) {
                $exitRowSeats = $aircraftType->seats()
                    ->where('is_exit', true)
                    ->get();

                foreach ($exitRowSeats as $seat) {
                    if (!$flight->seats()->where('seat_id', $seat->id)->exists()) {
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

                    if (!$flight->seats()->where('seat_id', $seatId)->exists()) {
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
    }

    private function createHoldsAndPositions(AircraftType $aircraftType): void
    {
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
