<?php

namespace App\Livewire\AircraftType;

use App\Models\AircraftType;
use Livewire\Component;

class CrewSettings extends Component
{
    public AircraftType $aircraftType;

    public $settings;

    public $showSeatingModal = false;

    public $showDistributionModal = false;

    public $isEditing = false;

    public $selectedPosition = null;

    public $distribution = [];

    public $distributions = [];

    public $seatingForm = [
        'location' => '',
        'index_per_kg' => 0,
        'arm_length' => 0,
        'max_crew' => 1,
        'is_deck_crew' => false,
    ];

    public $distributionForm = [
        'distribution' => [],
    ];

    public function mount(AircraftType $aircraftType)
    {
        $this->aircraftType = $aircraftType;
        $this->settings = $aircraftType->getCrewSettings();
        $this->loadDistributions();
    }

    public function createSeating()
    {
        $this->isEditing = false;
        $this->selectedPosition = null;
        $this->seatingForm = [
            'location' => '',
            'index_per_kg' => 0,
            'arm_length' => 0,
            'max_crew' => 1,
            'is_deck_crew' => false,
        ];
        $this->showSeatingModal = true;
    }

    public function editSeating($position)
    {
        $this->isEditing = true;
        $this->selectedPosition = $position;
        $this->seatingForm = [
            'location' => $this->settings['seating'][$position]['location'],
            'index_per_kg' => $this->settings['seating'][$position]['index_per_kg'],
            'arm_length' => $this->settings['seating'][$position]['arm_length'],
            'max_crew' => $this->settings['seating'][$position]['max_crew'],
            'is_deck_crew' => $this->settings['seating'][$position]['is_deck_crew'] ?? false,
        ];
        $this->showSeatingModal = true;
    }

    public function updatedDistributionFormCrewCount($value)
    {
        $this->distributionForm['distribution'] =
            $this->settings['distributions'][$value] ??
            array_fill(0, $this->getCabinCrewPositionsCount(), 0);
    }

    public function loadDistributions()
    {
        $this->distributions = collect(range(1, max(5, max(array_keys($this->settings['distributions'] ?? [])))))
            ->mapWithKeys(fn ($count) => [
                $count => $this->settings['distributions'][$count] ??
                    array_fill(0, $this->getCabinCrewPositionsCount(), 0),
            ])
            ->toArray();
    }

    public function showDistributionModal()
    {
        $this->distributions = collect(range(1, max(5, max(array_keys($this->settings['distributions'] ?? [])))))
            ->mapWithKeys(fn ($count) => [
                $count => $this->settings['distributions'][$count] ??
                    array_fill(0, $this->getCabinCrewPositionsCount(), 0),
            ])
            ->toArray();

        $this->showDistributionModal = true;
    }

    public function addDistributionRow()
    {
        $nextCount = max(array_keys($this->distributions)) + 1;
        if ($nextCount <= 8) {
            $this->distributions[$nextCount] = array_fill(0, $this->getCabinCrewPositionsCount(), 0);
        }
    }

    public function removeDistributionRow($count)
    {
        unset($this->distributions[$count]);
        $this->distributions = array_combine(
            range(1, count($this->distributions)),
            array_values($this->distributions)
        );
    }

    public function saveDistributions()
    {
        $this->validate([
            'distributions.*.*' => 'required|integer|min:0',
        ]);

        $settings = $this->settings;
        $settings['distributions'] = $this->distributions;

        $this->updateSettings($settings);
        $this->dispatch('alert', icon: 'success', message: 'Crew distributions updated successfully');
        $this->showDistributionModal = false;
        $this->dispatch('distributions-saved');
    }

    public function saveSeating()
    {
        $this->validate([
            'seatingForm.location' => 'required|string|min:3',
            'seatingForm.index_per_kg' => 'required|numeric',
            'seatingForm.arm_length' => 'required|numeric',
            'seatingForm.max_crew' => 'required|integer|min:1',
            'seatingForm.is_deck_crew' => 'boolean',
        ]);

        $settings = $this->settings;
        $position = $this->isEditing ?
            $this->selectedPosition :
            strtolower(str_replace([' ', '/', '\\'], '_', $this->seatingForm['location']));

        $settings['seating'][$position] = [
            'location' => $this->seatingForm['location'],
            'index_per_kg' => (float) $this->seatingForm['index_per_kg'],
            'arm_length' => (float) $this->seatingForm['arm_length'],
            'max_crew' => (int) $this->seatingForm['max_crew'],
            'is_deck_crew' => (bool) $this->seatingForm['is_deck_crew'],
        ];

        $this->updateSettings($settings);
        $this->dispatch('seating-saved');
    }

    public function deleteSeating($position)
    {
        $settings = $this->settings;
        unset($settings['seating'][$position]);

        $this->updateSettings($settings);
        $this->dispatch('alert', [
            'icon' => 'success',
            'message' => 'Crew position deleted successfully',
        ]);
    }

    protected function getCabinCrewPositionsCount()
    {
        return collect($this->settings['seating'])
            ->filter(fn ($config) => ! ($config['is_deck_crew'] ?? false))
            ->count();
    }

    protected function getCabinCrewPositions()
    {
        return collect($this->settings['seating'])
            ->filter(fn ($config) => ! ($config['is_deck_crew'] ?? false));
    }

    protected function updateSettings($settings)
    {
        $this->aircraftType->settings()->updateOrCreate(
            [
                'key' => 'crew_settings',
                'airline_id' => $this->aircraftType->airline_id,
            ],
            [
                'value' => json_encode($settings),
                'type' => 'json',
                'description' => 'Aircraft Type Crew Configurations',
            ]
        );

        $this->settings = $settings;
    }

    public function render()
    {
        return view('livewire.aircraft_type.crew-settings', [
            'cabinCrewPositions' => $this->getCabinCrewPositions(),
        ]);
    }
}
