<?php

namespace App\Livewire\Airline;

use App\Models\Airline;
use App\Models\Container;
use Livewire\Attributes\On;
use Livewire\Component;

class UldManager extends Component
{
    public Airline $airline;

    public $editingUldKey = null;

    public $showUldUnitsModal = false;

    public $selectedUldType = null;

    public $editingUldUnitKey = null;

    public $uldForm = [
        'code' => '',
        'name' => '',
        'tare_weight' => 0,
        'max_gross_weight' => 0,
        'positions_required' => 1,
        'color' => '#0dcaf0',
        'icon' => 'box-seam',
        'allowed_holds' => ['FWD', 'AFT'],
        'restrictions' => [
            'requires_adjacent_positions' => false,
            'requires_vertical_positions' => false,
        ],
    ];

    public $uldUnitForm = [
        'container_number' => '',
        'serviceable' => true,
    ];

    protected $defaultUldTypes = [
        'pmc' => [
            'code' => 'PMC',
            'name' => 'Pallet with Net',
            'tare_weight' => 110,
            'max_gross_weight' => 3400,
            'positions_required' => 2,
            'color' => '#fd7e14',
            'icon' => 'box-seam',
            'allowed_holds' => ['FWD', 'AFT'],
            'restrictions' => [
                'requires_adjacent_positions' => true,
                'requires_vertical_positions' => true,
            ],
        ],
        'ake' => [
            'code' => 'AKE',
            'name' => 'LD3 Container',
            'tare_weight' => 85,
            'max_gross_weight' => 1588,
            'positions_required' => 1,
            'color' => '#0dcaf0',
            'icon' => 'box-seam',
            'allowed_holds' => ['FWD', 'AFT', 'BULK'],
            'restrictions' => [
                'requires_adjacent_positions' => false,
                'requires_vertical_positions' => false,
            ],
        ],
    ];

    public function mount(Airline $airline)
    {
        $this->airline = $airline;
    }

    public function getUldTypes()
    {
        $uldSettings = $this->airline->settings()->where('key', 'uld_types')->first();

        if (!$uldSettings) {
            return $this->initializeDefaultUldTypes();
        }

        return json_decode($uldSettings->value, true);
    }

    protected function initializeDefaultUldTypes()
    {
        $defaultUlds = collect($this->defaultUldTypes)->toArray();

        $this->airline->settings()->create([
            'key' => 'uld_types',
            'value' => json_encode($defaultUlds),
        ]);

        return $defaultUlds;
    }

    public function createUldType()
    {
        $this->resetUldForm();
        $this->dispatch('showUldModal');
    }

    public function editUldType($key)
    {
        $this->editingUldKey = $key;
        $uldTypes = $this->getUldTypes();

        if (!isset($uldTypes[$key])) {
            $this->dispatch('alert', icon: 'error', message: 'ULD type not found.');

            return;
        }

        $this->uldForm = $uldTypes[$key];
    }

    public function saveUldType()
    {
        $this->validate([
            'uldForm.code' => 'required|string|max:3',
            'uldForm.name' => 'required|string|max:255',
            'uldForm.tare_weight' => 'required|numeric|min:0',
            'uldForm.max_gross_weight' => 'required|numeric|min:0',
            'uldForm.positions_required' => 'required|integer|min:1|max:2',
            'uldForm.color' => 'required|string',
            'uldForm.icon' => 'required|string',
            'uldForm.allowed_holds' => 'required|array|min:1',
            'uldForm.allowed_holds.*' => 'required|in:FWD,AFT,BULK',
            'uldForm.restrictions.requires_adjacent_positions' => 'required|boolean',
            'uldForm.restrictions.requires_vertical_positions' => 'required|boolean',
        ]);

        $uldTypes = $this->getUldTypes();
        $key = $this->editingUldKey ?? strtolower($this->uldForm['code']);

        if (!$this->editingUldKey && isset($uldTypes[$key])) {
            $this->addError('uldForm.code', 'This ULD code already exists.');

            return;
        }

        $uldTypes[$key] = [
            'code' => $this->uldForm['code'],
            'name' => $this->uldForm['name'],
            'tare_weight' => (float) $this->uldForm['tare_weight'],
            'max_gross_weight' => (float) $this->uldForm['max_gross_weight'],
            'positions_required' => (int) $this->uldForm['positions_required'],
            'color' => $this->uldForm['color'],
            'icon' => $this->uldForm['icon'],
            'allowed_holds' => $this->uldForm['allowed_holds'],
            'restrictions' => [
                'requires_adjacent_positions' => (bool) $this->uldForm['restrictions']['requires_adjacent_positions'],
                'requires_vertical_positions' => (bool) $this->uldForm['restrictions']['requires_vertical_positions'],
            ],
        ];

        $this->airline->settings()->updateOrCreate(
            ['key' => 'uld_types'],
            ['value' => json_encode($uldTypes)]
        );

        $this->dispatch('uld-saved');
        $this->resetUldForm();
        $this->dispatch('alert', icon: 'success', message: 'ULD type ' . ($this->editingUldKey ? 'updated' : 'created') . ' successfully.');
    }

