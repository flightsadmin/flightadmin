# FlightAdmin

[![Latest Version on Packagist](https://img.shields.io/packagist/v/flightsadmin/flightadmin.svg?style=flat-square)](https://packagist.org/packages/flightsadmin/flightadmin)
[![Total Downloads](https://img.shields.io/packagist/dt/flightsadmin/flightadmin.svg?style=flat-square)](https://packagist.org/packages/flightsadmin/flightadmin)

FlightAdmin is a comprehensive flight operations management system designed for airlines to manage their flight operations, load control, and weight & balance calculations.

## Overview

FlightAdmin provides airlines with a centralized platform to manage their entire flight operation process, from scheduling flights to generating load sheets and weight & balance calculations. The system is designed to improve operational efficiency, ensure safety compliance, and streamline communication between different departments.

## Key Features

### Airline Network Management
- Manage airlines, stations, and routes
- Configure airline-specific settings and operational parameters
- Track station details including contact information and hub status

### Flight Scheduling
- Create and manage flight schedules with recurring patterns
- Generate flights automatically based on schedules
- Track flight status throughout the operational lifecycle

### Aircraft Management
- Maintain aircraft fleet information
- Configure aircraft types with detailed specifications
- Manage cabin layouts, seat maps, and cargo hold configurations

### Load Control
- Track passengers, baggage, cargo, and mail
- Manage ULDs (Unit Load Devices) and containers
- Generate loading instructions for ground handling

### Weight & Balance
- Calculate aircraft weight and balance
- Generate trim sheets and load sheets
- Ensure compliance with aircraft limitations

### Crew Management
- Assign flight crew and cabin crew to flights
- Track crew qualifications and duty times
- Configure crew seating positions for weight & balance calculations

## Technical Details

### Built With
- Laravel - PHP framework for the backend
- Livewire - Full-stack framework for dynamic interfaces
- Bootstrap - Frontend CSS framework
- MySQL - Database

### System Requirements
- PHP 8.1 or higher
- MySQL 5.7 or higher
- Composer
- Node.js and NPM

## Installation

1. Clone the repository
```bash
git clone https://github.com/flightsadmin/flightadmin.git
cd flightadmin
```

2. Install dependencies
```bash
composer install
npm install
npm run build
```

3. Configure environment
```bash
cp .env.example .env
php artisan key:generate
```

4. Set up database
```bash
php artisan migrate
php artisan db:seed
```

5. Start the development server
```bash
php artisan serve
```

## Default Users

After seeding the database, the following users will be available:

| User Type | Email | Password |
|-----------|-------|----------|
| Super Admin | super-admin@flightadmin.info | password |
| Admin | admin@flightadmin.info | password |
| User | user@flightadmin.info | password |
| Test User | test@example.com | password |
| Wab Admin | wab@flightadmin.info | password |

## Data Structure

### Core Entities
- **Airlines**: Airline companies operating flights
- **Aircraft Types**: Models of aircraft with specific configurations
- **Aircraft**: Individual aircraft in an airline's fleet
- **Stations**: Airports or locations served by airlines
- **Routes**: Connections between stations
- **Schedules**: Recurring flight patterns
- **Flights**: Individual flight operations

### Operational Entities
- **Passengers**: Travelers on flights
- **Baggage**: Passenger luggage
- **Cargo**: Freight items
- **Containers**: ULDs for baggage and cargo
- **Crew**: Flight and cabin crew members
- **Fuel**: Aircraft fuel loads

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support, please contact support@flightadmin.info or open an issue on the GitHub repository.
