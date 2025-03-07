<?php

namespace Database\Factories;

use App\Models\Flight;
use App\Models\Passenger;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Passenger>
 */
class PassengerFactory extends Factory
{
    protected $model = Passenger::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['male', 'female', 'child']);
        $attributes = [
            'wchr' => fake()->boolean(5),
            'wchs' => fake()->boolean(5),
            'wchc' => fake()->boolean(5),
            'exst' => fake()->boolean(5),
            'stcr' => fake()->boolean(5),
            'deaf' => fake()->boolean(5),
            'blind' => fake()->boolean(5),
            'dpna' => fake()->boolean(5),
            'meda' => fake()->boolean(5),
            'infant' => false,
            'infant_name' => null,
        ];

        // Generate realistic travel documents
        $documents = [
            'travel_documents' => [
                [
                    'type' => fake()->randomElement(['passport', 'national_id', 'residence_permit']),
                    'number' => strtoupper(fake()->bothify('??#####??')),
                    'issuing_country' => fake()->countryCode(),
                    'nationality' => fake()->countryCode(),
                    'issue_date' => fake()->dateTimeBetween('-5 years', '-1 year')->format('Y-m-d'),
                    'expiry_date' => fake()->dateTimeBetween('+1 year', '+10 years')->format('Y-m-d'),
                ],
            ],
            'visas' => [],
        ];

        // Add visa for some passengers
        if (fake()->boolean(30)) {
            $documents['visas'][] = [
                'type' => fake()->randomElement(['tourist', 'business', 'transit']),
                'number' => strtoupper(fake()->bothify('???####??')),
                'issuing_country' => fake()->countryCode(),
                'entries' => fake()->randomElement(['single', 'multiple']),
                'issue_date' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
                'expiry_date' => fake()->dateTimeBetween('+1 month', '+1 year')->format('Y-m-d'),
                'duration_of_stay' => fake()->numberBetween(1, 90),
            ];
        }

        return [
            'flight_id' => null,
            'name' => fake()->name(),
            'type' => $type,
            'pnr' => strtoupper(fake()->bothify('??????')),
            'ticket_number' => fake()->numerify('###-##########'),
            'acceptance_status' => 'pending',
            'boarding_status' => 'pending',
            'special_requirements' => $attributes,
            'documents' => $documents,
        ];
    }

    public function infant(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'infant',
                'special_requirements' => [
                    'wchr' => false,
                    'wchs' => false,
                    'wchc' => false,
                    'exst' => false,
                    'stcr' => false,
                    'deaf' => false,
                    'blind' => false,
                    'dpna' => false,
                    'meda' => false,
                    'infant' => true,
                    'infant_name' => null,
                ],
            ];
        });
    }

    public function configure()
    {
        return $this->afterCreating(function (Passenger $passenger) {
            // If this is an infant, find or create an adult on the same flight
            if ($passenger->type === 'infant') {
                // Try to find an existing adult passenger without an infant
                $adult = Passenger::where('flight_id', $passenger->flight_id)
                    ->whereIn('type', ['male', 'female'])
                    ->whereJsonDoesntContain('special_requirements->infant', true)
                    ->inRandomOrder()
                    ->first();

                // If no adult found, create one
                if (! $adult) {
                    $adult = Passenger::factory()->state([
                        'flight_id' => $passenger->flight_id,
                        'type' => fake()->randomElement(['male', 'female']),
                    ])->create();
                }

                // Update adult's attributes to include infant info
                $attributes = $adult->special_requirements;
                $attributes['infant'] = true;
                $attributes['infant_name'] = $passenger->name;
                $adult->update(['special_requirements' => $attributes]);
            }
            // If this is an adult, randomly assign an infant
            elseif (in_array($passenger->type, ['male', 'female']) && fake()->boolean(20)) {
                // Only create infant if adult doesn't already have one
                if (! ($passenger->special_requirements['infant'] ?? false)) {
                    Passenger::factory()->infant()->create([
                        'flight_id' => $passenger->flight_id,
                    ]);
                }
            }
        });
    }

    public function forFlight(Flight $flight)
    {
        return $this->state(function (array $attributes) use ($flight) {
            return [
                'flight_id' => $flight->id,
            ];
        });
    }
}
