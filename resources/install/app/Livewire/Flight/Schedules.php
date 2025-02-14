<?php

namespace App\Livewire\Flight;

use Carbon\Carbon;
use App\Models\Flight;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class Schedules extends Component
{
    use WithFileUploads, WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    public $flightNumbers = [], $selectedDays = [], $flightFields = [], $startDate, $endDate, $file, $selectedFlights = [];

    public function render()
    {
        $airlines = Airline::all();
        $flights = Flight::latest()->paginate();
        return view('livewire.flight.schedules.view', compact('airlines', 'flights'))->extends('components.layouts.admin');
    }

    public function mount()
    {
        $this->startDate = Carbon::now()->format('Y-m-d');
        $this->endDate = Carbon::now()->addDays(30)->format('Y-m-d');
    }

    public function addFlights()
    {
        $this->flightNumbers[] = rand(100, 999);
    }

    public function removeFlights($index)
    {
        unset($this->flightNumbers[$index]);
        $this->flightNumbers = array_values($this->flightNumbers);
    }

    public function deleteSelected()
    {
        Flight::whereIn('id', $this->selectedFlights)->delete();
        $this->dispatch(
            'closeModal',
            icon: 'warning',
            message: 'Selected Flights Deleted Successfully.',
        );
        $this->reset(['selectedFlights']);
    }

    public function createFlights()
    {
        foreach ($this->selectedDays as $selectedDay) {
            list($flightNumber, $day) = explode('-', $selectedDay);

            // Calculate the first occurrence of the selected day within the date range
            $date = Carbon::parse($this->startDate)->next($day);
            if ($date->lt($this->startDate)) {
                $date = $date->next($day);
            }

            // Create flights for each occurrence of the selected day within the date range
            while ($date->lte($this->endDate)) {
                $flight = new Flight;
                $flight->airline_id = strtoupper($this->flightFields[$flightNumber]['airline_id']);
                $flight->flight_no = strtoupper($this->flightFields[$flightNumber]['flight_no']);
                $flight->registration = '';
                $flight->origin = strtoupper($this->flightFields[$flightNumber]['origin'] ?? 'DOH');
                $flight->destination = strtoupper($this->flightFields[$flightNumber]['destination'] ?? 'MCT');
                $flight->scheduled_time_arrival = $date->format('Y-m-d ') . $this->flightFields[$flightNumber]['arrival'] ?? '00:00';
                $flight->scheduled_time_departure = $date->format('Y-m-d ') . $this->flightFields[$flightNumber]['departure'] ?? '00:00';
                $flight->flight_type = strtoupper($this->flightFields[$flightNumber]['flight_type'] ?? 'departure');
                $flight->save();
                $date = $date->next($day);
            }
        }
        $this->dispatch(
            'closeModal',
            icon: 'success',
            message: 'Schedule Created Successfully.',
        );
        $this->reset(['selectedDays', 'flightNumbers', 'flightFields']);
        return $this->redirect(route('admin.flights'), true);
    }
}