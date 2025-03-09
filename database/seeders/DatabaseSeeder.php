<?php

namespace Database\Seeders;

use App\Models\Aircraft;
use App\Models\AircraftType;
use App\Models\Airline;
use App\Models\Baggage;
use App\Models\Cargo;
use App\Models\Container;
use App\Models\Crew;
use App\Models\Flight;
use App\Models\Fuel;
use App\Models\Passenger;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Airline::factory()->create()->each(function ($airline) {
            AircraftType::factory()->forAirline($airline)->create()->each(function ($aircraftType) use ($airline) {
                Aircraft::factory(3)->create([
                    'airline_id' => $airline->id,
                    'aircraft_type_id' => $aircraftType->id,
                ])->each(function ($aircraft) use ($airline) {
                    Flight::factory(1)->forAirline($airline)->create([
                        'aircraft_id' => $aircraft->id,
                    ])->each(function ($flight) use ($airline) {
                        $captain = Crew::factory()->captain()->create();
                        $captain->flights()->attach($flight);

                        $firstOfficer = Crew::factory()->firstOfficer()->create();
                        $firstOfficer->flights()->attach($flight);

                        Crew::factory(rand(4, 6))->cabinCrew()->create()
                            ->each(function ($crew) use ($flight) {
                                $crew->flights()->attach($flight);
                            });

                        Passenger::factory(rand(10, 30))->forFlight($flight)->create()->each(function ($passenger) use ($flight) {
                            $passenger->baggage()->saveMany(Baggage::factory(rand(1, 3))->make([
                                'flight_id' => $flight->id,
                            ]));
                        });

                        Cargo::factory(rand(5, 10))->create([
                            'flight_id' => $flight->id,
                        ]);

                        Fuel::factory()->create([
                            'flight_id' => $flight->id,
                        ]);

                        foreach (['baggage', 'cargo'] as $type) {
                            Container::factory(rand(1, 2))->forAirline($airline)->create()->each(function ($container) use ($flight, $type) {
                                $flight->containers()->attach($container->id, [
                                    'type' => $type,
                                    'weight' => $container->tare_weight,
                                    'status' => 'unloaded',
                                ]);
                            });
                        }
                    });
                });
            });
        });

        $this->call([
            AdminSeeder::class,
            AircraftSeeder::class,
            EnvelopeSeeder::class,
            CrewSeatingSeeder::class,
            UldSeeder::class,
            ScheduleSeeder::class,
            EmailTemplateSeeder::class,
            AirlineNetworkSeeder::class,
        ]);
    }
}
