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

    public function updateContainer(Baggage $baggage, $containerId)
    {
        $oldContainer = $baggage->container;
        $weight = $baggage->weight;

        // Update baggage container
        $baggage->update([
            'container_id' => $containerId ?: null,
            'status' => $containerId ? 'loaded' : 'checked',
        ]);

        // Case 1: Moving from one container to another
        if ($oldContainer && $containerId) {
            // Decrement old container
            $oldContainer->flights()->where('flight_id', $this->flight->id)->decrement('weight', $weight);
            $oldContainer->flights()->where('flight_id', $this->flight->id)->decrement('pieces', 1);

            // Increment new container
            Container::find($containerId)->flights()->where('flight_id', $this->flight->id)->increment('weight', $weight);
            Container::find($containerId)->flights()->where('flight_id', $this->flight->id)->increment('pieces', 1);
        }
        // Case 2: Loading into a container (no previous container)
        elseif (! $oldContainer && $containerId) {
            Container::find($containerId)->flights()->where('flight_id', $this->flight->id)->increment('weight', $weight);
            Container::find($containerId)->flights()->where('flight_id', $this->flight->id)->increment('pieces', 1);
        }
        // Case 3: Offloading from a container (no new container)
        elseif ($oldContainer && ! $containerId) {
            $oldContainer->flights()->where('flight_id', $this->flight->id)->decrement('weight', $weight);
            $oldContainer->flights()->where('flight_id', $this->flight->id)->decrement('pieces', 1);
        }

        $this->dispatch('alert', icon: 'success', message: 'Baggage container updated successfully.');
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
        if (empty($this->selected)) {
            return;
        }

        $newContainer = $this->bulkContainer ? Container::find($this->bulkContainer) : null;
        $baggage = Baggage::whereIn('id', $this->selected)->get();

        foreach ($baggage as $item) {
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
                $oldContainer->flights()->where('flight_id', $this->flight->id)->decrement('pieces', 1);

                $newContainer->flights()->where('flight_id', $this->flight->id)->increment('weight', $item->weight);
                $newContainer->flights()->where('flight_id', $this->flight->id)->increment('pieces', 1);
            }
            // Case 2: New loading (no previous container)
            elseif (! $oldContainer && $newContainer) {
                $newContainer->flights()->where('flight_id', $this->flight->id)->increment('weight', $item->weight);
                $newContainer->flights()->where('flight_id', $this->flight->id)->increment('pieces', 1);
            }
            // Case 3: Unloading from container (no new container)
            elseif ($oldContainer && ! $newContainer) {
                $oldContainer->flights()->where('flight_id', $this->flight->id)->decrement('weight', $item->weight);
                $oldContainer->flights()->where('flight_id', $this->flight->id)->decrement('pieces', 1);
            }
        }

        $this->dispatch(
            'alert',
            icon: 'success',
            message: count($this->selected).' baggage items '.($this->bulkContainer ? 'loaded to container' : 'unloaded from container').'.'
        );

        $this->selected = [];
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