    public function deleteUldType($key)
    {
        $uldTypes = $this->getUldTypes();

        if (!isset($uldTypes[$key])) {
            $this->dispatch('alert', icon: 'error', message: 'ULD type not found.');

            return;
        }

        // Check if there are any containers of this type
        $containersCount = Container::where('airline_id', $this->airline->id)
            ->where('uld_type', $key)
            ->count();

        if ($containersCount > 0) {
            $this->dispatch('alert', icon: 'error', message: "Cannot delete ULD type. {$containersCount} containers of this type exist.");
            return;
        }

        unset($uldTypes[$key]);
        $this->airline->settings()->updateOrCreate(
            ['key' => 'uld_types'],
            ['value' => json_encode($uldTypes)]
        );

        $this->dispatch('alert', icon: 'success', message: 'ULD type deleted successfully.');
    }

    public function resetUldForm()
    {
        $this->editingUldKey = null;
        $this->uldForm = [
            'code' => '',
            'name' => '',
            'tare_weight' => 0,
            'max_gross_weight' => 0,
            'positions_required' => 1,
            'color' => '#0dcaf0',
            'icon' => 'box-seam',
            'allowed_holds' => ['FWD', 'AFT'],
            'restrictions' => [
                'requires_adjacent_positions' => false,
                'requires_vertical_positions' => false,
            ],
        ];
    }

    public function showUldUnits($typeKey)
    {
        $this->selectedUldType = $typeKey;
        $this->resetUldUnitForm();
        $this->showUldUnitsModal = true;
    }

    public function createUldUnit()
    {
        $this->validate([
            'uldUnitForm.container_number' => 'required|string|max:20',
            'uldUnitForm.serviceable' => 'required|boolean',
        ]);

        $uldTypes = $this->getUldTypes();

        if (!isset($uldTypes[$this->selectedUldType])) {
            $this->dispatch('alert', icon: 'error', message: 'ULD type not found.');
            return;
        }

        // Get tare weight and max weight from ULD type
        $tareWeight = $uldTypes[$this->selectedUldType]['tare_weight'] ?? 60;
        $maxWeight = $uldTypes[$this->selectedUldType]['max_gross_weight'] ?? 2000;

        // Generate a container number with ULD type code if not provided
        if (!$this->editingUldUnitKey) {
            $uldCode = $uldTypes[$this->selectedUldType]['code'];
            $baseNumber = $this->uldUnitForm['container_number'];

            // If the container number doesn't already include the ULD code, prepend it
            if (!str_starts_with($baseNumber, $uldCode)) {
                $this->uldUnitForm['container_number'] = $uldCode . $baseNumber;
            }
        }

        // Check if container number already exists
        $existingContainer = Container::where('airline_id', $this->airline->id)
            ->where('container_number', $this->uldUnitForm['container_number'])
            ->first();

        if ($existingContainer && !$this->editingUldUnitKey) {
            $this->addError('uldUnitForm.container_number', 'This container number already exists.');
            return;
        }

        try {
            if ($this->editingUldUnitKey) {
                // Update existing container
                Container::where('airline_id', $this->airline->id)
                    ->where('container_number', $this->editingUldUnitKey)
                    ->update([
                        'container_number' => $this->uldUnitForm['container_number'],
                        'tare_weight' => $tareWeight,
                        'max_weight' => $maxWeight,
                        'serviceable' => (bool) $this->uldUnitForm['serviceable'],
                        'uld_type' => $this->selectedUldType,
                    ]);
            } else {
                // Create new container
                Container::create([
                    'airline_id' => $this->airline->id,
                    'container_number' => $this->uldUnitForm['container_number'],
                    'tare_weight' => $tareWeight,
                    'max_weight' => $maxWeight,
                    'serviceable' => (bool) $this->uldUnitForm['serviceable'],
                    'uld_type' => $this->selectedUldType,
                ]);
            }

            $this->resetUldUnitForm();
            $this->dispatch('alert', icon: 'success', message: 'ULD unit ' . ($this->editingUldUnitKey ? 'updated' : 'created') . ' successfully.');
        } catch (\Exception $e) {
            $this->dispatch('alert', icon: 'error', message: 'Error saving ULD unit: ' . $e->getMessage());
        }
    }

