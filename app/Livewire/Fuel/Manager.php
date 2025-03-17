<?php

namespace App\Livewire\Fuel;

use App\Models\Flight;
use Livewire\Component;

class Manager extends Component
{
    public Flight $flight;

    public $showModal = false;

    public $block_fuel = 10000;

    public $take_off_fuel;

    public $taxi_fuel = 200;

    public $trip_fuel = 8000;

    public $crew = '2/4';

    public $pantry = 'A';

    public function mount(Flight $flight)
    {
        $this->flight = $flight;
        $this->loadFuelData();
    }

    public function loadFuelData()
    {
        if ($fuel = $this->flight->fuel) {
            $this->block_fuel = $fuel->block_fuel;
            $this->take_off_fuel = $fuel->take_off_fuel;
            $this->taxi_fuel = $fuel->taxi_fuel;
            $this->trip_fuel = $fuel->trip_fuel;
            $this->crew = $fuel->crew;
            $this->pantry = $fuel->pantry;
        }
    }

    public function save()
    {
        $validated = $this->validate([
            'block_fuel' => 'required|numeric|min:0',
            'taxi_fuel' => 'required|numeric|min:0',
            'trip_fuel' => 'required|numeric|min:0',
            'crew' => 'required|string',
            'pantry' => 'required|string',
        ]);

        $validated['take_off_fuel'] = $validated['block_fuel'] - $validated['taxi_fuel'];

        $this->flight->fuel()->updateOrCreate(
            ['flight_id' => $this->flight->id],
            $validated
        );

        $this->dispatch('alert', icon: 'success', message: 'Fuel data saved successfully.');
        $this->dispatch('close-modal');
    }

    protected function getPantryOptions()
    {
        return $this->flight->aircraft->type->getAllPantries();
    }

    public function calculatePantryWeight()
    {
        if (!$this->pantry) {
            return 0;
        }

        $pantryDetails = $this->flight->aircraft->type->getPantryDetails($this->pantry);

        return $pantryDetails['weight'] ?? 0;
    }

    public function calculatePantryIndex()
    {
        if (!$this->pantry) {
            return 0;
        }

        $pantryDetails = $this->flight->aircraft->type->getPantryDetails($this->pantry);

        return $pantryDetails['index'] ?? 0;
    }

    public function getCrewOptions()
    {
        $maxDeckCrew = $this->flight->aircraft->type->max_deck_crew;
        $maxCabinCrew = $this->flight->aircraft->type->max_cabin_crew;

        $crewOptions = [];
        for ($deck = 2; $deck <= $maxDeckCrew; $deck++) {
            for ($cabin = 1; $cabin <= $maxCabinCrew; $cabin++) {
                $crewOptions[] = $deck . '/' . $cabin;
            }
        }

        return $crewOptions;
    }

    public function render()
    {
        return view('livewire.flights.fuel.manager', [
            'fuel' => $this->flight->fuel,
            'pantryOptions' => $this->getPantryOptions(),
            'crewOptions' => $this->getCrewOptions(),
        ]);
    }
}
