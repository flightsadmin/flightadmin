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
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data to avoid duplicates
        $this->truncateTables();

        // Create airlines
        $this->command->info('Creating airlines...');
        $airlines = $this->createAirlines();

        // Create stations
        $this->command->info('Creating stations...');
        $stations = $this->createStations();

        // Assign stations to airlines
        $this->command->info('Assigning stations to airlines...');
        $this->assignStationsToAirlines($airlines, $stations);

        // Create routes
        $this->command->info('Creating routes...');
        $routes = $this->createRoutes($airlines);

        // Create email notifications
        $this->command->info('Creating email notifications...');
        $this->createEmailNotifications($airlines, $routes);

        $this->command->info('Airline network seeding completed successfully!');
    }

    /**
     * Truncate all related tables.
     */
    private function truncateTables(): void
    {
        $this->command->info('Clearing existing data...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        EmailNotification::truncate();
        Route::truncate();
        DB::table('airline_station')->truncate();
        Station::truncate();
        Airline::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Create airlines using factory.
     */
    private function createAirlines(int $count = 5): array
    {
        return Airline::factory()->count($count)->create()->all();
    }

    /**
     * Create stations using factory.
     */
    private function createStations(int $count = 20): array
    {
        return Station::factory()->count($count)->create()->all();
    }

    /**
     * Assign stations to airlines with hub designation.
     */
    private function assignStationsToAirlines(array $airlines, array $stations): void
    {
        foreach ($airlines as $airline) {
            // Determine how many stations to assign to this airline (between 5 and 15)
            $stationCount = min(count($stations), rand(5, 15));

            // Randomly select stations for this airline
            $airlineStations = collect($stations)->random($stationCount);

            // Designate 1-2 stations as hubs
            $hubCount = min(2, $stationCount);
            $hubs = $airlineStations->random($hubCount);

            // Attach stations to airline
            foreach ($airlineStations as $station) {
                $isHub = $hubs->contains($station);

                // Generate contact information
                $contactEmail = null;
                $contactPhone = null;

                if (fake()->boolean(70)) { // 70% chance to have contact info
                    // Use a clearly fake email domain
                    $emailDomain = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $airline->name)) . '.example.org';
                    $contactEmail = 'test-' . strtolower($station->code) . '@' . $emailDomain;
                    $contactPhone = '+' . fake()->numberBetween(1, 999) . ' ' . fake()->numberBetween(100, 999) . ' ' . fake()->numberBetween(1000, 9999);
                }

                // Attach station with pivot data
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

    /**
     * Create routes for airlines.
     */
    private function createRoutes(array $airlines): array
    {
        $routes = [];
        $existingRoutes = []; // Track all created routes to avoid duplicates

        foreach ($airlines as $airline) {
            // Get stations for this airline
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
            // Create airline-wide notifications for different document types
            foreach ($documentTypes as $documentType) {
                if (fake()->boolean(80)) { // 80% chance to create notification for each document type
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

            // Create route-specific notifications for some routes
            $airlineRoutes = collect($routes)->filter(function ($route) use ($airline) {
                return $route->airline_id === $airline->id;
            });

            $routeCount = min(3, $airlineRoutes->count());
            if ($routeCount > 0) {
                $selectedRoutes = $airlineRoutes->random($routeCount);

                foreach ($selectedRoutes as $route) {
                    // Create notifications for 1-2 random document types
                    $notificationCount = fake()->numberBetween(1, 2);
                    $selectedDocTypes = collect($documentTypes)->random($notificationCount);

                    foreach ($selectedDocTypes as $documentType) {
                        EmailNotification::factory()
                            ->forAirline($airline)
                            ->forRoute($route)
                            ->forDocumentType($documentType)
                            ->create();
                    }
                }
            }
        }
    }
}