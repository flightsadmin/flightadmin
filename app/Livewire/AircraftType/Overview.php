<?php

namespace App\Livewire\AircraftType;

use App\Models\AircraftType;
use Livewire\Component;

class Overview extends Component
{
    public AircraftType $aircraftType;

    public function mount(AircraftType $aircraftType)
    {
        $this->aircraftType = $aircraftType;
    }

    public function render()
    {
        return view('livewire.aircraft_type.overview');
    }
}
