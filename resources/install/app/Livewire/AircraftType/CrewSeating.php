<?php

namespace App\Livewire\AircraftType;

use App\Models\AircraftType;
use App\Models\CrewSeating as CrewSeatingModel;
use Illuminate\Support\Str;
use Livewire\Component;

class CrewSeating extends Component
{
    public AircraftType $aircraftType;

    public $isEditable = false;

    public $deck_crew = [];

    public $cabin_crew = [];

    public $crewSeats = [];

    protected $rules = [
        'deck_crew.*.location' => 'required|string',
        'deck_crew.*.max_number' => 'required|numeric',
        'deck_crew.*.arm' => 'required|numeric',
        'deck_crew.*.index_per_kg' => 'required|numeric',
        'cabin_crew.*.location' => 'required|string',
        'cabin_crew.*.max_number' => 'required|numeric',
        'cabin_crew.*.arm' => 'required|numeric',
        'cabin_crew.*.index_per_kg' => 'required|numeric',
        'crewSeats.*.number' => 'required|integer|distinct|min:1',
    ];

    public function mount()
    {
        $this->loadCrewSeating();
        $this->loadCrewDistribution();
    }

    protected function loadCrewSeating()
    {
        $this->deck_crew = $this->aircraftType->crewSeating()
            ->where('position', 'deck_crew')
            ->get()
            ->map(function ($crew) {
                return [
                    'id' => $crew->id,
                    'location' => $crew->location,
                    'max_number' => $crew->max_number,
                    'arm' => $crew->arm,
                    'index_per_kg' => $crew->index_per_kg,
                ];
            })
            ->toArray();

        $this->cabin_crew = $this->aircraftType->crewSeating()
            ->where('position', 'cabin_crew')
            ->get()
            ->map(function ($crew) {
                return [
                    'id' => $crew->id,
                    'location' => $crew->location,
                    'max_number' => $crew->max_number,
                    'arm' => $crew->arm,
                    'index_per_kg' => $crew->index_per_kg,
                ];
            })
            ->toArray();

        if (empty($this->deck_crew)) {
            $this->deck_crew = [
                [
                    'location' => '',
                    'max_number' => '',
                    'arm' => '',
                    'index_per_kg' => '',
                ],
            ];
        }

        if (empty($this->cabin_crew)) {
            $this->cabin_crew = [
                [
                    'location' => '',
                    'max_number' => '',
                    'arm' => '',
                    'index_per_kg' => '',
                ],
            ];
        }
    }

    protected function loadCrewDistribution()
    {
        $distributions = $this->aircraftType->crewDistributions()
            ->orderBy('crew_count')
            ->get();

        if ($distributions->isEmpty()) {
            $this->crewSeats = [
                [
                    'number' => 1,
                ],
            ];

            return;
        }

        $this->crewSeats = $distributions->map(function ($dist) {
            return array_merge(
                ['number' => $dist->crew_count],
                $dist->distribution
            );
        })->toArray();
    }

    public function getCrewLocationsProperty()
    {
        return collect($this->cabin_crew)->map(function ($crew) {
            return [
                'location' => $crew['location'],
                'max_number' => $crew['max_number'],
            ];
        })->toArray();
    }

    public function addSeat()
    {
        $newSeat = ['number' => count($this->crewSeats) + 1];

        foreach ($this->crewLocations as $location) {
            $key = Str::snake(strtolower($location['location']));
            $newSeat[$key] = 0;
        }

        $this->crewSeats[] = $newSeat;
    }

    public function removeSeat($index)
    {
        $crewCount = $this->crewSeats[$index]['number'];

        // Delete from database if exists
        $this->aircraftType->crewDistributions()
            ->where('crew_count', $crewCount)
            ->delete();

        unset($this->crewSeats[$index]);
        $this->crewSeats = array_values($this->crewSeats);
    }

    protected function saveCrewDistributions()
    {
        // First, remove all existing distributions
        $this->aircraftType->crewDistributions()->delete();

        // Create new distributions
        foreach ($this->crewSeats as $seat) {
            $crewCount = $seat['number'];
            $distribution = collect($seat)
                ->except('number')
                ->all();

            $this->aircraftType->crewDistributions()->create([
                'crew_count' => $crewCount,
                'distribution' => $distribution,
            ]);
        }
    }

    public function toggleEdit()
    {
        $this->isEditable = !$this->isEditable;
    }

    public function addCrew()
    {
        $this->cabin_crew[] = [
            'location' => '',
            'max_number' => '',
            'arm' => '',
            'index_per_kg' => '',
        ];
    }

    public function removeCrew($index)
    {
        if (isset($this->cabin_crew[$index])) {
            if (isset($this->cabin_crew[$index]['id'])) {
                CrewSeatingModel::destroy($this->cabin_crew[$index]['id']);
            }
            unset($this->cabin_crew[$index]);
            $this->cabin_crew = array_values($this->cabin_crew);
        }
    }

    public function save()
    {
        // $this->validate();

        // Save deck crew
        foreach ($this->deck_crew as $crew) {
            $data = [
                'position' => 'deck_crew',
                'location' => $crew['location'],
                'max_number' => $crew['max_number'],
                'arm' => $crew['arm'],
                'index_per_kg' => $crew['index_per_kg'],
            ];

            if (isset($crew['id'])) {
                CrewSeatingModel::find($crew['id'])->update($data);
            } else {
                $this->aircraftType->crewSeating()->create($data);
            }
        }

        // Save cabin crew
        foreach ($this->cabin_crew as $crew) {
            $data = [
                'position' => 'cabin_crew',
                'location' => $crew['location'],
                'max_number' => $crew['max_number'],
                'arm' => $crew['arm'],
                'index_per_kg' => $crew['index_per_kg'],
            ];

            if (isset($crew['id'])) {
                CrewSeatingModel::find($crew['id'])->update($data);
            } else {
                $this->aircraftType->crewSeating()->create($data);
            }
        }

        // Save crew distributions
        $this->saveCrewDistributions();

        $this->dispatch('refreshAvailable', $this->aircraftType->id);
        $this->dispatch('alert', icon: 'success', message: 'Crew data saved successfully.');
        $this->toggleEdit();
        $this->loadCrewSeating();
        $this->loadCrewDistribution();
    }

    public function render()
    {
        return view('livewire.aircraft_type.crew-seating');
    }
}
