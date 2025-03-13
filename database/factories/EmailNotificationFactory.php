<?php

namespace Database\Factories;

use App\Models\Airline;
use App\Models\EmailNotification;
use App\Models\Station;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmailNotificationFactory extends Factory
{
    protected $model = EmailNotification::class;

    public function definition(): array
    {
        $documentTypes = ['loadsheet', 'loadinginstructions', 'notoc', 'flightplan'];
        $airline = Airline::inRandomOrder()->first();

        $emailDomain = $this->generateEmailDomain($airline->name);
        $emailAddresses = $this->generateEmailAddresses($emailDomain);
        $sitaAddresses = $this->generateSitaAddresses($airline);

        return [
            'airline_id' => $airline->id,
            'station_id' => null,
            'route_id' => null,
            'document_type' => fake()->randomElement($documentTypes),
            'email_addresses' => $emailAddresses,
            'sita_addresses' => $sitaAddresses,
            'is_active' => fake()->boolean(80),
        ];
    }

    public function forAirline(Airline $airline)
    {
        return $this->state(function (array $attributes) use ($airline) {
            $emailDomain = $this->generateEmailDomain($airline->name);
            $emailAddresses = $this->generateEmailAddresses($emailDomain);
            $sitaAddresses = $this->generateSitaAddresses($airline);

            return [
                'airline_id' => $airline->id,
                'email_addresses' => $emailAddresses,
                'sita_addresses' => $sitaAddresses,
            ];
        });
    }

    public function forDocumentType(string $documentType)
    {
        return $this->state(function (array $attributes) use ($documentType) {
            return [
                'document_type' => $documentType,
            ];
        });
    }

    public function forStation(Station $station)
    {
        return $this->state(function (array $attributes) use ($station) {
            $airline = Airline::find($attributes['airline_id']);
            $emailDomain = $this->generateEmailDomain($airline->name);

            $emailAddresses = [
                strtolower($station->code).'@'.$emailDomain,
                'ops.'.strtolower($station->code).'@'.$emailDomain,
            ];

            $sitaAddresses = [strtoupper($station->code).'XX'.$airline->iata_code];

            return [
                'station_id' => $station->id,
                'route_id' => null,
                'email_addresses' => $emailAddresses,
                'sita_addresses' => $sitaAddresses,
            ];
        });
    }

    private function generateEmailDomain($airlineName)
    {
        $domain = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $airlineName));

        return $domain.'.test';
    }

    private function generateEmailAddresses($domain)
    {
        $departments = ['ops', 'dispatch', 'loadcontrol', 'crew', 'flight', 'station'];
        $emailCount = fake()->numberBetween(1, 3);
        $emails = [];

        for ($i = 0; $i < $emailCount; $i++) {
            $department = fake()->randomElement($departments);
            $emails[] = strtolower($department).'@'.$domain;
        }

        return $emails;
    }

    private function generateSitaAddresses(Airline $airline)
    {
        $sitaAddresses = [];

        for ($i = 0; $i < fake()->numberBetween(1, 2); $i++) {
            $sitaAddresses[] = strtoupper($airline->icao_code).'XX'.$airline->iata_code;
        }

        return $sitaAddresses;
    }
}
