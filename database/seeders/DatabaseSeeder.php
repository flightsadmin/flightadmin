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
        $airlines = Airline::all();

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

                    // Get a random route
                    $route = $routes->random();

                    // Create a flight using this route
                    $flight = Flight::factory()
                        ->forAircraft($aircraft)
                        ->forRoute($route)
                        ->create();

                    // Create crew for this flight
                    $captain = Crew::factory()->captain()->create();
                    $captain->flights()->attach($flight);

                    $firstOfficer = Crew::factory()->firstOfficer()->create();
                    $firstOfficer->flights()->attach($flight);

                    Crew::factory(rand(4, 6))->cabinCrew()->create()
                        ->each(function ($crew) use ($flight) {
                            $crew->flights()->attach($flight);
                        });

                    // Create passengers and baggage
                    Passenger::factory(rand(10, 30))->forFlight($flight)->create()->each(function ($passenger) use ($flight) {
                        $passenger->baggage()->saveMany(Baggage::factory(rand(1, 3))->make([
                            'flight_id' => $flight->id,
                        ]));
                    });

                    // Create cargo
                    Cargo::factory(rand(5, 10))->create([
                        'flight_id' => $flight->id,
                    ]);

                    // Create fuel
                    Fuel::factory()->create([
                        'flight_id' => $flight->id,
                    ]);

                    // Create containers
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
        }

        // Run remaining seeders
        $this->call([
            AircraftSeeder::class,
            EnvelopeSeeder::class,
            CrewSeatingSeeder::class,
            UldSeeder::class,
            ScheduleSeeder::class,
        ]);
    }
}
