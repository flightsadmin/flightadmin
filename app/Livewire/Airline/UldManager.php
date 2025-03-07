<?php

namespace App\Livewire\Airline;

use App\Models\Airline;
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
        'number' => '',
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
            'units' => [],
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
            'units' => [],
        ],
    ];

    public function mount(Airline $airline)
    {
        $this->airline = $airline;
    }

    public function getUldTypes()
    {
        $uldSettings = $this->airline->settings()->where('key', 'uld_types')->first();

        if (! $uldSettings) {
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

        if (! isset($uldTypes[$key])) {
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

        if (! $this->editingUldKey && isset($uldTypes[$key])) {
            $this->addError('uldForm.code', 'This ULD code already exists.');

            return;
        }

        // Preserve existing units if editing, or initialize empty array if new
        $existingUnits = $this->editingUldKey ? ($uldTypes[$key]['units'] ?? []) : [];

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
            'units' => $existingUnits,
        ];

        $this->airline->settings()->updateOrCreate(
            ['key' => 'uld_types'],
            ['value' => json_encode($uldTypes)]
        );

        $this->dispatch('uld-saved');
        $this->resetUldForm();
        $this->dispatch('alert', icon: 'success', message: 'ULD type '.($this->editingUldKey ? 'updated' : 'created').' successfully.');
    }

    public function deleteUldType($key)
    {
        $uldTypes = $this->getUldTypes();

        if (! isset($uldTypes[$key])) {
            $this->dispatch('alert', icon: 'error', message: 'ULD type not found.');

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
            'uldUnitForm.number' => 'required|string|max:20',
            'uldUnitForm.serviceable' => 'required|boolean',
        ]);

        $uldTypes = $this->getUldTypes();

        if (! isset($uldTypes[$this->selectedUldType])) {
            $this->dispatch('alert', icon: 'error', message: 'ULD type not found.');

            return;
        }

        // Check if unit number already exists
        $units = $uldTypes[$this->selectedUldType]['units'] ?? [];
        $existingUnit = collect($units)->firstWhere('number', $this->uldUnitForm['number']);

        if ($existingUnit && ! $this->editingUldUnitKey) {
            $this->addError('uldUnitForm.number', 'This unit number already exists.');

            return;
        }

        if ($this->editingUldUnitKey) {
            // Update existing unit
            $units = collect($units)->map(function ($unit) {
                if ($unit['number'] === $this->editingUldUnitKey) {
                    return [
                        'number' => $this->uldUnitForm['number'],
                        'serviceable' => (bool) $this->uldUnitForm['serviceable'],
                    ];
                }

                return $unit;
            })->toArray();
        } else {
            // Add new unit
            $units[] = [
                'number' => $this->uldUnitForm['number'],
                'serviceable' => (bool) $this->uldUnitForm['serviceable'],
            ];
        }

        $uldTypes[$this->selectedUldType]['units'] = $units;

        $this->airline->settings()->updateOrCreate(
            ['key' => 'uld_types'],
            ['value' => json_encode($uldTypes)]
        );

        $this->resetUldUnitForm();
        $this->dispatch('alert', icon: 'success', message: 'ULD unit '.($this->editingUldUnitKey ? 'updated' : 'created').' successfully.');
    }

    public function editUldUnit($unitNumber)
    {
        $uldTypes = $this->getUldTypes();
        $units = $uldTypes[$this->selectedUldType]['units'] ?? [];
        $unit = collect($units)->firstWhere('number', $unitNumber);

        if (! $unit) {
            $this->dispatch('alert', icon: 'error', message: 'ULD unit not found.');

            return;
        }

        $this->editingUldUnitKey = $unitNumber;
        $this->uldUnitForm = [
            'number' => $unit['number'],
            'serviceable' => $unit['serviceable'],
        ];
    }

    public function deleteUldUnit($unitNumber)
    {
        $uldTypes = $this->getUldTypes();
        $units = $uldTypes[$this->selectedUldType]['units'] ?? [];

        $units = collect($units)->reject(function ($unit) use ($unitNumber) {
            return $unit['number'] === $unitNumber;
        })->values()->toArray();

        $uldTypes[$this->selectedUldType]['units'] = $units;

        $this->airline->settings()->updateOrCreate(
            ['key' => 'uld_types'],
            ['value' => json_encode($uldTypes)]
        );

        $this->dispatch('alert', icon: 'success', message: 'ULD unit deleted successfully.');
    }

    public function resetUldUnitForm()
    {
        $this->editingUldUnitKey = null;
        $this->uldUnitForm = [
            'number' => '',
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

    public function render()
    {
        return view('livewire.airline.uld-manager', [
            'uldTypes' => $this->getUldTypes(),
        ]);
    }
}
