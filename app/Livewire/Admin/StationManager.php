<?php

namespace App\Livewire\Admin;

use App\Models\Station;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class StationManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $showModal = false;
    public $editMode = false;

    // Form fields
    public $stationId;
    public $code;
    public $name;
    public $country;
    public $timezone;
    public $is_active = true;

    protected $rules = [
        'code' => 'required|string|size:3|unique:stations,code',
        'name' => 'required|string|max:255',
        'country' => 'nullable|string|max:255',
        'timezone' => 'nullable|string|max:255',
        'is_active' => 'boolean',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

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
            $this->rules['code'] = 'required|string|size:3|unique:stations,code,' . $this->stationId;
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
            Station::findOrFail($this->stationId)->update($stationData);
            $message = 'Station updated successfully';
        } else {
            Station::create($stationData);
            $message = 'Station created successfully';
        }

        $this->dispatch('alert', icon: 'success', message: $message);
        $this->showModal = false;
        $this->resetForm();
    }

    public function toggleActive($id)
    {
        $station = Station::findOrFail($id);
        $station->update(['is_active' => !$station->is_active]);

        $status = $station->is_active ? 'activated' : 'deactivated';
        $this->dispatch('alert', icon: 'success', message: "Station {$status} successfully");
    }

    public function resetForm()
    {
        $this->reset(['stationId', 'code', 'name', 'country', 'timezone', 'is_active', 'editMode']);
        $this->resetValidation();
    }

    public function render()
    {
        $stations = Station::when($this->search, function ($query) {
            $query->where('code', 'like', '%' . $this->search . '%')
                ->orWhere('name', 'like', '%' . $this->search . '%')
                ->orWhere('country', 'like', '%' . $this->search . '%');
        })
            ->orderBy('code')
            ->paginate(10);

        return view('livewire.admin.station-manager', [
            'stations' => $stations,
        ]);
    }

    #[On('station-saved')]
    public function handleStationSaved($station)
    {
        // Refresh the stations list
        $this->dispatch('alert', icon: 'success', message: 'Station saved successfully');
    }
}