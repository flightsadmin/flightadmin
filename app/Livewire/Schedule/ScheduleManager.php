<?php

namespace App\Livewire\Schedule;

use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Schedule;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class ScheduleManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $showModal = false;

    public $showFlightsModal = false;

    public $editMode = false;

    public $search = '';

    public $airline_id = '';

    public $status = '';

    // Form fields
    public $schedule;

    public $flight_number = '';

    public $aircraft_id = null;

    public $departure_airport = '';

    public $arrival_airport = '';

    public $departure_time = '';

    public $arrival_time = '';

    public $start_date = '';

    public $end_date = '';

    public $days_of_week = [];

    public $is_active = true;

    // For flights modal
    public $selectedSchedule = null;

    public $scheduleFlights = [];

    // Days of week checkboxes
    public $dayOptions = [
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
    ];

    protected $rules = [
        'flight_number' => 'required|string|max:10',
        'aircraft_id' => 'nullable|exists:aircraft,id',
        'airline_id' => 'required|exists:airlines,id',
        'departure_airport' => 'required|string|size:3',
        'arrival_airport' => 'required|string|size:3',
        'departure_time' => 'required',
        'arrival_time' => 'required',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'days_of_week' => 'required|array|min:1',
        'days_of_week.*' => 'integer|between:0,6',
        'is_active' => 'boolean',
    ];

    public function createSchedule()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;

        // Set default values
        $this->start_date = Carbon::today()->format('Y-m-d');
        $this->end_date = Carbon::today()->addMonths(3)->format('Y-m-d');
        $this->days_of_week = [1, 2, 3, 4, 5]; // Default to weekdays
    }

    public function editSchedule(Schedule $schedule)
    {
        $this->schedule = $schedule;
        $this->flight_number = $schedule->flight_number;
        $this->aircraft_id = $schedule->aircraft_id;
        $this->airline_id = $schedule->airline_id;
        $this->departure_airport = $schedule->departure_airport;
        $this->arrival_airport = $schedule->arrival_airport;
        $this->departure_time = $schedule->departure_time->format('H:i');
        $this->arrival_time = $schedule->arrival_time->format('H:i');
        $this->start_date = $schedule->start_date->format('Y-m-d');
        $this->end_date = $schedule->end_date->format('Y-m-d');
        $this->days_of_week = $schedule->days_of_week;
        $this->is_active = $schedule->is_active;

        $this->editMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        $validated = $this->validate();

        // Convert time strings to proper format
        $validated['departure_time'] = Carbon::parse($validated['departure_time'])->format('H:i:s');
        $validated['arrival_time'] = Carbon::parse($validated['arrival_time'])->format('H:i:s');

        if ($this->editMode) {
            $this->schedule->update($validated);
            $schedule = $this->schedule;
            $message = 'Flight schedule updated successfully.';
        } else {
            $schedule = Schedule::create($validated);
            $message = 'Flight schedule created successfully.';
        }

        $this->showModal = false;
        $this->dispatch('alert', icon: 'success', message: $message);
        $this->dispatch('schedule-saved');
    }

    public function generateFlights(Schedule $schedule)
    {
        $createdFlights = $schedule->generateFlights();
        $count = count($createdFlights);

        $this->dispatch('alert', icon: 'success', message: "{$count} flights generated successfully.");
    }

    public function toggleStatus(Schedule $schedule)
    {
        $schedule->update(['is_active' => ! $schedule->is_active]);

        $status = $schedule->is_active ? 'activated' : 'deactivated';
        $this->dispatch('alert', icon: 'success', message: "Schedule {$status} successfully.");
    }

    public function showFlights(Schedule $schedule)
    {
        $this->selectedSchedule = $schedule;
        $this->scheduleFlights = $schedule->flights()
            ->with(['aircraft'])
            ->orderBy('scheduled_departure_time')
            ->take(10)
            ->get();
        $this->showFlightsModal = true;
    }

    private function resetForm()
    {
        $this->reset([
            'schedule',
            'flight_number',
            'aircraft_id',
            'airline_id',
            'departure_airport',
            'arrival_airport',
            'departure_time',
            'arrival_time',
            'start_date',
            'end_date',
            'days_of_week',
            'is_active',
        ]);
    }

    public function render()
    {
        $schedules = Schedule::query()
            ->with(['airline', 'aircraft.type'])
            ->when($this->search, function ($query) {
                $query->whereAny(['flight_number', 'departure_airport', 'arrival_airport'], 'like', '%'.$this->search.'%');
            })
            ->when($this->airline_id, fn ($query) => $query->where('airline_id', $this->airline_id))
            ->when($this->status !== '', function ($query) {
                $status = $this->status === 'active';
                $query->where('is_active', $status);
            })
            ->orderBy('flight_number')
            ->paginate(10);

        return view('livewire.schedule.schedule-manager', [
            'schedules' => $schedules,
            'airlines' => Airline::orderBy('name')->get(),
            'aircraft' => Aircraft::with('airline')->orderBy('registration_number')->get(),
        ]);
    }
}
