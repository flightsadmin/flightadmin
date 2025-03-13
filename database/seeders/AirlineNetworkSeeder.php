<?php

namespace Database\Seeders;

use App\Models\Airline;
use App\Models\EmailNotification;
use App\Models\Route;
use App\Models\Station;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AirlineNetworkSeeder extends Seeder
{
    public function run(): void
    {
        $this->truncateTables();

        $airlines = $this->createAirlines();
        $stations = $this->createStations();
        $this->assignStationsToAirlines($airlines, $stations);
        $routes = $this->createRoutes($airlines);
        $this->createEmailNotifications($airlines, $routes);
    }
    private function truncateTables(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        EmailNotification::truncate();
        Route::truncate();
        DB::table('airline_station')->truncate();
        Station::truncate();
        Airline::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function createAirlines(int $count = 5): array
    {
        return Airline::factory()->count($count)->create()->all();
    }

    private function createStations(int $count = 10): array
    {
        return Station::factory()->count($count)->create()->all();
    }

    private function assignStationsToAirlines(array $airlines, array $stations): void
    {
        foreach ($airlines as $airline) {
            $stationCount = min(count($stations), rand(5, 7));

            $airlineStations = collect($stations)->random($stationCount);

            $hubs = $airlineStations->random(min(2, $stationCount));

            foreach ($airlineStations as $station) {
                $isHub = $hubs->contains($station);

                $emailDomain = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $airline->name)) . '.test';
                $contactEmail = strtolower($station->code) . '@' . $emailDomain;
                $contactPhone = '+' . fake()->numberBetween(1, 999) . ' ' . fake()->numberBetween(100, 999) . ' ' . fake()->numberBetween(1000, 9999);

                $airline->stations()->attach($station->id, [
                    'is_hub' => $isHub,
                    'contact_email' => $contactEmail,
                    'contact_phone' => $contactPhone,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function createRoutes(array $airlines): array
    {
        $routes = [];
        $existingRoutes = [];

        foreach ($airlines as $airline) {
            $airlineStations = $airline->stations;

            // Skip if airline has less than 2 stations
            if ($airlineStations->count() < 2) {
                continue;
            }

            // Create routes between stations
            $routeCount = min(20, $airlineStations->count() * ($airlineStations->count() - 1) / 2);

            // Get all possible station pairs
            $stationPairs = [];
            foreach ($airlineStations as $departure) {
                foreach ($airlineStations as $arrival) {
                    if ($departure->id !== $arrival->id) {
                        $key = "{$airline->id}-{$departure->id}-{$arrival->id}";

                        // Check if this route or its reverse already exists in our tracking array
                        $reverseKey = "{$airline->id}-{$arrival->id}-{$departure->id}";
                        if (!isset($existingRoutes[$key]) && !isset($existingRoutes[$reverseKey])) {
                            $stationPairs[] = [
                                'departure' => $departure,
                                'arrival' => $arrival,
                                'key' => $key
                            ];
                        }
                    }
                }
            }

            // Shuffle the pairs to get random selection
            shuffle($stationPairs);

            // Take only the number of routes we need
            $selectedPairs = array_slice($stationPairs, 0, $routeCount);

            foreach ($selectedPairs as $pair) {
                // Check if this route already exists in the database
                $routeExists = Route::where('airline_id', $airline->id)
                    ->where('departure_station_id', $pair['departure']->id)
                    ->where('arrival_station_id', $pair['arrival']->id)
                    ->exists();

                if (!$routeExists) {
                    try {
                        // Create route using factory
                        $route = Route::factory()
                            ->forAirline($airline)
                            ->betweenStations($pair['departure'], $pair['arrival'])
                            ->create();

                        $routes[] = $route;
                        $existingRoutes[$pair['key']] = true;

                        // Create return route (70% chance)
                        if (fake()->boolean(70)) {
                            $reverseKey = "{$airline->id}-{$pair['arrival']->id}-{$pair['departure']->id}";

                            // Check if reverse route exists
                            $reverseRouteExists = Route::where('airline_id', $airline->id)
                                ->where('departure_station_id', $pair['arrival']->id)
                                ->where('arrival_station_id', $pair['departure']->id)
                                ->exists();

                            if (!$reverseRouteExists) {
                                $returnRoute = Route::factory()
                                    ->forAirline($airline)
                                    ->betweenStations($pair['arrival'], $pair['departure'])
                                    ->create();

                                $routes[] = $returnRoute;
                                $existingRoutes[$reverseKey] = true;
                            }
                        }
                    } catch (\Exception $e) {
                        $this->command->warn("Failed to create route: {$pair['departure']->code} to {$pair['arrival']->code} for {$airline->name}");
                        $this->command->warn($e->getMessage());
                    }
                }
            }
        }

        return $routes;
    }

    private function createEmailNotifications(array $airlines, array $routes): void
    {
        $documentTypes = ['loadsheet', 'flightplan', 'notoc', 'gendec', 'fueling', 'delay'];

        foreach ($airlines as $airline) {
            foreach ($documentTypes as $documentType) {
                if (fake()->boolean(80)) {
                    EmailNotification::factory()
                        ->forAirline($airline)
                        ->forDocumentType($documentType)
                        ->create();
                }
            }

            // Create station-specific notifications for some stations
            $stationCount = min(3, $airline->stations->count());
            if ($stationCount > 0) {
                $stations = $airline->stations->random($stationCount);

                foreach ($stations as $station) {
                    // Create notifications for 1-3 random document types
                    $notificationCount = fake()->numberBetween(1, 3);
                    $selectedDocTypes = collect($documentTypes)->random($notificationCount);

                    foreach ($selectedDocTypes as $documentType) {
                        EmailNotification::factory()
                            ->forAirline($airline)
                            ->forStation($station)
                            ->forDocumentType($documentType)
                            ->create();
                    }
                }
            }
        }
    }
}