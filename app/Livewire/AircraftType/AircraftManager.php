<?php

namespace App\Livewire\AircraftType;

use App\Models\Aircraft;
use App\Models\AircraftType;
use Livewire\Component;
use Livewire\WithPagination;

class AircraftManager extends Component
{
    use WithPagination;

    public $paginationTheme = 'bootstrap';
    public AircraftType $aircraftType;

    public $showAircraftModal = false;

    public $editingAircraft = null;

    public $aircraftForm = [
        'registration_number' => '',
        'basic_weight' => '',
        'basic_index' => '',
        'remarks' => '',
        'active' => true,
    ];

    public function mount(AircraftType $aircraftType)
    {
        $this->aircraftType = $aircraftType;
    }

    public function editAircraft(Aircraft $aircraft)
    {
        $this->editingAircraft = $aircraft;
        $this->aircraftForm = [
            'registration_number' => $aircraft->registration_number,
            'basic_weight' => $aircraft->basic_weight,
            'basic_index' => $aircraft->basic_index,
            'remarks' => $aircraft->remarks,
            'active' => $aircraft->active,
        ];
        $this->showAircraftModal = true;
    }

    public function saveAircraft()
    {
        $this->validate([
            'aircraftForm.registration_number' => 'required|string|max:10',
            'aircraftForm.basic_weight' => 'required|integer|min:0',
            'aircraftForm.basic_index' => 'required|numeric|min:0',
            'aircraftForm.remarks' => 'nullable|string',
            'aircraftForm.active' => 'boolean',
        ]);

        $data = array_merge($this->aircraftForm, [
            'airline_id' => $this->aircraftType->airline_id,
            'aircraft_type_id' => $this->aircraftType->id,
        ]);

        $this->aircraftType->aircraft()->updateOrCreate(
            [
                'id' => $this->editingAircraft?->id,
            ],
            $data
        );

        $this->dispatch('alert', icon: 'success', message: 'Aircraft saved successfully.');
        $this->dispatch('aircraft-saved');
        $this->reset('aircraftForm', 'editingAircraft', 'showAircraftModal');
    }

    public function deleteAircraft(Aircraft $aircraft)
    {
        $aircraft->delete();
        $this->dispatch('alert', icon: 'success', message: 'Aircraft deleted successfully.');
    }

    public function render()
    {
        return view('livewire.aircraft_type.aircraft-manager', [
            'aircraft' => $this->aircraftType->aircraft()
                ->with('airline')
                ->orderBy('registration_number')
                ->paginate(10),
        ]);
    }
}
