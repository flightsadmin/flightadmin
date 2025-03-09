<?php

namespace Database\Seeders;

use App\Models\Airline;
use App\Models\Route;
use App\Models\Schedule;
use App\Models\Container;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all airlines
        $airlines = Airline::all();

        foreach ($airlines as $airline) {
            // Get routes for this airline
            $routes = Route::where('airline_id', $airline->id)->get();

            // Skip if no routes exist for this airline
            if ($routes->isEmpty()) {
                $this->command->info("No routes found for {$airline->name}, skipping schedule creation.");
                continue;
            }

            // Create 3-5 schedules for each airline
            $scheduleCount = rand(3, 5);

            for ($i = 0; $i < $scheduleCount; $i++) {
                // Get a random route
                $route = $routes->random();

                // Create a schedule using this route
                Schedule::factory()
                    ->forRoute($route)
                    ->create();
            }

            $this->command->info("Created {$scheduleCount} schedules for {$airline->name}.");
        }

        // Generate flights for some of the schedules
        $schedules = Schedule::where('is_active', true)
            ->inRandomOrder()
            ->limit(5)
            ->get();

        foreach ($schedules as $schedule) {
            $flightIds = $schedule->generateFlights();

            foreach ($flightIds as $flightId) {
                $flight = $schedule->flights()->find($flightId);

                Container::factory()->forAirline($schedule->airline)->create()->flights()->attach($flight->id, [
                    'type' => 'baggage',
                    'weight' => 70,
                    'status' => 'unloaded'
                ]);

                Container::factory()->forAirline($schedule->airline)->create()->flights()->attach($flight->id, [
                    'type' => 'cargo',
                    'weight' => 80,
                    'status' => 'unloaded'
                ]);
            }
        }
    }
}
