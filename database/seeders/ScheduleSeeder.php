<?php

namespace Database\Seeders;

use App\Models\Airline;
use App\Models\Schedule;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $airlines = Airline::all();

        if ($airlines->isEmpty()) {
            $this->command->info('No airlines found. Creating a default airline.');
            $airlines = Airline::factory()->count(1)->create();
        }

        foreach ($airlines as $airline) {
            // Create some weekday-only schedules
            Schedule::factory()
                ->count(3)
                ->forAirline($airline)
                ->weekdaysOnly()
                ->active()
                ->create();

            // Create some weekend-only schedules
            Schedule::factory()
                ->count(2)
                ->forAirline($airline)
                ->weekendsOnly()
                ->active()
                ->create();

            // Create some daily schedules
            Schedule::factory()
                ->count(2)
                ->forAirline($airline)
                ->daily()
                ->active()
                ->create();

            // Create some schedules without aircraft
            Schedule::factory()
                ->count(2)
                ->forAirline($airline)
                ->withoutAircraft()
                ->active()
                ->create();
        }

        // Generate flights for some of the schedules
        $schedules = Schedule::where('is_active', true)
            ->inRandomOrder()
            ->limit(5)
            ->get();

        foreach ($schedules as $schedule) {
            $flightCount = count($schedule->generateFlights());
            $this->command->info("Generated {$flightCount} flights for schedule {$schedule->flight_number}");
        }
    }
}