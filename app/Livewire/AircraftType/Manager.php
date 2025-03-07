<?php

namespace App\Livewire\AircraftType;

use App\Models\AircraftType;
use App\Models\Airline;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Manager extends Component
{
    use WithPagination;

    public $selectedAirlineId = '';

    #[Url]
    public $selectedAircraft = null;

    #[Url]
    public $activeTab = 'overview';

    public $search = '';

    public $showForm = false;

    public $editingAircraftType = null;

    public $form = [
        'code' => '',
        'name' => '',
        'manufacturer' => '',
        'max_passengers' => 0,
        'cargo_capacity' => 0,
        'max_fuel_capacity' => 0,
        'empty_weight' => 0,
        'max_zero_fuel_weight' => 0,
        'max_takeoff_weight' => 0,
        'max_landing_weight' => 0,
        'category' => '',
        'max_deck_crew' => 2,
        'max_cabin_crew' => 2,
    ];

    protected $rules = [
        'form.code' => 'required|string|max:10',
        'form.name' => 'required|string|max:255',
        'form.manufacturer' => 'required|string|max:255',
        'form.max_passengers' => 'required|integer|min:0',
        'form.cargo_capacity' => 'required|integer|min:0',
        'form.max_fuel_capacity' => 'required|integer|min:0',
        'form.empty_weight' => 'required|numeric|min:0',
        'form.max_zero_fuel_weight' => 'required|numeric|min:0',
        'form.max_takeoff_weight' => 'required|numeric|min:0',
        'form.max_landing_weight' => 'required|numeric|min:0',
        'form.category' => 'required|string|in:Narrow-body,Wide-body,Regional',
        'form.max_deck_crew' => 'required|integer|min:1',
        'form.max_cabin_crew' => 'required|integer|min:1',
    ];

    #[Computed]
    public function selectedType()
    {
        return $this->selectedAircraft ? AircraftType::find($this->selectedAircraft) : null;
    }

    public function mount()
    {
        $this->selectedAirlineId = session('selected_airline_id');
    }

    public function selectType($id)
    {
        $this->selectedAircraft = $id;
    }

    public function render()
    {
        return view('livewire.aircraft_type.manager', [
            'airlines' => Airline::orderBy('name')->get(),
            'aircraftTypes' => AircraftType::query()
                ->when($this->selectedAirlineId, fn ($q) => $q->where('airline_id', $this->selectedAirlineId))
                ->when($this->search, fn ($q) => $q->whereAny(['name', 'code'], 'like', "%{$this->search}%"))
                ->orderBy('code')
                ->paginate(10),
        ]);
    }

    public function updatedSelectedAirlineId($value)
    {
        if ($value) {
            session(['selected_airline_id' => $value]);
        } else {
            session()->forget('selected_airline_id');
        }
        $this->resetPage();

        return $this->redirectRoute('aircraft_types.index', navigate: true);
    }

    public function edit(AircraftType $aircraftType)
    {
        $this->editingAircraftType = $aircraftType;
        $this->form = $aircraftType->toArray();
    }

    public function save()
    {
        $this->validate();

        $aircraftType = $this->editingAircraftType
            ? tap($this->editingAircraftType)->update($this->form)
            : AircraftType::create($this->form);

        if ($this->selectedAirlineId && ! $this->editingAircraftType) {
            $airline = Airline::find($this->selectedAirlineId);
            $airline->aircraftTypes()->syncWithoutDetaching([$aircraftType->id]);
        }

        $this->reset('form', 'editingAircraftType');
        $this->dispatch('aircraft-type-saved');
    }

    public function delete(AircraftType $aircraftType)
    {
        $aircraftType->delete();
    }
}
