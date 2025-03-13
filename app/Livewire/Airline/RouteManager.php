<?php

namespace App\Livewire\Airline;

use App\Models\Airline;
use App\Models\Route;
use Livewire\Component;
use Livewire\WithPagination;

class RouteManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public Airline $airline;

    public $search = '';

    public $departureFilter = '';

    public $arrivalFilter = '';

    public $showModal = false;

    public $editMode = false;

    // Form fields
    public $routeId;

    public $departure_station_id;

    public $arrival_station_id;

    public $flight_time;

    public $distance;

    public $is_active = true;

    protected $rules = [
        'departure_station_id' => 'required|exists:stations,id',
        'arrival_station_id' => 'required|exists:stations,id|different:departure_station_id',
        'flight_time' => 'nullable|integer|min:1',
        'distance' => 'nullable|integer|min:1',
        'is_active' => 'boolean',
    ];

    public function mount(Airline $airline)
    {
        $this->airline = $airline;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedDepartureFilter()
    {
        $this->resetPage();
    }

    public function updatedArrivalFilter()
    {
        $this->resetPage();
    }

    public function createRoute()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function editRoute($id)
    {
        $this->resetForm();
        $this->routeId = $id;
        $this->editMode = true;

        $route = Route::findOrFail($id);
        $this->departure_station_id = $route->departure_station_id;
        $this->arrival_station_id = $route->arrival_station_id;
        $this->flight_time = $route->flight_time;
        $this->distance = $route->distance;
        $this->is_active = $route->is_active;

        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        // Check for duplicate route
        $query = Route::where('airline_id', $this->airline->id)
            ->where('departure_station_id', $this->departure_station_id)
            ->where('arrival_station_id', $this->arrival_station_id);

        if ($this->editMode) {
            $query->where('id', '!=', $this->routeId);
        }

        $exists = $query->exists();

        if ($exists) {
            $this->addError('departure_station_id', 'This route already exists for this airline.');

            return;
        }

        $routeData = [
            'airline_id' => $this->airline->id,
            'departure_station_id' => $this->departure_station_id,
            'arrival_station_id' => $this->arrival_station_id,
            'flight_time' => $this->flight_time,
            'distance' => $this->distance,
            'is_active' => $this->is_active,
        ];

        if ($this->editMode) {
            Route::findOrFail($this->routeId)->update($routeData);
            $message = 'Route updated successfully';
        } else {
            Route::create($routeData);
            $message = 'Route created successfully';
        }

        $this->dispatch('alert', icon: 'success', message: $message);
        $this->showModal = false;
        $this->resetForm();
    }

    public function toggleActive($id)
    {
        $route = Route::findOrFail($id);
        $route->update(['is_active' => ! $route->is_active]);

        $status = $route->is_active ? 'activated' : 'deactivated';
        $this->dispatch('alert', icon: 'success', message: "Route {$status} successfully");
    }

    public function deleteRoute($id)
    {
        Route::findOrFail($id)->delete();
        $this->dispatch('alert', icon: 'success', message: 'Route deleted successfully');
    }

    public function resetForm()
    {
        $this->reset([
            'routeId',
            'departure_station_id',
            'arrival_station_id',
            'flight_time',
            'distance',
            'is_active',
            'editMode',
        ]);
        $this->resetValidation();
    }

    public function render()
    {
        $routes = $this->airline->routes()
            ->with(['departureStation', 'arrivalStation'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('departureStation', function ($sq) {
                        $sq->where('code', 'like', '%'.$this->search.'%')
                            ->orWhere('name', 'like', '%'.$this->search.'%');
                    })
                        ->orWhereHas('arrivalStation', function ($sq) {
                            $sq->where('code', 'like', '%'.$this->search.'%')
                                ->orWhere('name', 'like', '%'.$this->search.'%');
                        });
                });
            })
            ->when($this->departureFilter, function ($query) {
                $query->where('departure_station_id', $this->departureFilter);
            })
            ->when($this->arrivalFilter, function ($query) {
                $query->where('arrival_station_id', $this->arrivalFilter);
            })
            ->orderBy('departure_station_id')
            ->orderBy('arrival_station_id')
            ->paginate(10);

        // Get all stations assigned to this airline for the dropdowns
        $stations = $this->airline->stations()->orderBy('code')->get();

        return view('livewire.airline.route-manager', [
            'routes' => $routes,
            'stations' => $stations,
        ]);
    }
}
