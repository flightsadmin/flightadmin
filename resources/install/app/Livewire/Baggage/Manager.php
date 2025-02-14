<?php

namespace App\Livewire\Baggage;

use App\Models\Baggage;
use App\Models\Container;
use App\Models\Flight;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Manager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $flight = null;

    public $showForm = false;

    public $editingBaggage = null;

    public $search = '';

    public $status = '';

    public $container_id = '';

    public $selected = [];

    public $selectAll = false;

    public $bulkContainer = null;

    public $form = [
        'passenger_id' => '',
        'tag_number' => '',
        'weight' => '',
        'container_id' => null,
        'status' => 'pending',
        'notes' => '',
    ];

    protected $rules = [
        'form.passenger_id' => 'required|exists:passengers,id',
        'form.tag_number' => 'required|string|max:255',
        'form.weight' => 'required|numeric|min:0',
        'form.container_id' => 'nullable|exists:containers,id',
        'form.status' => 'required|in:pending,loaded,offloaded',
        'form.notes' => 'nullable|string',
    ];

    public function mount(?Flight $flight = null)
    {
        $this->flight = $flight;
    }

    public function edit(Baggage $baggage)
    {
        $this->editingBaggage = $baggage;
        $this->form = $baggage->only([
            'passenger_id',
            'tag_number',
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
            'flight_id' => $this->flight?->id,
        ]);

        if ($this->editingBaggage) {
            $this->editingBaggage->update($data);
        } else {
            Baggage::create($data);
        }

        $this->reset('form', 'editingBaggage', 'showForm');
        $this->dispatch(
            'alert',
            icon: 'success',
            message: 'Baggage saved successfully.'
        );
        $this->dispatch('baggage-saved');
    }

    public function delete(Baggage $baggage)
    {
        $baggage->delete();
        $this->dispatch(
            'alert',
            icon: 'success',
            message: 'Baggage removed successfully.'
        );
    }

    public function updateContainer($baggageId, $containerId)
    {
        $baggage = Baggage::findOrFail($baggageId);
        $baggage->update([
            'container_id' => $containerId ? $containerId : null,
            'status' => $containerId ? 'loaded' : 'offloaded',
        ]);

        $this->dispatch(
            'alert',
            icon: 'success',
            message: $containerId ? 'Baggage loaded to container.' : 'Baggage removed from container.'
        );
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selected = $this->getBaggageQuery()
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function updatedSelected()
    {
        $this->selectAll = count($this->selected) === $this->getBaggageQuery()->count();
    }

    public function loadSelectedToContainer()
    {
        if (empty($this->selected) || ! $this->bulkContainer) {
            return;
        }

        $container = Container::find($this->bulkContainer);

        foreach ($this->selected as $baggageId) {
            $baggage = Baggage::find($baggageId);
            $baggage->update([
                'container_id' => $this->bulkContainer,
                'status' => 'loaded',
            ]);
        }

        $container->updateWeight();

        $this->dispatch(
            'alert',
            icon: 'success',
            message: count($this->selected).' baggage items loaded to container.'
        );

        $this->selected = [];
        $this->selectAll = false;
        $this->bulkContainer = null;
    }

    protected function getBaggageQuery()
    {
        $query = Baggage::query()->with(['passenger', 'container', 'flight']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('tag_number', 'like', '%'.$this->search.'%')
                    ->orWhereHas('passenger', function ($q) {
                        $q->whereAny(['name', 'ticket_number'], 'like', '%'.$this->search.'%');
                    });
            });
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

    #[On('baggage-saved')]
    public function render()
    {
        $query = $this->getBaggageQuery();
        $query->orderByDesc('created_at');

        return view('livewire.flights.baggage.manager', [
            'baggage' => $query->paginate(10),
            'passengers' => $this->flight ? $this->flight->passengers()->get() : collect(),
            'containers' => $this->flight ? $this->flight->containers()->where('type', 'baggage')->get() : collect(),
        ]);
    }
}
