<?php

namespace Database\Seeders;

use App\Models\Airline;
use App\Models\Route;
use App\Models\Schedule;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $airlines = Airline::all();

        foreach ($airlines as $airline) {
            $routes = Route::where('airline_id', $airline->id)->get();

            if ($routes->isEmpty()) {
                $this->command->info("No routes found for {$airline->name}, skipping schedule creation.");

                continue;
            }

            for ($i = 0; $i < rand(2, 4); $i++) {
                $route = $routes->random();

                Schedule::factory()->forRoute($route)->create([
                    'flight_number' => $airline->iata_code.rand(100, 999),
                ]);
            }
        }
    }
}
