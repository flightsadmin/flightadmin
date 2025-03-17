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
use App\Models\Route;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            EmailTemplateSeeder::class,
            AirlineNetworkSeeder::class,
        ]);

        $airlines = Airline::inRandomOrder()->limit(3)->get();

        // Create a pool of crew members to reuse
        $captains = Crew::factory(3)->captain()->create();
        $firstOfficers = Crew::factory(3)->firstOfficer()->create();
        $cabinCrew = Crew::factory(10)->cabinCrew()->create();

        // Create a pool of containers to reuse
        $baggageContainers = [];
        $cargoContainers = [];

        foreach ($airlines as $airline) {
            // Create 2-3 containers per airline for reuse
            for ($i = 0; $i < 3; $i++) {
                $baggageContainers[$airline->id][] = Container::factory()->forAirline($airline)->create();
                $cargoContainers[$airline->id][] = Container::factory()->forAirline($airline)->create();
            }

            AircraftType::factory()->forAirline($airline)->create()->each(function ($aircraftType) use ($airline, $captains, $firstOfficers, $cabinCrew, $baggageContainers, $cargoContainers) {
                Aircraft::factory(2)->create([
                    'airline_id' => $airline->id,
                    'aircraft_type_id' => $aircraftType->id,
                ])->each(function ($aircraft) use ($airline, $captains, $firstOfficers, $cabinCrew, $baggageContainers, $cargoContainers) {
                    $routes = Route::where('airline_id', $airline->id)->get();

                    if ($routes->isEmpty()) {
                        return;
                    }

                    $route = $routes->random();

                    Flight::factory()->forAircraft($aircraft)->forRoute($route)->create([
                        'flight_number' => $airline->iata_code . rand(100, 999),
                    ])->each(function ($flight) use ($airline, $captains, $firstOfficers, $cabinCrew, $baggageContainers, $cargoContainers) {
                        // Assign random crew from the pool instead of creating new ones
                        $flight->crew()->attach($captains->random()->id);
                        $flight->crew()->attach($firstOfficers->random()->id);

                        // Assign 2-3 cabin crew members from the pool
                        $randomCabinCrew = $cabinCrew->random(rand(2, 3));
                        foreach ($randomCabinCrew as $crew) {
                            $flight->crew()->attach($crew->id);
                        }

                        Passenger::factory(rand(20, 30))->forFlight($flight)->create()->each(function ($passenger) use ($flight) {
                            $passenger->baggage()->saveMany(Baggage::factory(rand(1, 3))->make([
                                'flight_id' => $flight->id,
                            ]));
                        });

                        Cargo::factory(rand(10, 15))->create([
                            'flight_id' => $flight->id,
                        ]);

                        Fuel::factory()->create([
                            'flight_id' => $flight->id,
                        ]);

                        // Use containers from the pool instead of creating new ones
                        if (isset($baggageContainers[$airline->id])) {
                            $container = $baggageContainers[$airline->id][array_rand($baggageContainers[$airline->id])];
                            if (!$flight->containers()->where('container_id', $container->id)->exists()) {
                                $flight->containers()->attach($container->id, [
                                    'type' => 'baggage',
                                    'weight' => $container->tare_weight,
                                    'status' => 'unloaded',
                                ]);
                            }
                        }

                        if (isset($cargoContainers[$airline->id])) {
                            $container = $cargoContainers[$airline->id][array_rand($cargoContainers[$airline->id])];
                            if (!$flight->containers()->where('container_id', $container->id)->exists()) {
                                $flight->containers()->attach($container->id, [
                                    'type' => 'cargo',
                                    'weight' => $container->tare_weight,
                                    'status' => 'unloaded',
                                ]);
                            }
                        }
                    });
                });
            });
        }

        $this->call([
            AircraftSeeder::class,
            EnvelopeSeeder::class,
            CrewSeatingSeeder::class,
            UldSeeder::class,
            ScheduleSeeder::class,
        ]);
    }
}
