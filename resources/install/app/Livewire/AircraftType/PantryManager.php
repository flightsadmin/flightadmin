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
        'name' => '',
        'code' => '',
        'weight' => '',
        'index' => '',
    ];

    protected $rules = [
        'pantryForm.name' => 'required|string|max:255',
        'pantryForm.code' => 'required|string|max:10',
        'pantryForm.weight' => 'required|numeric|min:0',
        'pantryForm.index' => 'required|numeric',
    ];

    public function savePantry()
    {
        $this->validate();

        $code = strtolower($this->pantryForm['code']);

        // Save pantry settings
        $this->aircraftType->settings()->updateOrCreate(
            [
                'key' => "pantry_{$code}_weight",
                'airline_id' => $this->aircraftType->airline_id,
            ],
            [
                'value' => $this->pantryForm['weight'],
                'type' => 'float',
                'description' => "{$this->pantryForm['name']} weight (kg)",
            ]
        );

        $this->aircraftType->settings()->updateOrCreate(
            [
                'key' => "pantry_{$code}_name",
                'airline_id' => $this->aircraftType->airline_id,
            ],
            [
                'value' => $this->pantryForm['name'],
                'type' => 'string',
                'description' => 'Pantry name',
            ]
        );

        $this->aircraftType->settings()->updateOrCreate(
            [
                'key' => "pantry_{$code}_index",
                'airline_id' => $this->aircraftType->airline_id,
            ],
            [
                'value' => $this->pantryForm['index'],

                'type' => 'float',
                'description' => "{$this->pantryForm['name']} index",
            ]
        );

        $this->dispatch('alert', icon: 'success', message: 'Pantry saved successfully.');
        $this->dispatch('pantry-saved');
        $this->reset('pantryForm', 'editingPantry', 'showPantryModal');
    }

    public function editPantry($code)
    {
        $code = strtolower($code);
        $this->editingPantry = $code;

        $this->pantryForm = [
            'name' => $this->aircraftType->getSetting("pantry_{$code}_name"),
            'code' => $code,
            'weight' => $this->aircraftType->getSetting("pantry_{$code}_weight"),
            'index' => $this->aircraftType->getSetting("pantry_{$code}_index"),
        ];

        $this->showPantryModal = true;
    }

    public function deletePantry($code)
    {
        $code = strtolower($code);

        $this->aircraftType->settings()
            ->whereIn('key', [
                "pantry_{$code}_name",
                "pantry_{$code}_weight",
                "pantry_{$code}_index",
            ])
            ->delete();

        $this->dispatch('alert', icon: 'success', message: 'Pantry deleted successfully.');
    }

    public function render()
    {
        $pantries = collect($this->aircraftType->settings()
            ->where('key', 'like', 'pantry_%_name')
            ->get())
            ->map(function ($setting) {
                $code = str_replace(['pantry_', '_name'], '', $setting->key);

                return [
                    'code' => $code,
                    'name' => $setting->value,
                    'weight' => $this->aircraftType->getSetting("pantry_{$code}_weight"),
                    'index' => $this->aircraftType->getSetting("pantry_{$code}_index"),
                ];
            });

        return view('livewire.aircraft_type.pantry-manager', [
            'pantries' => $pantries,
        ]);
    }
}
