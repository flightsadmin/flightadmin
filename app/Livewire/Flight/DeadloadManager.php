<?php

namespace App\Livewire\Flight;

use App\Models\Flight;
use Livewire\Component;

class DeadloadManager extends Component
{
    public Flight $flight;

    public $deadloadItems = [];

    public $showModal = false;

    public $unsavedChanges = false;

    // Form fields
    public $newItem = [
        'pieces' => 1,
        'weight' => 0,
        'position' => null,
        'type' => 'cargo',
    ];

    public $positions = [];

    public $editingIndex = null;

    public function mount(Flight $flight)
    {
        $this->flight = $flight;
        $this->loadDeadloadItems();
        $this->loadPositions();
    }

    protected function loadPositions()
    {
        // Get all bulk positions from the aircraft
        $bulkHolds = $this->flight->aircraft->type->holds()
            ->where('name', 'like', '%Bulk%')
            ->with('positions')
            ->get();

        $this->positions = $bulkHolds->flatMap(function ($hold) {
            return $hold->positions->map(function ($position) use ($hold) {
                return [
                    'id' => $position->id,
                    'code' => $position->code,
                    'hold_name' => $hold->name,
                ];
            });
        })->toArray();
    }

    protected function loadDeadloadItems()
    {
        // Load deadload items from settings table
        $deadloadSetting = $this->flight->settings()
            ->where('key', 'manual_deadload')
            ->first();

        if ($deadloadSetting) {
            $this->deadloadItems = json_decode($deadloadSetting->value, true) ?: [];
        }
    }

    public function saveDeadloadItems()
    {
        $this->flight->settings()->updateOrCreate(
            [
                'key' => 'manual_deadload',
                'airline_id' => $this->flight->airline_id,
            ],
            [
                'value' => json_encode($this->deadloadItems),
                'type' => 'json',
                'description' => 'Deadload items - '.$this->flight->flight_number,
            ]
        );

        $this->unsavedChanges = false;
        $this->dispatch('deadload-updated');
        $this->dispatch('alert', icon: 'success', message: 'Deadload items saved successfully');
    }

    public function openModal($index = null)
    {
        if ($index !== null) {
            $this->editingIndex = $index;
            $this->newItem = $this->deadloadItems[$index];
        } else {
            $this->editingIndex = null;
            $this->newItem = [
                'pieces' => 1,
                'weight' => 10,
                'position' => null,
                'type' => 'cargo',
            ];
        }

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->newItem = [
            'pieces' => 1,
            'weight' => 10,
            'position' => null,
            'type' => 'cargo',
        ];
        $this->editingIndex = null;
    }

    public function addDeadloadItem()
    {
        $this->validate([
            'newItem.pieces' => 'required|integer|min:1',
            'newItem.weight' => 'required|numeric|min:0.1',
            'newItem.position' => 'nullable',
            'newItem.type' => 'required|in:cargo,baggage',
        ]);

        if ($this->editingIndex !== null) {
            $this->deadloadItems[$this->editingIndex] = $this->newItem;
        } else {
            $this->deadloadItems[] = $this->newItem;
        }

        $this->unsavedChanges = true;
        $this->closeModal();
        $this->dispatch('alert', icon: 'info', message: 'Deadload item added. Click "Save Changes" to apply.');
    }

    public function removeDeadloadItem($index)
    {
        unset($this->deadloadItems[$index]);
        $this->deadloadItems = array_values($this->deadloadItems);
        $this->unsavedChanges = true;
        $this->dispatch('alert', icon: 'info', message: 'Deadload item removed. Click "Save Changes" to apply.');
    }

    public function getTotalWeightProperty()
    {
        return collect($this->deadloadItems)->sum(function ($item) {
            return $item['weight'] * $item['pieces'];
        });
    }

    public function getTotalPiecesProperty()
    {
        return collect($this->deadloadItems)->sum('pieces');
    }

    public function render()
    {
        return view('livewire.flights.deadload-manager');
    }
}
