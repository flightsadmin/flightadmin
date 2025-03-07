<?php

namespace App\Livewire\Airline;

use App\Models\Airline;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';

    public $showModal = false;

    public $editingAirline = null;

    // Form fields
    public $form = [
        'name' => '',
        'iata_code' => '',
        'icao_code' => '',
        'country' => '',
        'phone' => '',
        'email' => '',
        'address' => '',
        'description' => '',
    ];

    protected $rules = [
        'form.name' => 'required|string|max:255',
        'form.iata_code' => 'required|string|size:2',
        'form.icao_code' => 'required|string|size:3',
        'form.country' => 'required|string|max:255',
        'form.phone' => 'nullable|string|max:255',
        'form.email' => 'nullable|email|max:255',
        'form.address' => 'nullable|string|max:255',
        'form.description' => 'nullable|string',
    ];

    public function edit(Airline $airline)
    {
        $this->editingAirline = $airline;
        $this->form = $airline->only(['name', 'iata_code', 'icao_code', 'country', 'phone', 'email', 'address', 'description']);
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->editingAirline) {
            $this->editingAirline->update($this->form);
            $message = 'Airline updated successfully.';
        } else {
            Airline::create($this->form);
            $message = 'Airline created successfully.';
        }

        $this->dispatch('alert', icon: 'success', message: $message);
        $this->dispatch('airline-saved');
        $this->reset(['form', 'editingAirline', 'showModal']);
    }

    public function toggleStatus(Airline $airline)
    {
        $airline->update(['active' => ! $airline->active]);
        $this->dispatch('alert', icon: 'success', message: 'Airline status updated successfully.');
    }

    public function remove(Airline $airline)
    {
        $airline->delete();
        $this->dispatch('alert', icon: 'success', message: 'Airline deleted successfully.');
    }

    public function render()
    {
        return view('livewire.airline.index', [
            'airlines' => Airline::query()
                ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('iata_code', 'like', "%{$this->search}%")
                    ->orWhere('icao_code', 'like', "%{$this->search}%"))
                ->orderBy('name')
                ->paginate(10),
        ]);
    }
}
