<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Welcome New User',
                'subject' => 'Welcome to FlightAdmin - Get Started',
                'body' => "Hello {name},\n\nWelcome to FlightAdmin! We're excited to have you on board.\n\nYour account has been successfully created with the following details:\nEmail: {email}\n\nTo get started, please click the button below to verify your email address:\n\n{verification_link}\n\nIf you have any questions, feel free to reach out to our support team.\n\nBest regards,\nThe FlightAdmin Team",
                'variables' => [
                    'name' => 'User\'s full name',
                    'email' => 'User\'s email address',
                    'verification_link' => 'Email verification URL'
                ]
            ],
            [
                'name' => 'Load Sheet Released',
                'subject' => 'Load Sheet Released for Flight {flight_number}',
                'body' => "Dear {name},\n\nThe load sheet for flight {flight_number} has been released.\n\nFlight Details:\nDate: {date}\nDeparture: {departure}\nArrival: {arrival}\nSTD: {departure_time}\n\nPlease review the attached load sheet and confirm receipt.\n\nBest regards,\n\nFlight Operations",
                'variables' => [
                    'name' => 'Recipient\'s name',
                    'flight_number' => 'Flight number',
                    'date' => 'Flight date',
                    'departure' => 'Departure airport',
                    'arrival' => 'Arrival airport',
                    'departure_time' => 'Scheduled departure time',
                ]
            ],
            [
                'name' => 'Loading Instructions',
                'subject' => 'Loading Instructions - Flight {flight_number}',
                'body' => "Loading Instructions for Flight {flight_number}\n\nDate: {date}\nAircraft: {aircraft_type} - {registration}\n\nDeparture: {departure} at {departure_time}\nArrival: {arrival}\n\nSpecial Instructions:\n{special_instructions}\n\nCargo Distribution:\nFWD Hold: {fwd_hold_weight}\nAFT Hold: {aft_hold_weight}\n\nRemarks:\n{remarks}\n\nPlease ensure all loading is completed 45 minutes before STD.",
                'variables' => [
                    'flight_number' => 'Flight number',
                    'date' => 'Flight date',
                    'aircraft_type' => 'Aircraft type',
                    'registration' => 'Aircraft registration',
                    'departure' => 'Departure airport',
                    'arrival' => 'Arrival airport',
                    'departure_time' => 'Departure time',
                    'special_instructions' => 'Special loading instructions',
                    'fwd_hold_weight' => 'Forward hold weight',
                    'aft_hold_weight' => 'Aft hold weight',
                    'remarks' => 'Additional remarks'
                ]
            ],
            [
                'name' => 'Password Reset',
                'subject' => 'Reset Your FlightAdmin Password',
                'body' => "Hello {name},\n\nWe received a request to reset your password for your FlightAdmin account.\n\nTo reset your password, click the link below:\n{reset_link}\n\nThis link will expire in {expires_in} minutes.\n\nIf you didn't request this password reset, please ignore this email.\n\nBest regards,\nFlightAdmin Security Team",
                'variables' => [
                    'name' => 'User\'s name',
                    'reset_link' => 'Password reset URL',
                    'expires_in' => 'Link expiration time in minutes'
                ]
            ],
            [
                'name' => 'Flight Schedule Change',
                'subject' => 'Schedule Change - Flight {flight_number}',
                'body' => "Dear {name},\n\nThis is to inform you of a schedule change for flight {flight_number}.\n\nOriginal Schedule:\nDate: {original_date}\nDeparture: {original_departure_time}\n\nNew Schedule:\nDate: {new_date}\nDeparture: {new_departure_time}\n\nReason for change: {change_reason}\n\nPlease update your records accordingly.\n\nBest regards,\nFlight Operations",
                'variables' => [
                    'name' => 'Recipient\'s name',
                    'flight_number' => 'Flight number',
                    'original_date' => 'Original flight date',
                    'original_departure_time' => 'Original departure time',
                    'new_date' => 'New flight date',
                    'new_departure_time' => 'New departure time',
                    'change_reason' => 'Reason for schedule change'
                ]
            ]
        ];

        foreach ($templates as $template) {
            EmailTemplate::create($template);
        }
    }
}
