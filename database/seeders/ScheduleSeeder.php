<?php

namespace Database\Seeders;

use App\Models\Airline;
use App\Models\Route;
use App\Models\Schedule;
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

            // Create 2-4 schedules for each airline
            $scheduleCount = rand(2, 4);

            for ($i = 0; $i < $scheduleCount; $i++) {
                // Get a random route
                $route = $routes->random();

                // Create a schedule using this route with airline IATA code in flight number
                Schedule::factory()->forRoute($route)->create([
                    'flight_number' => $airline->iata_code . rand(100, 999)
                ]);
            }
        }
    }
}
