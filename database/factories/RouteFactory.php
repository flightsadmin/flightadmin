<?php

namespace Database\Factories;

use App\Models\Airline;
use App\Models\Route;
use App\Models\Station;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Route>
 */
class RouteFactory extends Factory
{
    protected $model = Route::class;

    public function definition(): array
    {
        $departureStation = Station::inRandomOrder()->first();

        $arrivalStation = Station::where('id', '!=', $departureStation->id)
            ->inRandomOrder()
            ->first();

        $distance = $this->calculateDistance($departureStation, $arrivalStation);
        $flightTime = $this->calculateFlightTime($distance);

        return [
            'airline_id' => Airline::inRandomOrder()->first()->id,
            'departure_station_id' => $departureStation->id,
            'arrival_station_id' => $arrivalStation->id,
            'flight_time' => $flightTime,
            'distance' => $distance,
            'is_active' => true,
        ];
    }

    public function forAirline(Airline $airline)
    {
        return $this->state(function (array $attributes) use ($airline) {
            return [
                'airline_id' => $airline->id,
            ];
        });
    }

    public function betweenStations(Station $departure, Station $arrival)
    {
        return $this->state(function (array $attributes) use ($departure, $arrival) {
            $distance = $this->calculateDistance($departure, $arrival);
            $flightTime = $this->calculateFlightTime($distance);

            return [
                'departure_station_id' => $departure->id,
                'arrival_station_id' => $arrival->id,
                'flight_time' => $flightTime,
                'distance' => $distance,
            ];
        });
    }

    private function calculateDistance($departureStation, $arrivalStation)
    {
        $regions = [
            'East Africa' => ['countries' => ['Kenya', 'Uganda', 'Tanzania', 'Rwanda', 'Burundi', 'Ethiopia']],
            'Southern Africa' => ['countries' => ['South Africa', 'Zimbabwe', 'Zambia', 'Mozambique']],
            'West Africa' => ['countries' => ['Ghana', 'Nigeria', 'Ivory Coast', 'Senegal']],
            'North Africa' => ['countries' => ['Egypt', 'Morocco', 'Algeria', 'Tunisia']],
            'Middle East' => ['countries' => ['United Arab Emirates', 'Qatar']],
            'Europe' => ['countries' => ['United Kingdom', 'France', 'Netherlands']],
            'North America' => ['countries' => ['United States']],
        ];

        $regionDistances = [
            'East Africa-East Africa' => [500, 1500],
            'East Africa-Southern Africa' => [2500, 3500],
            'East Africa-West Africa' => [3000, 4500],
            'East Africa-North Africa' => [3000, 4000],
            'East Africa-Middle East' => [3000, 4500],
            'East Africa-Europe' => [6000, 8000],
            'East Africa-North America' => [11000, 14000],

            'Southern Africa-Southern Africa' => [500, 1500],
            'Southern Africa-West Africa' => [3500, 5000],
            'Southern Africa-North Africa' => [5000, 7000],
            'Southern Africa-Middle East' => [6000, 8000],
            'Southern Africa-Europe' => [8000, 10000],
            'Southern Africa-North America' => [12000, 15000],

            'West Africa-West Africa' => [500, 1500],
            'West Africa-North Africa' => [2500, 4000],
            'West Africa-Middle East' => [5000, 7000],
            'West Africa-Europe' => [4000, 6000],
            'West Africa-North America' => [7000, 9000],

            'North Africa-North Africa' => [500, 1500],
            'North Africa-Middle East' => [2000, 3500],
            'North Africa-Europe' => [2000, 3500],
            'North Africa-North America' => [7000, 9000],

            'Middle East-Middle East' => [300, 800],
            'Middle East-Europe' => [4000, 5500],
            'Middle East-North America' => [10000, 12000],

            'Europe-Europe' => [300, 1500],
            'Europe-North America' => [5500, 7500],

            'North America-North America' => [1000, 4000],
        ];

        // Determine regions for departure and arrival
        $departureRegion = $this->getRegionForCountry($departureStation->country, $regions);
        $arrivalRegion = $this->getRegionForCountry($arrivalStation->country, $regions);

        // Sort regions alphabetically to ensure consistent key lookup
        $regionPair = [$departureRegion, $arrivalRegion];
        sort($regionPair);
        $regionKey = $regionPair[0].'-'.$regionPair[1];

        // If same airport, return 0
        if ($departureStation->id === $arrivalStation->id) {
            return 0;
        }

        // If same country but different airports, use a smaller range
        if ($departureStation->country === $arrivalStation->country) {
            return fake()->numberBetween(200, 800);
        }

        // Use predefined distances between regions
        if (isset($regionDistances[$regionKey])) {
            return fake()->numberBetween($regionDistances[$regionKey][0], $regionDistances[$regionKey][1]);
        }

        // Default fallback
        return fake()->numberBetween(1000, 10000);
    }

    /**
     * Get the region for a country.
     */
    private function getRegionForCountry($country, $regions)
    {
        foreach ($regions as $region => $data) {
            if (in_array($country, $data['countries'])) {
                return $region;
            }
        }

        return 'Other';
    }

    private function calculateFlightTime($distance)
    {
        $cruisingTimeMinutes = ($distance / 800) * 60;
        $taxiAndProceduresMinutes = 40; // 20 minutes for takeoff, 20 for landing

        return (int) round($cruisingTimeMinutes + $taxiAndProceduresMinutes);
    }
}
