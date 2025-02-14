<?php

namespace App\Enums;

enum Settings
{
    case STANDARD_COCKPIT_CREW_WEIGHT = 'standard_cockpit_crew_weight';
    case STANDARD_CABIN_CREW_WEIGHT = 'standard_cabin_crew_weight';
    case STANDARD_BAGGAGE_WEIGHT = 'standard_baggage_weight';
    case STANDARD_PANTRY_WEIGHT = 'standard_pantry_weight';
    case STANDARD_PASSENGER_WEIGHT = 'standard_passenger_weight';
    case STANDARD_MALE_PASSENGER_WEIGHT = 'standard_male_passenger_weight';
    case STANDARD_FEMALE_PASSENGER_WEIGHT = 'standard_female_passenger_weight';
    case STANDARD_CHILD_PASSENGER_WEIGHT = 'standard_child_passenger_weight';
    case STANDARD_INFANT_PASSENGER_WEIGHT = 'standard_infant_passenger_weight';

    public function label(): string
    {
        return match ($this) {
            self::STANDARD_COCKPIT_CREW_WEIGHT => 'Standard Cockpit Crew Weight',
            self::STANDARD_CABIN_CREW_WEIGHT => 'Standard Cabin Crew Weight',
            self::STANDARD_BAGGAGE_WEIGHT => 'Standard Baggage Weight',
            self::STANDARD_PANTRY_WEIGHT => 'Standard Pantry Weight',
            self::STANDARD_PASSENGER_WEIGHT => 'Standard Passenger Weight',
            self::STANDARD_MALE_PASSENGER_WEIGHT => 'Standard Male Passenger Weight',
            self::STANDARD_FEMALE_PASSENGER_WEIGHT => 'Standard Female Passenger Weight',
            self::STANDARD_CHILD_PASSENGER_WEIGHT => 'Standard Child Passenger Weight',
            self::STANDARD_INFANT_PASSENGER_WEIGHT => 'Standard Infant Passenger Weight',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::STANDARD_COCKPIT_CREW_WEIGHT => 'The standard weight for cockpit crew members.',
            self::STANDARD_CABIN_CREW_WEIGHT => 'The standard weight for cabin crew members.',
            self::STANDARD_BAGGAGE_WEIGHT => 'The standard weight for baggage.',
            self::STANDARD_PANTRY_WEIGHT => 'The standard weight for pantry items.',
            self::STANDARD_PASSENGER_WEIGHT => 'The standard weight for passengers.',
            self::STANDARD_MALE_PASSENGER_WEIGHT => 'The standard weight for male passengers.',
            self::STANDARD_FEMALE_PASSENGER_WEIGHT => 'The standard weight for female passengers.',
            self::STANDARD_CHILD_PASSENGER_WEIGHT => 'The standard weight for child passengers.',
            self::STANDARD_INFANT_PASSENGER_WEIGHT => 'The standard weight for infant passengers.',
        };
    }

    public function type(): string
    {
        return match ($this) {
            self::STANDARD_COCKPIT_CREW_WEIGHT => 'number',
            self::STANDARD_CABIN_CREW_WEIGHT => 'number',
            self::STANDARD_BAGGAGE_WEIGHT => 'number',
            self::STANDARD_PANTRY_WEIGHT => 'number',
            self::STANDARD_PASSENGER_WEIGHT => 'number',
            self::STANDARD_MALE_PASSENGER_WEIGHT => 'number',
            self::STANDARD_FEMALE_PASSENGER_WEIGHT => 'number',
            self::STANDARD_CHILD_PASSENGER_WEIGHT => 'number',
            self::STANDARD_INFANT_PASSENGER_WEIGHT => 'number',
        };
    }

    public function default(): int
    {
        return match ($this) {
            self::STANDARD_COCKPIT_CREW_WEIGHT => 85,
            self::STANDARD_CABIN_CREW_WEIGHT => 75,
            self::STANDARD_BAGGAGE_WEIGHT => 20,
            self::STANDARD_PANTRY_WEIGHT => 250,
            self::STANDARD_PASSENGER_WEIGHT => 84,
            self::STANDARD_MALE_PASSENGER_WEIGHT => 88,
            self::STANDARD_FEMALE_PASSENGER_WEIGHT => 70,
            self::STANDARD_CHILD_PASSENGER_WEIGHT => 35,
            self::STANDARD_INFANT_PASSENGER_WEIGHT => 10,
        };
    }
}
