<?php

namespace App\Livewire\Flight;

use App\Models\Flight;
use Livewire\Component;

class LoadsheetManager extends Component
{
    public Flight $flight;

    public $loadsheet;

    public $loadplan;

    public $showModal = false;

    public function mount(Flight $flight)
    {
        $this->flight = $flight->load([
            'aircraft.type.holds',
            'containers.cargo',
            'containers.baggage',
            'passengers',
            'crew',
            'fuel',
            'loadplans',
        ]);

        $this->loadsheet = $this->flight->loadsheets()->latest()->first();
        $this->loadplan = $this->flight->loadplans()->latest()->first();
    }

    private function calculateTotalDeadload()
    {
        return array_sum(array_column($this->generateLoadData()['hold_breakdown'], 'weight'));
    }

    private function calculateTotalTrafficLoad()
    {
        return $this->calculateTotalDeadload() + array_sum(array_column($this->generateLoadData()['pax_by_type'], 'weight'));
    }

    private function calculateCrewWeight()
    {
        return $this->flight->aircraft->type->getCrewIndexes($this->flight->fuel->crew)['weight'];
    }

    private function calculateDryOperatingWeight()
    {
        return $this->flight->aircraft->basic_weight + $this->calculateCrewWeight();
    }

    private function calculateZeroFuelWeight()
    {
        return $this->calculateDryOperatingWeight() + $this->calculateTotalTrafficLoad();
    }

    private function calculateTakeoffWeight()
    {
        return $this->calculateZeroFuelWeight() + $this->flight->fuel->take_off_fuel;
    }

    private function calculateLandingWeight()
    {
        return $this->calculateTakeoffWeight() - $this->flight->fuel->trip_fuel;
    }

    private function calculateUnderload()
    {
        return min(
            $this->flight->aircraft->type->max_zero_fuel_weight - $this->calculateZeroFuelWeight(),
            $this->flight->aircraft->type->max_takeoff_weight - $this->calculateTakeoffWeight(),
            $this->flight->aircraft->type->max_landing_weight - $this->calculateLandingWeight()
        );
    }

    private function calculatePantryIndex()
    {
        if (!$this->flight->fuel) {
            return;
        }

        return $this->flight->aircraft->type->getPantryDetails($this->flight->fuel->pantry);
    }

    private function calculateFuel()
    {
        return [
            'block' => $this->flight->fuel->block_fuel,
            'taxi' => $this->flight->fuel->taxi_fuel,
            'trip' => $this->flight->fuel->trip_fuel,
            'takeoff' => $this->flight->fuel->take_off_fuel,
            'crew' => $this->flight->fuel->crew,
        ];
    }

    public function finalizeLoadsheet()
    {
        $this->loadsheet->update([
            'final' => true,
            'released_by' => auth()->id(),
            'released_at' => now(),
        ]);

        $this->dispatch('alert', icon: 'success', message: 'Loadsheet finalized successfully.');
    }

    public function revokeLoadsheet()
    {
        $this->loadsheet->update([
            'final' => false,
            'released_by' => null,
            'released_at' => null,
        ]);

        $this->dispatch('alert', icon: 'success', message: 'Loadsheet Revoked successfully.');
    }

    public function generateLoadsheet()
    {
        $distribution = [
            'trim_data' => $this->generateTrimData(),
            'flight' => $this->generateFlightData(),
            'load_data' => $this->generateLoadData(),
            'fuel' => $this->calculateFuel(),
            'weights' => [
                'dry_operating_weight' => $this->calculateDryOperatingWeight(),
                'zero_fuel_weight' => $this->calculateZeroFuelWeight(),
                'takeoff_weight' => $this->calculateTakeoffWeight(),
                'landing_weight' => $this->calculateLandingWeight(),
            ],
            'indices' => $this->calculateIndices(),
        ];

        $this->loadsheet = $this->flight->loadsheets()->create([
            'payload_distribution' => $distribution,
            'edition' => $this->flight->loadsheets()->count() + 1,
            'created_by' => auth()->id(),
        ]);

        $this->dispatch('loadsheet-updated');

        $this->dispatch('alert', icon: 'success', message: 'Loadsheet generated successfully.');
    }

    private function generateFlightData()
    {
        return [
            'flight_number' => $this->flight->flight_number,
            'flight_date' => strtoupper($this->flight->scheduled_departure_time?->format('dMY')),
            'short_flight_date' => $this->flight->scheduled_departure_time?->format('d'),
            'registration' => $this->flight->aircraft->registration_number,
            'destination' => $this->flight->arrival_airport,
            'sector' => $this->flight->departure_airport . '/' . $this->flight->arrival_airport,
            'version' => $this->flight->aircraft->type->code,
            'release_time' => now('Asia/Qatar')->format('H:i'),
            'underload' => $this->calculateUnderload(),
            'total_deadload' => $this->calculateTotalDeadload(),
            'total_traffic_load' => $this->calculateTotalTrafficLoad()
        ];
    }

