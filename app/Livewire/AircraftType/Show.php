<?php

namespace App\Livewire\AircraftType;

use App\Models\AircraftType;
use Livewire\Component;

class Show extends Component
{
    public AircraftType $aircraftType;

    public $activeTab = 'overview';

    public function mount(AircraftType $aircraft_type)
    {
        $this->aircraftType = $aircraft_type;
    }

    public function render()
    {
        return view('livewire.aircraft_type.show');
    }
}
