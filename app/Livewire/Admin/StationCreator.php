<?php

namespace App\Livewire\Admin;

use App\Models\Station;
use Livewire\Component;

class StationCreator extends Component
{
    public $showModal = false;

    public $editMode = false;

    // Form fields
    public $stationId;

    public $code;

    public $name;

    public $country;

    public $timezone;

    public $is_active = true;

    // Optional callback event to dispatch after saving
    public $callbackEvent = null;

    protected $rules = [
        'code' => 'required|string|size:3|unique:stations,code',
        'name' => 'required|string|max:255',
        'country' => 'nullable|string|max:255',
        'timezone' => 'nullable|string|max:255',
        'is_active' => 'boolean',
    ];

    public function createStation()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function editStation($id)
    {
        $this->resetForm();
        $this->stationId = $id;
        $this->editMode = true;

        $station = Station::findOrFail($id);
        $this->code = $station->code;
        $this->name = $station->name;
        $this->country = $station->country;
        $this->timezone = $station->timezone;
        $this->is_active = $station->is_active;

        $this->showModal = true;
    }

    public function save()
    {
        if ($this->editMode) {
            $this->rules['code'] = 'required|string|size:3|unique:stations,code,'.$this->stationId;
        }

        $this->validate();

        $stationData = [
            'code' => strtoupper($this->code),
            'name' => $this->name,
            'country' => $this->country,
            'timezone' => $this->timezone,
            'is_active' => $this->is_active,
        ];

        if ($this->editMode) {
            $station = Station::findOrFail($this->stationId);
            $station->update($stationData);
            $message = 'Station updated successfully';
        } else {
            $station = Station::create($stationData);
            $message = 'Station created successfully';
        }

        $this->dispatch('alert', icon: 'success', message: $message);
        $this->showModal = false;
        $this->resetForm();

        // Dispatch callback event if provided
        if ($this->callbackEvent) {
            $this->dispatch($this->callbackEvent, station: $station);
        }

        // Dispatch a general event for any listeners
        $this->dispatch('station-saved', station: $station);
    }

    public function resetForm()
    {
        $this->reset(['stationId', 'code', 'name', 'country', 'timezone', 'is_active', 'editMode']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.station-creator');
    }
}
