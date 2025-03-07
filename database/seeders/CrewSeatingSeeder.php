<?php

namespace Database\Seeders;

use App\Models\AircraftType;
use Illuminate\Database\Seeder;

class CrewSeatingSeeder extends Seeder
{
    public function run(): void
    {
        $aircraftTypes = AircraftType::all();
        $crewSettings = [
            'seating' => [
                'cockpit' => [
                    'location' => 'Cockpit',
                    'index_per_kg' => -0.01341,
                    'arm_length' => -13.410,
                    'max_crew' => 4,
                    'is_deck_crew' => true,
                ],
                'fwd_door' => [
                    'location' => 'FWD of FWD door',
                    'index_per_kg' => -0.01152,
                    'arm_length' => -11.520,
                    'max_crew' => 2,
                    'is_deck_crew' => false,
                ],
                'aft_door_rh' => [
                    'location' => 'FWD of aft door RH',
                    'index_per_kg' => -0.01299,
                    'arm_length' => 12.991,
                    'max_crew' => 1,
                    'is_deck_crew' => false,
                ],
                'aft_door_lh' => [
                    'location' => 'FWD of aft door LH',
                    'index_per_kg' => 0.01299,
                    'arm_length' => 12.991,
                    'max_crew' => 1,
                    'is_deck_crew' => false,
                ],
                'aft_door' => [
                    'location' => 'Aft of aft door',
                    'index_per_kg' => 0.01366,
                    'arm_length' => 13.665,
                    'max_crew' => 1,
                    'is_deck_crew' => false,
                ],
            ],
            'distributions' => [
                1 => [
                    'fwd_door' => 1,
                    'aft_door_rh' => 0,
                    'aft_door_lh' => 0,
                    'aft_door' => 0,
                ],
                2 => [
                    'fwd_door' => 2,
                    'aft_door_rh' => 0,
                    'aft_door_lh' => 0,
                    'aft_door' => 0,
                ],
                3 => [
                    'fwd_door' => 1,
                    'aft_door_rh' => 1,
                    'aft_door_lh' => 1,
                    'aft_door' => 0,
                ],
                4 => [
                    'fwd_door' => 1,
                    'aft_door_rh' => 1,
                    'aft_door_lh' => 1,
                    'aft_door' => 1,
                ],
                5 => [
                    'fwd_door' => 2,
                    'aft_door_rh' => 1,
                    'aft_door_lh' => 1,
                    'aft_door' => 1,
                ],
            ],
        ];
        foreach ($aircraftTypes as $type) {
            $type->settings()->updateOrCreate(
                ['key' => 'crew_settings', 'airline_id' => $type->airline_id],
                [
                    'value' => json_encode($crewSettings),
                    'type' => 'json',
                    'description' => 'Aircraft Type Crew Configurations',
                ]
            );
        }
    }
}
