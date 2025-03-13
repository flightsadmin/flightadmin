<?php

namespace Database\Factories;

use App\Models\Airline;
use App\Models\EmailNotification;
use App\Models\Route;
use App\Models\Station;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmailNotification>
 */
class EmailNotificationFactory extends Factory
{
    protected $model = EmailNotification::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $documentTypes = ['loadsheet', 'flightplan', 'notoc', 'gendec', 'fueling', 'delay'];
        $airline = Airline::inRandomOrder()->first();

        // Generate email addresses based on airline
        $emailDomain = $this->generateEmailDomain($airline->name);
        $emailAddresses = $this->generateEmailAddresses($emailDomain);

        // Generate SITA addresses based on airline ICAO code
        $sitaAddresses = $this->generateSitaAddresses($airline->icao_code);

        return [
            'airline_id' => $airline->id,
            'station_id' => null,
            'route_id' => null,
            'document_type' => fake()->randomElement($documentTypes),
            'email_addresses' => $emailAddresses,
            'sita_addresses' => $sitaAddresses,
            'is_active' => fake()->boolean(80), // 80% chance of being active
        ];
    }

    /**
     * Configure the factory to create a notification for a specific airline.
     */
    public function forAirline(Airline $airline)
    {
        return $this->state(function (array $attributes) use ($airline) {
            $emailDomain = $this->generateEmailDomain($airline->name);
            $emailAddresses = $this->generateEmailAddresses($emailDomain);
            $sitaAddresses = $this->generateSitaAddresses($airline->icao_code);

            return [
                'airline_id' => $airline->id,
                'email_addresses' => $emailAddresses,
                'sita_addresses' => $sitaAddresses,
            ];
        });
    }

    /**
     * Configure the factory to create a notification for a specific document type.
     */
    public function forDocumentType(string $documentType)
    {
        return $this->state(function (array $attributes) use ($documentType) {
            return [
                'document_type' => $documentType,
            ];
        });
    }

    /**
     * Configure the factory to create a station-specific notification.
     */
    public function forStation(Station $station)
    {
        return $this->state(function (array $attributes) use ($station) {
            $airline = Airline::find($attributes['airline_id']);
            $emailDomain = $this->generateEmailDomain($airline->name);

            // Generate station-specific email addresses
            $emailAddresses = [
                strtolower($station->code) . '-test@' . $emailDomain,
                'ops.' . strtolower($station->code) . '-test@' . $emailDomain,
            ];

            // Generate station-specific SITA address
            $sitaAddresses = [$airline->icao_code . strtoupper($station->code)];

            return [
                'station_id' => $station->id,
                'route_id' => null,
                'email_addresses' => $emailAddresses,
                'sita_addresses' => $sitaAddresses,
            ];
        });
    }

    public function forRoute(Route $route)
    {
        return $this->state(function (array $attributes) use ($route) {
            $airline = Airline::find($attributes['airline_id']);
            $departureStation = Station::find($route->departure_station_id);
            $arrivalStation = Station::find($route->arrival_station_id);

            $emailDomain = $this->generateEmailDomain($airline->name);

            // Generate route-specific email addresses
            $emailAddresses = [
                strtolower($departureStation->code) . strtolower($arrivalStation->code) . '@' . $emailDomain,
                'route.' . strtolower($departureStation->code) . strtolower($arrivalStation->code) . '@' . $emailDomain,
            ];

            // Generate route-specific SITA addresses
            $sitaAddresses = [
                $airline->icao_code . strtoupper($departureStation->code),
                $airline->icao_code . strtoupper($arrivalStation->code)
            ];

            return [
                'station_id' => $route->departure_station_id,
                'route_id' => $route->id,
                'email_addresses' => $emailAddresses,
                'sita_addresses' => $sitaAddresses,
            ];
        });
    }

    /**
     * Generate an email domain from airline name.
     */
    private function generateEmailDomain($airlineName)
    {
        // Convert airline name to lowercase and remove spaces and special characters
        $domain = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $airlineName));

        // Add a clearly fake domain to avoid using real airline domains
        return $domain . '.example.org';
    }

    /**
     * Generate email addresses for notifications.
     */
    private function generateEmailAddresses($domain)
    {
        $departments = ['ops', 'dispatch', 'loadcontrol', 'crew', 'flight', 'station'];
        $emailCount = fake()->numberBetween(1, 3);
        $emails = [];

        for ($i = 0; $i < $emailCount; $i++) {
            $department = fake()->randomElement($departments);
            // Add 'test-' prefix to make it clear these are test emails
            $emails[] = 'test-' . $department . '@' . $domain;
        }

        return $emails;
    }

    /**
     * Generate SITA addresses for notifications.
     */
    private function generateSitaAddresses($icaoCode)
    {
        $sitaTypes = ['XHQT', 'XHQD', 'XHQO', 'XHQF', 'XHQS'];
        $sitaCount = fake()->numberBetween(0, 2);
        $sitaAddresses = [];

        for ($i = 0; $i < $sitaCount; $i++) {
            $sitaType = fake()->randomElement($sitaTypes);
            $sitaAddresses[] = $icaoCode . $sitaType;
        }

        return $sitaAddresses;
    }
}