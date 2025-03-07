<?php

namespace App\Livewire\Cargo;

use App\Models\Cargo;
use App\Models\Container;
use App\Models\Flight;
use Livewire\Component;
use Livewire\WithPagination;

class Manager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $flight;

    public $search;

    public $type;

    public $status;

    public $container_id;

    public $selected = [];

    public $selectAll = false;

    public $bulkContainer = null;

    public $showForm = false;

    public $editingCargo = null;

    public $form = [
        'awb_number' => '',
        'type' => '',
        'weight' => '',
        'pieces' => '',
        'container_id' => null,
        'status' => 'accepted',
        'notes' => '',
    ];

    protected $rules = [
        'form.awb_number' => 'required|string|max:255',
        'form.type' => 'required|in:general,perishable,dangerous_goods,live_animals,valuable,mail',
        'form.weight' => 'required|numeric|min:0',
        'form.pieces' => 'required|numeric|min:0',
        'form.container_id' => 'nullable|exists:containers,id',
        'form.status' => 'required|in:accepted,loaded,offloaded',
        'form.notes' => 'nullable|string',
    ];

    public function mount(Flight $flight)
    {
        $this->flight = $flight;
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selected = $this->getCargoQuery()
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function updatedSelected()
    {
        $this->selectAll = count($this->selected) === $this->getCargoQuery()->count();
    }

    public function loadSelectedToContainer()
    {
        if (empty($this->selected)) {
            return;
        }

        $newContainer = $this->bulkContainer ? Container::find($this->bulkContainer) : null;
        $cargo = Cargo::whereIn('id', $this->selected)->get();

        foreach ($cargo as $item) {
            $oldContainer = $item->container;

            // Skip if same container
            if ($oldContainer && $oldContainer->id == $this->bulkContainer) {
                continue;
            }

            // Update container_id
            $item->update([
                'container_id' => $this->bulkContainer,
                'status' => $this->bulkContainer ? 'loaded' : 'checked',
            ]);

            // Case 1: Moving from one container to another
            if ($oldContainer && $newContainer) {
                $oldContainer->flights()->where('flight_id', $this->flight->id)->decrement('weight', $item->weight);
                $oldContainer->flights()->where('flight_id', $this->flight->id)->decrement('pieces', $item->pieces);

                $newContainer->flights()->where('flight_id', $this->flight->id)->increment('weight', $item->weight);
                $newContainer->flights()->where('flight_id', $this->flight->id)->increment('pieces', $item->pieces);
            }
            // Case 2: New loading (no previous container)
            elseif (! $oldContainer && $newContainer) {
                $newContainer->flights()->where('flight_id', $this->flight->id)->increment('weight', $item->weight);
                $newContainer->flights()->where('flight_id', $this->flight->id)->increment('pieces', $item->pieces);
            }
            // Case 3: Unloading from container (no new container)
            elseif ($oldContainer && ! $newContainer) {
                $oldContainer->flights()->where('flight_id', $this->flight->id)->decrement('weight', $item->weight);
                $oldContainer->flights()->where('flight_id', $this->flight->id)->decrement('pieces', $item->pieces);
            }
        }

        $this->dispatch(
            'alert',
            icon: 'success',
            message: count($this->selected).' cargo items '.($this->bulkContainer ? 'loaded to container' : 'unloaded from container').'.'
        );

        $this->selected = [];
        $this->bulkContainer = null;
    }

    protected function getCargoQuery()
    {
        $query = Cargo::query();
        if ($this->search) {
            $query->whereAny(['awb_number'], 'like', '%'.$this->search.'%');
        }
        if ($this->type) {
            $query->where('type', $this->type);
        }
        if ($this->status) {
            $query->where('status', $this->status);
        }
        if ($this->container_id) {
            $query->where('container_id', $this->container_id);
        }
        if ($this->flight) {
            $query->whereHas('flight', function ($q) {
                $q->where('flight_id', $this->flight->id);
            });
        }

        return $query;
    }

    public function updateContainer(Cargo $cargo, $containerId)
    {
        $oldContainer = $cargo->container;
        $weight = $cargo->weight;
        $pieces = $cargo->pieces;

        // Update baggage container
        $cargo->update([
            'container_id' => $containerId ?: null,
            'status' => $containerId ? 'loaded' : 'checked',
        ]);

        // Case 1: Moving from one container to another
        if ($oldContainer && $containerId) {
            // Decrement old container
            $oldContainer->flights()->where('flight_id', $this->flight->id)->decrement('weight', $weight);
            $oldContainer->flights()->where('flight_id', $this->flight->id)->decrement('pieces', $pieces);

            // Increment new container
            Container::find($containerId)->flights()->where('flight_id', $this->flight->id)->increment('weight', $weight);
            Container::find($containerId)->flights()->where('flight_id', $this->flight->id)->increment('pieces', $pieces);
        }
        // Case 2: Loading into a container (no previous container)
        elseif (! $oldContainer && $containerId) {
            Container::find($containerId)->flights()->where('flight_id', $this->flight->id)->increment('weight', $weight);
            Container::find($containerId)->flights()->where('flight_id', $this->flight->id)->increment('pieces', $pieces);
        }
        // Case 3: Offloading from a container (no new container)
        elseif ($oldContainer && ! $containerId) {
            $oldContainer->flights()->where('flight_id', $this->flight->id)->decrement('weight', $weight);
            $oldContainer->flights()->where('flight_id', $this->flight->id)->decrement('pieces', $pieces);
        }

        $this->dispatch('alert', icon: 'success', message: 'Cargo container updated successfully.');
    }

    public function delete(Cargo $cargo)
    {
        $cargo->delete();
        $this->dispatch('alert', icon: 'success', message: 'Cargo removed successfully.');
    }

    public function edit(Cargo $cargo)
    {
        $this->editingCargo = $cargo;
        $this->form = $cargo->only([
            'awb_number',
            'type',
            'pieces',
            'weight',
            'container_id',
            'status',
            'notes',
        ]);
        $this->showForm = true;
    }

    public function save()
    {
        $this->validate();

        $data = array_merge($this->form, [
            'flight_id' => $this->flight->id,
        ]);

        if ($this->editingCargo) {
            $this->editingCargo->update($data);
        } else {
            Cargo::create($data);
        }

        $this->reset('form', 'editingCargo', 'showForm');
        $this->dispatch('alert', icon: 'success', message: 'Cargo saved successfully.');
        $this->dispatch('cargo-saved');
    }

    public function render()
    {
        $query = $this->getCargoQuery();

        return view('livewire.flights.cargo.manager', [
            'cargo' => $query->latest()->paginate(20),
            'containers' => $this->flight->containers()->where('type', 'cargo')->latest()->paginate(20),
        ])->layout('components.layouts.app');
    }
}
