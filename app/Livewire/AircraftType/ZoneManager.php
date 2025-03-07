<?php

namespace App\Livewire\AircraftType;

use App\Models\AircraftType;
use App\Models\CabinZone;
use Livewire\Component;

class ZoneManager extends Component
{
    public AircraftType $aircraftType;

    public $showCabinZoneModal = false;

    public $editingZone = null;

    public $zoneForm = [
        'name' => '',
        'max_capacity' => '',
        'index' => '',
        'arm' => '',
    ];

    protected $rules = [
        'zoneForm.name' => 'required|string|max:255',
        'zoneForm.max_capacity' => 'required|numeric|min:0',
        'zoneForm.index' => 'required|numeric',
        'zoneForm.arm' => 'required|numeric',
    ];

    public function mount(AircraftType $aircraftType)
    {
        $this->aircraftType = $aircraftType;
    }

    public function editZone(CabinZone $zone)
    {
        $this->editingZone = $zone;
        $this->zoneForm = [
            'name' => $zone->name,
            'max_capacity' => $zone->max_capacity,
            'index' => $zone->index,
            'arm' => $zone->arm,
        ];
        $this->showCabinZoneModal = true;
    }

    public function saveZone()
    {
        $this->validate($this->rules);

        if ($this->editingZone) {
            $this->editingZone->update($this->zoneForm);
            $message = 'Cabin zone updated successfully.';
        } else {
            $this->aircraftType->cabinZones()->create($this->zoneForm);
            $message = 'Cabin zone created successfully.';
        }

        $this->dispatch('alert', icon: 'success', message: $message);
        $this->dispatch('zone-saved');
        $this->reset('zoneForm', 'editingZone', 'showCabinZoneModal');
    }

    public function deleteZone(CabinZone $zone)
    {
        $zone->delete();
        $this->dispatch('alert', icon: 'success', message: 'Cabin zone deleted successfully.');
    }

    public function render()
    {
        return view('livewire.aircraft_type.zone-manager');
    }
}
