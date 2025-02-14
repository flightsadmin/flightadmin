<?php

namespace App\Livewire\Flight;

use App\Models\Flight;
use App\Models\Aircraft;
use App\Models\Airline;
use Livewire\Component;
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

    public $flight;
    public $flight_number = '';
    public $aircraft_id = '';
    public $departure_airport = '';
    public $arrival_airport = '';
    public $scheduled_departure_time = '';
    public $scheduled_arrival_time = '';

    protected $rules = [
        'flight_number' => 'required|string|max:10',
        'aircraft_id' => 'required|exists:aircraft,id',
        'airline_id' => 'required|exists:airlines,id',
        'departure_airport' => 'required|string|size:3',
        'arrival_airport' => 'required|string|size:3',
        'scheduled_departure_time' => 'required|date',

        'scheduled_arrival_time' => 'required|date|after:scheduled_departure_time',
    ];

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

        $flight = Flight::updateOrCreate(
            ['flight_number' => $validated['flight_number'], 'airline_id' => $validated['airline_id']],
            $validated
        );

        $this->dispatch('alert', icon: 'success', message: 'Flight saved successfully.');
        $this->dispatch('flight-saved');
    }

    private function resetForm()
    {
        $this->reset([
            'flight',
            'flight_number',
            'airline_id',
            'aircraft_id',
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
            ->with(['aircraft.airline', 'aircraft.type'])
            ->when($this->search, function ($query) {
                $query->whereAny(['flight_number', 'departure_airport', 'arrival_airport'], 'like', '%' . $this->search . '%');
            })
            ->when($this->status, fn($query) => $query->where('status', $this->status))
            ->when($this->airline_id, fn($query) => $query->where('airline_id', $this->airline_id))
            ->when($this->date, fn($query) => $query->whereDate('scheduled_departure_time', $this->date))
            ->orderBy('scheduled_departure_time')
            ->paginate(10);

        return view('livewire.flights.flight-manager', [
            'flights' => $flights,
            'airlines' => Airline::orderBy('name')->get(),
            'aircraft' => Aircraft::with('airline')->orderBy('registration_number')->get(),
        ]);

    }
}