<?php

namespace App\Livewire\Airline;

use App\Models\Airline;
use App\Models\Station;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class StationManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public Airline $airline;
    public $search = '';
    public $showModal = false;
    public $showAssignModal = false;

    // Form fields for station assignment
    public $station_id;
    public $is_hub = false;
    public $contact_email;
    public $contact_phone;
    public $notes;

    protected $rules = [
        'station_id' => 'required|exists:stations,id',
        'is_hub' => 'boolean',
        'contact_email' => 'nullable|email',
        'contact_phone' => 'nullable|string|max:20',
        'notes' => 'nullable|string',
    ];

    public function mount(Airline $airline)
    {
        $this->airline = $airline;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function openAssignModal()
    {
        $this->resetForm();

        // Ensure we have the latest stations
        $this->dispatch('$refresh');

        $this->showAssignModal = true;
    }

    public function editAssignment($stationId)
    {
        $this->resetForm();

        $pivot = $this->airline->stations()->where('station_id', $stationId)->first()->pivot;

        $this->station_id = $stationId;
        $this->is_hub = $pivot->is_hub;
        $this->contact_email = $pivot->contact_email;
        $this->contact_phone = $pivot->contact_phone;
        $this->notes = $pivot->notes;

        $this->showAssignModal = true;
    }

    public function saveAssignment()
    {
        $this->validate();

        // Check if station is already assigned
        $exists = $this->airline->stations()->where('station_id', $this->station_id)->exists();

        $data = [
            'is_hub' => $this->is_hub,
            'contact_email' => $this->contact_email,
            'contact_phone' => $this->contact_phone,
            'notes' => $this->notes,
        ];

        if ($exists) {
            $this->airline->stations()->updateExistingPivot($this->station_id, $data);
            $message = 'Station assignment updated successfully';
        } else {
            $this->airline->stations()->attach($this->station_id, $data);
            $message = 'Station assigned successfully';
        }

        $this->dispatch('alert', icon: 'success', message: $message);
        $this->showAssignModal = false;
        $this->resetForm();
    }

    public function removeStation($stationId)
    {
        $this->airline->stations()->detach($stationId);
        $this->dispatch('alert', icon: 'success', message: 'Station removed from airline');
    }

    public function toggleHub($stationId)
    {
        $station = $this->airline->stations()->where('station_id', $stationId)->first();
        $this->airline->stations()->updateExistingPivot($stationId, [
            'is_hub' => !$station->pivot->is_hub
        ]);

        $status = !$station->pivot->is_hub ? 'set as hub' : 'removed as hub';
        $this->dispatch('alert', icon: 'success', message: "Station {$status} successfully");
    }

    public function resetForm()
    {
        $this->reset(['station_id', 'is_hub', 'contact_email', 'contact_phone', 'notes']);
        $this->resetValidation();
    }

    #[On('station-saved')]
    public function handleStationSaved($station)
    {
        // Refresh the available stations list
        $this->dispatch('alert', icon: 'info', message: 'New station created. You can now assign it to this airline.');
    }

    public function render()
    {
        // Get all stations assigned to this airline
        $assignedStations = $this->airline->stations()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('code', 'like', '%' . $this->search . '%')
                        ->orWhere('name', 'like', '%' . $this->search . '%')
                        ->orWhere('country', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('is_hub', 'desc')
            ->orderBy('code')
            ->paginate(10);

        // Get all stations for the dropdown
        $availableStations = Station::where('is_active', true)
            ->whereNotIn('id', $this->airline->stations()->pluck('stations.id'))
            ->orderBy('code')
            ->get();

        return view('livewire.airline.station-manager', [
            'assignedStations' => $assignedStations,
            'availableStations' => $availableStations,
        ]);
    }
}