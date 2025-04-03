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
                'subject' => 'Welcome to {company_name} - Set Your Password',
                'body' => "Hello {name},\n\n" .
                    "Welcome to {company_name}! Your account has been created with below details\n\n" .
                    "Email: {email}\n\n" .
                    "To get started, please click the link below to set your password:\n\n" .
                    "{reset_link}\n\n" .
                    "This link will expire in 60 minutes.\n\n" .
                    "If you have any questions, feel free to reach out to our support team.\n\n" .
                    "Best regards,\n" .
                    "The {company_name} Team",
                'variables' => [
                    'name' => 'User\'s full name',
                    'email' => 'User\'s email address',
                    'reset_link' => 'Password set URL',
                    'company_name' => 'Company Name'
                ],
            ],
            [
                'name' => 'Load Sheet Released',
                'subject' => 'Load Sheet Released for Flight {flight_number}',
                'body' => "Dear {name},\n\n" .
                    "The load sheet for flight {flight_number}/{date} has been released.\n\n" .
                    "Please review the attached load sheet and confirm receipt.\n\n" .
                    "Best regards,\n" .
                    "{company_name} Flight Operations",
                'variables' => [
                    'name' => 'Recipient\'s name',
                    'flight_number' => 'Flight number',
                    'date' => 'Flight date',
                    'company_name' => 'Company Name'
                ],
            ],
            [
                'name' => 'Loading Instructions',
                'subject' => 'Loading Instructions - Flight {flight_number}',
                'body' => "Dear {name},\n\n" .
                    "Loading Instructions for Flight {flight_number}/{date} has been released\n\n" .
                    "Please revert with final loading to release loadsheet.\n\n" .
                    "Best regards,\n" .
                    "{company_name} Flight Operations",
                'variables' => [
                    'name' => 'Recipient\'s name',
                    'flight_number' => 'Flight number',
                    'date' => 'Flight date',
                    'company_name' => 'Company Name'
                ],
            ]
        ];

        foreach ($templates as $template) {
            EmailTemplate::create($template);
        }
    }
}
