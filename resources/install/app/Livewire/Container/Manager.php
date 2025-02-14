<?php

namespace App\Livewire\Container;

use App\Models\Flight;
use Livewire\Component;
use App\Models\Container;
use Livewire\Attributes\On;
use Livewire\WithPagination;

class Manager extends Component
{
    use WithPagination;

    public $paginationTheme = 'bootstrap';
    public Flight $flight;
    public $showModal = false;
    public $search = '';
    public $type = '';
    public $selected = [];
    public $assignmentType = 'baggage';

    public function mount(Flight $flight)
    {
        $this->flight = $flight;
    }

    public function assignContainers()
    {
        if (empty($this->selected)) {
            return;
        }

        $containers = Container::whereIn('id', $this->selected)->get();

        foreach ($containers as $container) {
            $this->flight->containers()->attach($container->id, [
                'type' => $this->assignmentType,
                'status' => 'unloaded'
            ]);
        }

        $this->dispatch('alert', icon: 'success', message: 'Containers assigned successfully.');
        $this->dispatch('containerSaved');
        $this->reset('selected', 'assignmentType');
    }

    public function unassignContainer($containerId)
    {
        $this->flight->containers()->detach($containerId);
        $this->dispatch('alert', icon: 'success', message: 'Container unassigned successfully.');
        $this->dispatch('containerSaved');
    }

    #[On('container_position_updated')]
    public function render()
    {
        $assignedContainers = $this->flight->containers()
            ->withPivot('type', 'position_id', 'status', 'pieces')
            ->with(['position.hold'])
            ->when($this->search, function ($query) {
                $query->where('container_number', 'like', "%{$this->search}%");
            })
            ->when($this->type, function ($query) {
                $query->where('container_flight.type', $this->type);
            })
            ->get();

        $availableContainers = Container::query()
            ->whereDoesntHave('flights', function ($query) {
                $query->where('flight_id', $this->flight->id);
            })
            ->when($this->search, function ($query) {
                $query->where('container_number', 'like', "%{$this->search}%");
            })
            ->orderBy('container_number')
            ->paginate(10);

        return view('livewire.container.manager', [
            'assignedContainers' => $assignedContainers,
            'availableContainers' => $availableContainers,
        ]);
    }
}
