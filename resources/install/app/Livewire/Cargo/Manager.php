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
        if (empty($this->selected) || ! $this->bulkContainer) {
            return;
        }

        $container = Container::find($this->bulkContainer);
        foreach ($this->selected as $cargoId) {
            $cargo = Cargo::find($cargoId);
            $cargo->update([
                'container_id' => $this->bulkContainer,
                'status' => 'loaded',
            ]);
        }

        $container->updateWeight();

        $this->dispatch(
            'alert',
            icon: 'success',
            message: count($this->selected).' cargo items loaded to container.'
        );

        $this->selected = [];
        $this->selectAll = false;
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

    public function render()
    {
        $query = $this->getCargoQuery();

        return view('livewire.flights.cargo.manager', [
            'cargo' => $query->latest()->paginate(20),
            'containers' => $this->flight->containers()->where('type', 'cargo')->latest()->paginate(20),
        ])->layout('components.layouts.app');
    }

    public function updateContainer($cargoId, $containerId)
    {
        $cargo = Cargo::findOrFail($cargoId);
        $cargo->update([
            'container_id' => $containerId ? $containerId : null,
            'status' => $containerId ? 'loaded' : 'offloaded',
        ]);

        $this->dispatch(
            'alert',
            icon: 'success',
            message: $containerId ? 'Cargo loaded to container.' : 'Cargo removed from container.'
        );
    }

    public function delete(Cargo $cargo)
    {
        $cargo->delete();
        $this->dispatch(
            'alert',
            icon: 'success',
            message: 'Cargo removed successfully.'
        );
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
        $this->dispatch(
            'alert',
            icon: 'success',
            message: 'Cargo saved successfully.'
        );
        $this->dispatch('cargo-saved');
    }
}