    public function editUldUnit($containerNumber)
    {
        $container = Container::where('airline_id', $this->airline->id)
            ->where('container_number', $containerNumber)
            ->first();

        if (!$container) {
            $this->dispatch('alert', icon: 'error', message: 'ULD unit not found.');
            return;
        }

        $this->editingUldUnitKey = $containerNumber;
        $this->uldUnitForm = [
            'container_number' => $container->container_number,
            'serviceable' => $container->serviceable,
        ];
    }

    public function deleteUldUnit($containerNumber)
    {
        try {
            // Check if container is assigned to any flights
            $container = Container::where('airline_id', $this->airline->id)
                ->where('container_number', $containerNumber)
                ->first();

            if (!$container) {
                $this->dispatch('alert', icon: 'error', message: 'ULD unit not found.');
                return;
            }

            if ($container->flights()->count() > 0) {
                $this->dispatch('alert', icon: 'error', message: 'Cannot delete container that is assigned to flights.');
                return;
            }

            // Check if container has baggage or cargo
            if ($container->baggage()->count() > 0 || $container->cargo()->count() > 0) {
                $this->dispatch('alert', icon: 'error', message: 'Cannot delete container that contains baggage or cargo.');
                return;
            }

            $container->delete();
            $this->dispatch('alert', icon: 'success', message: 'ULD unit deleted successfully.');
        } catch (\Exception $e) {
            $this->dispatch('alert', icon: 'error', message: 'Error deleting ULD unit: ' . $e->getMessage());
        }
    }

    public function toggleServiceability($containerNumber)
    {
        $container = Container::where('airline_id', $this->airline->id)
            ->where('container_number', $containerNumber)
            ->first();

        if (!$container) {
            $this->dispatch('alert', icon: 'error', message: 'ULD unit not found.');
            return;
        }

        $container->update([
            'serviceable' => !$container->serviceable
        ]);

        $this->dispatch('alert', icon: 'success', message: 'Container marked as ' . ($container->serviceable ? 'serviceable' : 'unserviceable') . '.');
    }

    public function resetUldUnitForm()
    {
        $this->editingUldUnitKey = null;
        $this->uldUnitForm = [
            'container_number' => '',
            'serviceable' => true,
        ];
    }

    #[On('modalClosed')]
    public function handleModalClosed()
    {
        if ($this->showUldUnitsModal) {
            $this->showUldUnitsModal = false;
            $this->selectedUldType = null;
            $this->resetUldUnitForm();
        } else {
            $this->resetUldForm();
        }
    }

    public function getContainersForSelectedType()
    {
        if (!$this->selectedUldType) {
            return collect();
        }

        return $this->airline->containers->where('uld_type', $this->selectedUldType);
    }

    public function getContainerStats()
    {
        $containers = $this->airline->containers;
        $uldTypes = $this->getUldTypes();
        $stats = [];

        foreach ($uldTypes as $key => $type) {
            $total = $containers->where('uld_type', $key)->count();

            $serviceable = $containers->where('uld_type', $key)->where('serviceable', true)->count();

            $stats[$key] = [
                'total' => $total,
                'serviceable' => $serviceable,
                'unserviceable' => $total - $serviceable,
                'available' => $serviceable,
            ];
        }

        return $stats;
    }

    public function render()
    {
        $uldTypes = $this->getUldTypes();
        $containers = $this->getContainersForSelectedType();
        $containerStats = $this->getContainerStats();

        return view('livewire.airline.uld-manager', [
            'uldTypes' => $uldTypes,
            'containers' => $containers,
            'containerStats' => $containerStats,
        ]);
    }
}
