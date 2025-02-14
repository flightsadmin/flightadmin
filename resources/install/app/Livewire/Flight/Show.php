<?php

namespace App\Livewire\Flight;

use App\Models\Flight;
use Livewire\Component;

class Show extends Component
{
    public Flight $flight;

    public $activeTab;

    public function mount(Flight $flight)
    {
        $this->flight = $flight->load(['airline', 'aircraft', 'crew', 'passengers', 'baggage', 'cargo', 'fuel']);
        $this->activeTab = session('flight_tab', 'overview');
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        session(['flight_tab' => $tab]);
    }

    public function render()
    {
        return view('livewire.flights.show', [
            'baggage_count' => $this->flight->baggage->count(),
            'cargo_count' => $this->flight->cargo->count(),
            'passengers_count' => $this->flight->passengers->count(),
            'crew_count' => $this->flight->crew->count(),
            'containers_count' => $this->flight->containers->count(),
        ]);
    }
}
