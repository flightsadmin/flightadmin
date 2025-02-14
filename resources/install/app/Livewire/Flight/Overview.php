<?php

namespace App\Livewire\Flight;

use App\Models\Flight;
use Livewire\Component;

class Overview extends Component
{
    public Flight $flight;

    public function mount(Flight $flight)
    {
        $this->flight = $flight->load(['airline', 'aircraft', 'crew', 'passengers', 'baggage', 'cargo']);
    }

    public function updateStatus($status)
    {
        $this->flight->update(['status' => $status]);
    }

    public function updateRegistration($reg)
    {
        $this->flight->update(['aircraft_id' => $reg]);
    }

    public function render()
    {
        return view('livewire.flights.overview')->layout('components.layouts.app');
    }
}
