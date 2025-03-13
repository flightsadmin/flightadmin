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
        // Run these seeders first to set up airlines, stations, and routes
        $this->call([
            AdminSeeder::class,
            EmailTemplateSeeder::class,
            AirlineNetworkSeeder::class,
        ]);

        // Now create flights based on the routes we've created
        $airlines = Airline::inRandomOrder()->limit(1)->get();

        foreach ($airlines as $airline) {
            AircraftType::factory()->forAirline($airline)->create()->each(function ($aircraftType) use ($airline) {
                Aircraft::factory(2)->create([
                    'airline_id' => $airline->id,
                    'aircraft_type_id' => $aircraftType->id,
                ])->each(function ($aircraft) use ($airline) {
                    $routes = Route::where('airline_id', $airline->id)->get();

                    if ($routes->isEmpty()) {
                        return;
                    }

                    $route = $routes->random();

                    Flight::factory()->forAircraft($aircraft)->forRoute($route)->create([
                        'flight_number' => $airline->iata_code.rand(100, 999),
                    ])->each(function ($flight) use ($airline) {
                        $flight->crew()->attach(Crew::factory()->captain()->create()->id);
                        $flight->crew()->attach(Crew::factory()->firstOfficer()->create()->id);

                        Crew::factory(rand(4, 6))->cabinCrew()->create()
                            ->each(function ($crew) use ($flight) {
                                $flight->crew()->attach($crew->id);
                            });

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
