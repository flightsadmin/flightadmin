<?php

namespace App\Livewire\Crew;

use App\Models\Crew;
use App\Models\Flight;
use Livewire\Component;

class Manager extends Component
{
    public Flight $flight;

    public $search = '';

    public $position = '';

    public $showCrewModal = false;

    public $showAvailableModal = false;

    public $editingCrew = null;

    // Form fields
    public $form = [
        'name' => '',
        'position' => 'cabin_crew',
        'employee_id' => '',
        'notes' => '',
    ];

    protected $rules = [
        'form.name' => 'required|string|max:255',
        'form.position' => 'required|string|max:255',
        'form.employee_id' => 'nullable|string|max:255',
        'form.notes' => 'nullable|string',
    ];

    public function mount(Flight $flight)
    {
        $this->flight = $flight;
    }

    public function showAvailableCrew()
    {
        $this->reset(['search', 'position']);
        $this->showAvailableModal = true;
    }

    public function createCrew()
    {
        $this->reset('form', 'editingCrew');
        $this->showCrewModal = true;
    }

    public function editCrew(Crew $crew)
    {
        $this->editingCrew = $crew;
        $this->form = $crew->only(['name', 'position', 'employee_id', 'notes']);
        $this->showCrewModal = true;
    }

    public function saveCrew()
    {
        $this->validate();

        if ($this->editingCrew) {
            $this->editingCrew->update($this->form);
            $message = 'Crew member updated successfully.';
        } else {
            $crew = Crew::create($this->form);
            $message = 'Crew member created successfully.';
        }

        $this->dispatch('alert', icon: 'success', message: $message);
        $this->dispatch('crew-modal-saved');
        $this->reset(['form', 'editingCrew', 'showCrewModal']);
    }

    public function assignCrew(Crew $crew)
    {
        $this->flight->crew()->attach($crew->id);
        $this->dispatch('alert', icon: 'success', message: 'Crew member assigned successfully.');
        $this->showAvailableModal = false;
    }

    public function removeCrew(Crew $crew)
    {
        $this->flight->crew()->detach($crew->id);
        $this->dispatch('alert', icon: 'success', message: 'Crew member removed successfully.');
    }

    public function getAvailableCrewProperty()
    {
        return Crew::query()
            ->whereDoesntHave('flights', function ($query) {
                $query->where('flight_id', $this->flight->id);
            })
            ->when($this->position, function ($query) {
                $query->where('position', $this->position);
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->whereAny(['name', 'employee_id'], 'like', '%'.$this->search.'%');
                });
            })
            ->get()->sortBy('position');
    }

    public function render()
    {
        return view('livewire.flights.crew.manager', [
            'assignedCrew' => $this->flight->crew()
                ->when($this->position, function ($query) {
                    $query->where('position', $this->position);
                })
                ->when($this->search, function ($query) {
                    $query->where(function ($q) {
                        $q->whereAny(['name', 'employee_id'], 'like', '%'.$this->search.'%');
                    });
                })
                ->get(),
            'availableCrew' => $this->availableCrew,
        ]);
    }
}
