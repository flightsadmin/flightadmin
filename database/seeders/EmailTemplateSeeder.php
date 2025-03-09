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
                'subject' => 'Welcome to FlightAdmin - Set Your Password',
                'body' => "Hello {name},\n\n".
                    "Welcome to FlightAdmin! Your account has been created with below details\n\n Email: {email}\n\n".
                    "To get started, please click the link below to set your password:\n\n".
                    "{reset_link}\n\n".
                    "This link will expire in 60 minutes.\n\n".
                    "If you have any questions, feel free to reach out to our support team.\n\n".
                    "Best regards,\n\nThe FlightAdmin Team",
                'variables' => [
                    'name' => 'User\'s full name',
                    'email' => 'User\'s email address',
                    'reset_link' => 'Password set URL',
                ],
            ],
            [
                'name' => 'Load Sheet Released',
                'subject' => 'Load Sheet Released for Flight {flight_number}',
                'body' => "Dear {name},\n\nThe load sheet for flight {flight_number}/{date} has been released.\n\nPlease review the attached load sheet and confirm receipt.\n\nBest regards,\n\nFlight Operations",
                'variables' => [
                    'name' => 'Recipient\'s name',
                    'flight_number' => 'Flight number',
                    'date' => 'Flight date',
                ],
            ],
            [
                'name' => 'Loading Instructions',
                'subject' => 'Loading Instructions - Flight {flight_number}',
                'body' => "Dear {name},\n\nLoading Instructions for Flight {flight_number}/{date} has been released\n\nPlease revert with final loading to release loadsheet.",
                'variables' => [
                    'name' => 'Recipient\'s name',
                    'flight_number' => 'Flight number',
                    'date' => 'Flight date',
                ],
            ],
            [
                'name' => 'Flight Schedule Change',
                'subject' => 'Schedule Change - Flight {flight_number}',
                'body' => "Dear {name},\n\nThis is to inform you of a schedule change for your flight {flight_number}.\n\nOriginal Schedule:\nDate: {original_date}\nDeparture: {original_departure_time}\n\nNew Schedule:\nDate: {new_date}\nDeparture: {new_departure_time}\n\nReason for change: {change_reason}\n\nPlease update your records accordingly.\n\nBest regards,\nFlight Operations",
                'variables' => [
                    'name' => 'Recipient\'s name',
                    'flight_number' => 'Flight number',
                    'original_date' => 'Original flight date',
                    'original_departure_time' => 'Original departure time',
                    'new_date' => 'New flight date',
                    'new_departure_time' => 'New departure time',
                    'change_reason' => 'Reason for schedule change',
                ],
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::create($template);
        }
    }
}
