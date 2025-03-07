<?php

namespace App\Livewire\AircraftType;

use App\Models\AircraftType;
use App\Models\Hold;
use Livewire\Component;

class HoldManager extends Component
{
    public AircraftType $aircraftType;

    public $showHoldModal = false;

    public $editingHold = null;

    public $holdForm = [
        'name' => 'Forward Hold',
        'code' => 'FH',
        'position' => 1,
        'max_weight' => 2000,
        'index' => 0.00001,
        'is_active' => true,
        'positions' => [],
    ];

    protected $rules = [
        'holdForm.name' => 'required|string|max:255',
        'holdForm.code' => 'required|string|max:10',
        'holdForm.position' => 'required|integer|min:1',
        'holdForm.index' => 'required|numeric',
        'holdForm.max_weight' => 'required|numeric|min:0',
        'holdForm.is_active' => 'boolean',
        'holdForm.positions.*.row' => 'required|integer|min:1',
        'holdForm.positions.*.side' => 'nullable|in:L,R',
        'holdForm.positions.*.index' => 'required|numeric',
        'holdForm.positions.*.max_weight' => 'required|numeric|min:0',
        'holdForm.positions.*.is_active' => 'boolean',
    ];

    public function mount(AircraftType $aircraftType)
    {
        $this->aircraftType = $aircraftType;
    }

    public function editHold(Hold $hold)
    {
        $this->editingHold = $hold;
        $this->holdForm = array_merge($hold->toArray(), [
            'positions' => $hold->positions->map(function ($position) {
                return [
                    'row' => $position->row,
                    'side' => $position->side,
                    'index' => $position->index,
                    'max_weight' => $position->max_weight,
                    'is_active' => $position->is_active,
                ];
            })->toArray(),
        ]);
        $this->showHoldModal = true;
    }

    public function updatedHoldFormCode()
    {
        if ($this->holdForm['code'] === 'FH') {
            $this->holdForm['name'] = 'Forward Hold';
            $this->holdForm['position'] = 1;
        } elseif ($this->holdForm['code'] === 'AH') {
            $this->holdForm['name'] = 'Aft Hold';
            $this->holdForm['position'] = 2;
        } elseif ($this->holdForm['code'] === 'BH') {
            $this->holdForm['name'] = 'Bulk Hold';
            $this->holdForm['position'] = 3;
        }
    }

    public function addHoldPosition()
    {
        $lastRow = collect($this->holdForm['positions'])->max('row') ?? 0;

        if ($this->holdForm['code'] === 'BH') {
            $this->holdForm['positions'][] = [
                'row' => $lastRow + 1,
                'side' => null,
                'max_weight' => 2000,
                'index' => 0.00001,
                'is_active' => true,
            ];
        } else {
            $this->holdForm['positions'][] = [
                'row' => $lastRow + 1,
                'side' => 'L',
                'max_weight' => 2000,
                'index' => 0.00001,
                'is_active' => true,
            ];
            $this->holdForm['positions'][] = [
                'row' => $lastRow + 1,
                'side' => 'R',
                'max_weight' => 2000,
                'index' => 0.00001,
                'is_active' => true,
            ];
        }
    }

    public function removeHoldPosition($index)
    {
        unset($this->holdForm['positions'][$index]);
        $this->holdForm['positions'] = array_values($this->holdForm['positions']);
    }

    public function saveHold()
    {
        $this->validate();

        $hold = $this->aircraftType->holds()->updateOrCreate(
            [
                'id' => $this->editingHold?->id,
            ],
            collect($this->holdForm)->except('positions')->toArray()
        );

        $hold->positions()->delete();
        foreach ($this->holdForm['positions'] as $position) {
            $position['code'] = $position['row'].$position['side'];
            $hold->positions()->create($position);
        }

        $this->dispatch('alert', icon: 'success', message: 'Hold saved successfully.');
        $this->dispatch('hold-saved');
        $this->reset('holdForm', 'editingHold', 'showHoldModal');
    }

    public function deleteHold(Hold $hold)
    {
        $hold->positions()->delete();
        $hold->delete();
        $this->dispatch('alert', icon: 'success', message: 'Hold deleted successfully.');
    }

    public function render()
    {
        return view('livewire.aircraft_type.hold-manager', [
            'holds' => $this->aircraftType->holds()->with('positions')->orderBy('position')->get(),
        ]);
    }
}
