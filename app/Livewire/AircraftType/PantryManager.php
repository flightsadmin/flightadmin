<?php

namespace App\Livewire\AircraftType;

use App\Models\AircraftType;
use Livewire\Component;

class PantryManager extends Component
{
    public AircraftType $aircraftType;

    public $showPantryModal = false;

    public $editingPantry = null;

    public $pantryForm = [
        'code' => '',
        'name' => '',
        'weight' => '',
        'index' => '',
    ];

    public function savePantry()
    {
        $this->validate([
            'pantryForm.code' => 'required|string|max:10',
            'pantryForm.name' => 'required|string|max:255',
            'pantryForm.weight' => 'required|numeric|min:0',
            'pantryForm.index' => 'required|numeric',
        ]);

        $pantries = $this->aircraftType->getSetting('pantries', []);
        $code = strtoupper($this->pantryForm['code']);

        $pantries[$code] = [
            'name' => $this->pantryForm['name'],
            'code' => $code,
            'weight' => (float) $this->pantryForm['weight'],
            'index' => (float) $this->pantryForm['index'],
        ];

        $this->aircraftType->settings()->updateOrCreate(
            [
                'key' => 'pantries',
                'airline_id' => $this->aircraftType->airline_id,
            ],
            [
                'value' => json_encode($pantries),
                'type' => 'json',
                'description' => 'Aircraft Type Pantry Configurations',
            ]
        );

        $this->reset('pantryForm', 'editingPantry', 'showPantryModal');
        $this->dispatch('alert', icon: 'success', message: 'Pantry saved successfully.');
        $this->dispatch('pantry-saved');
    }

    public function editPantry($code)
    {
        $pantry = $this->aircraftType->getPantryDetails($code);
        $this->editingPantry = $code;
        $this->pantryForm = $pantry;
        $this->showPantryModal = true;
    }

    public function deletePantry($code)
    {
        $pantries = $this->aircraftType->getSetting('pantries', []);
        unset($pantries[strtoupper($code)]);

        $this->aircraftType->settings()->updateOrCreate(
            [
                'key' => 'pantries',
                'airline_id' => $this->aircraftType->airline_id,
            ],
            [
                'value' => json_encode($pantries),
                'type' => 'json',
                'description' => 'Aircraft Type Pantry Configurations',
            ]
        );

        $this->dispatch('alert', icon: 'success', message: 'Pantry deleted successfully.');
    }

    public function render()
    {
        return view('livewire.aircraft_type.pantry-manager', [
            'pantries' => $this->aircraftType->getAllPantries(),
        ]);
    }
}
