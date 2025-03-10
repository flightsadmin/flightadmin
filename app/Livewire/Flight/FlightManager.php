<?php

namespace App\Livewire\Flight;

use App\Models\Route;
use App\Models\Flight;
use App\Models\Airline;
use App\Models\Station;
use Livewire\Component;
use App\Models\Aircraft;
use Livewire\WithPagination;

class FlightManager extends Component
{
    use WithPagination;

    public $paginationTheme = 'bootstrap';

    public $showModal = false;

    public $editMode = false;

    public $search = '';

    public $status = '';

    public $airline_id = '';

    public $date = '';

    public $schedule_id = '';

    public $flight;

    public $flight_number = '';

    public $aircraft_id = '';

    public $route_id = '';

    public $departure_station_id = '';

    public $arrival_station_id = '';

    public $departure_airport = '';

    public $arrival_airport = '';

    public $scheduled_departure_time = '';

    public $scheduled_arrival_time = '';

    protected $rules = [
        'flight_number' => 'required|string|max:10',
        'aircraft_id' => 'nullable|exists:aircraft,id',
        'airline_id' => 'required|exists:airlines,id',
        'route_id' => 'nullable|exists:routes,id',
        'departure_station_id' => 'required_without:route_id|exists:stations,id',
        'arrival_station_id' => 'required_without:route_id|exists:stations,id',
        'scheduled_departure_time' => 'required|date',
        'scheduled_arrival_time' => 'required|date|after:scheduled_departure_time',
    ];

    public function mount()
    {
        // Check if schedule_id is in the query string
        $this->schedule_id = request()->query('schedule', '');
    }

    public function createFlight()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function editFlight(Flight $flight)
    {
        $this->flight = $flight;
        $this->flight_number = $flight->flight_number;
        $this->aircraft_id = $flight->aircraft_id;
        $this->airline_id = $flight->airline_id;
        $this->route_id = $flight->route_id;
        $this->departure_station_id = $flight->departure_station_id;
        $this->arrival_station_id = $flight->arrival_station_id;
        $this->departure_airport = $flight->departure_airport;
        $this->arrival_airport = $flight->arrival_airport;
        $this->scheduled_departure_time = $flight->scheduled_departure_time->format('Y-m-d\TH:i');
        $this->scheduled_arrival_time = $flight->scheduled_arrival_time->format('Y-m-d\TH:i');

        $this->editMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        $validated = $this->validate();

        // If route is selected, get departure and arrival stations from route
        if (!empty($validated['route_id'])) {
            $route = Route::findOrFail($validated['route_id']);
            $validated['departure_station_id'] = $route->departure_station_id;
            $validated['arrival_station_id'] = $route->arrival_station_id;

            // Get station codes for departure_airport and arrival_airport fields
            $departureStation = Station::find($route->departure_station_id);
            $arrivalStation = Station::find($route->arrival_station_id);

            if ($departureStation && $arrivalStation) {
                $validated['departure_airport'] = $departureStation->code;
                $validated['arrival_airport'] = $arrivalStation->code;
            }
        } else {
            // If no route selected, get airport codes from stations
            $departureStation = Station::find($validated['departure_station_id']);
            $arrivalStation = Station::find($validated['arrival_station_id']);

            if ($departureStation && $arrivalStation) {
                $validated['departure_airport'] = $departureStation->code;
                $validated['arrival_airport'] = $arrivalStation->code;
            }
        }

        if ($this->editMode) {
            $this->flight->update($validated);
            $message = 'Flight updated successfully.';
        } else {
            Flight::create($validated);
            $message = 'Flight created successfully.';
        }

        $this->dispatch('alert', icon: 'success', message: $message);
        $this->dispatch('flight-saved');
    }

    private function resetForm()
    {
        $this->reset([
            'flight',
            'flight_number',
            'airline_id',
            'aircraft_id',
            'route_id',
            'departure_station_id',
            'arrival_station_id',
            'departure_airport',
            'arrival_airport',
            'scheduled_departure_time',
            'scheduled_arrival_time',
        ]);
    }

    public function updateStatus(Flight $flight, $status)
    {
        if (in_array($status, ['scheduled', 'boarding', 'departed', 'arrived', 'cancelled'])) {
            $flight->update(['status' => $status]);
            $this->dispatch(
                'alert',
                icon: 'success',
                message: 'Flight status updated successfully.'
            );
        }
    }

    public function render()
    {
        $flights = Flight::query()
            ->with(['aircraft.airline', 'aircraft.type', 'schedule'])
            ->when($this->search, function ($query) {
                $query->whereAny(['flight_number', 'departure_airport', 'arrival_airport'], 'like', '%' . $this->search . '%');
            })
            ->when($this->status, fn($query) => $query->where('status', $this->status))
            ->when($this->airline_id, fn($query) => $query->where('airline_id', $this->airline_id))
            ->when($this->date, fn($query) => $query->whereDate('scheduled_departure_time', $this->date))
            ->when($this->schedule_id, fn($query) => $query->where('schedule_id', $this->schedule_id))
            ->orderBy('scheduled_departure_time')
            ->paginate(10);

        return view('livewire.flights.flight-manager', [
            'flights' => $flights,
            'airlines' => Airline::orderBy('name')->get(),
            'aircraft' => Aircraft::with('airline')->orderBy('registration_number')->get(),
            'schedules' => \App\Models\Schedule::orderBy('flight_number')->get(),
            'stations' => Station::orderBy('code')->get(),
            'routes' => Route::where('airline_id', $this->airline_id)->orderBy('departure_station_id')->get(),
        ]);
    }

    public function onRouteChange($routeId)
    {
        if (!empty($routeId)) {
            $route = Route::find($routeId);
            if ($route) {
                $this->departure_station_id = $route->departure_station_id;
                $this->arrival_station_id = $route->arrival_station_id;

                // Update airport codes
                $departureStation = Station::find($route->departure_station_id);
                $arrivalStation = Station::find($route->arrival_station_id);

                if ($departureStation && $arrivalStation) {
                    $this->departure_airport = $departureStation->code;
                    $this->arrival_airport = $arrivalStation->code;
                }
            }
        }
    }
}