    private function calculateIndices()
    {
        $aircraft = $this->flight->aircraft;
        $type = $aircraft->type;
        $fuel = $this->flight->fuel;

        $crewIndexes = $type->getCrewIndexes($fuel->crew);
        $fuelIndexes = $type->getFuelIndexes($fuel->take_off_fuel, $fuel->take_off_fuel - $fuel->trip_fuel);

        $paxData = $this->generateLoadData()['pax_by_type'];
        $cargoData = $this->generateLoadData()['hold_breakdown'];

        $indices = [
            'pantry' => $this->calculatePantryIndex(),
            'basic_index' => $aircraft->basic_index,
            'crew_index' => $crewIndexes['index'],
            'pax_index' => array_sum(array_column($paxData, 'index')),
            'cargo_index' => array_sum(array_column($cargoData, 'index')),
            'litof' => $fuelIndexes['takeoff'],
            'lildf' => $fuelIndexes['landing'],
        ];

        $indices['doi'] = $indices['basic_index'] + $indices['pantry']['index'] + $indices['crew_index'];
        $indices['dli'] = $indices['doi'] + $indices['cargo_index'];
        $indices['lizfw'] = $indices['dli'] + $indices['pax_index'];
        $indices['litow'] = $indices['lizfw'] + $fuelIndexes['takeoff'];
        $indices['lildw'] = $indices['litow'] + $fuelIndexes['landing'];
        $indices['maczfw'] = $type->getZfwMac($this->calculateZeroFuelWeight(), $indices['lizfw']);
        $indices['mactow'] = $type->getTowMac($this->calculateTakeoffWeight(), $indices['litow']);
        $indices['macldw'] = $type->getLdwMac($this->calculateLandingWeight(), $indices['lildw']);


        foreach ($indices as &$value) {
            if (is_numeric($value)) {
                $value = number_format($value, 2);
            }
        }
        unset($value);

        return $indices;
    }

    private function generateLoadData()
    {
        $passengerTypes = ['male', 'female', 'child', 'infant'];

        $paxByType = $this->flight->passengers->groupBy('type')
            ->map(fn($group) => $group->count());

        $orderedPaxByType = collect($passengerTypes)->mapWithKeys(function ($type) {
            $zones = $this->flight->aircraft->type->cabinZones()
                ->with([
                    'seats.passenger' => function ($query) use ($type) {
                        $query->where('flight_id', $this->flight->id)
                            ->where('type', $type);
                    }
                ])->get();

            $zoneData = $zones->map(function ($zone) use ($type) {
                $passengerCount = $zone->seats->filter(fn($seat) => $seat->passenger)->count();
                $weight = $passengerCount * $this->flight->airline->getStandardPassengerWeight($type);

                return [
                    'zone' => $zone->name,
                    'count' => $passengerCount,
                    'weight' => $weight,
                    'index' => round($weight * $zone->index, 2),
                ];
            })->filter(fn($zone) => $zone['count'] > 0)->values();

            $totalCount = $zoneData->sum('count');
            $totalWeight = $zoneData->sum('weight');
            $totalIndex = $zoneData->sum('index');

            return [
                $type => [
                    'count' => $totalCount,
                    'weight' => $totalWeight,
                    'index' => round($totalIndex, 2),
                    'zones' => $zoneData,
                ]
            ];
        })->toArray();

        $orderedWeightsUsed = collect($passengerTypes)->mapWithKeys(fn($type) => [
            $type => $this->flight->airline->getStandardPassengerWeight($type),
        ])->toArray();

        return [
            'pax_by_type' => $orderedPaxByType,
            'pax_by_type_count' => $paxByType,
            'passenger_weights_used' => $orderedWeightsUsed,
            'hold_breakdown' => $this->flight->aircraft->type->holds()
                ->with('positions')
                ->get()
                ->map(function ($hold) {
                    $containers = $this->flight->containers()
                        ->whereIn('position_id', $hold->positions->pluck('id'))->get();

                    $weight = $containers->sum('weight');

                    return [
                        'hold_no' => $hold->code,
                        'weight' => $weight,
                        'index' => round($weight * $hold->index, 2),
                    ];
                })->filter(fn($hold) => $hold['weight'] > 0)->values()->toArray(),
            'deadload_by_type' => [
                'C' => [
                    'pieces' => $this->flight->cargo->where('status', 'loaded')->whereNotNull('container_id')->sum('pieces'),
                    'weight' => $this->flight->cargo->where('status', 'loaded')->whereNotNull('container_id')->sum('weight'),
                ],
                'B' => [
                    'pieces' => $this->flight->baggage->where('status', 'loaded')->whereNotNull('container_id')->count(),
                    'weight' => $this->flight->baggage->where('status', 'loaded')->whereNotNull('container_id')->sum('weight'),
                ],
                'M' => [
                    'pieces' => 0,
                    'weight' => 0,
                ],
                'O' => [
                    'pieces' => 0,
                    'weight' => 0,
                ],
            ],
        ];
    }

    private function generateTrimData()
    {
        $envelopes = $this->flight->aircraft->type->envelopes()
            ->where('is_active', true)
            ->get()->mapWithKeys(function ($envelope) {
                $points = collect($envelope->points)->map(function ($point) {
                    return [
                        'x' => $point['index'],
                        'y' => $point['weight']
                    ];
                })->values()->toArray();

                return [strtolower($envelope->name) . 'Envelope' => $points];
            })
            ->toArray();

        return $envelopes;
    }

    public function render()
    {
        return view('livewire.flights.loadsheet-manager', [
            'loadsheets' => $this->flight->loadsheets()->latest()->get(),
        ]);
    }
}
