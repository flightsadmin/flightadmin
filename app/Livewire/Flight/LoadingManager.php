<?php

namespace App\Livewire\Flight;

use App\Models\Container;
use App\Models\Flight;
use App\Models\Position;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class LoadingManager extends Component
{
    public Flight $flight;
    public $loadplan;
    public $holds;
    public $containers;
    public $showLirfPreview = false;
    public $showWeightSummary = false;
    public $showAssignModal = false;
    public $loadInstructions = [];
    public $selectedContainer = null;
    public $searchQuery = '';
    public $searchResults = [];
    public $selectedType = 'baggage';
    public $unplannedType = null;
    public $attachedContainers = [];
    public function mount(Flight $flight)
    {
        $this->flight = $flight->load([
            'aircraft.type.holds.positions',
            'containers' => fn($q) => $q->withPivot(['type', 'pieces', 'weight', 'status', 'position_id']),
        ]);

        $this->loadplan = $flight->loadplans()->latest()->first();

        $this->holds = $this->flight->aircraft->type->holds->map(function ($hold) {
            return [
                'id' => $hold->id,
                'name' => $hold->name,
                'max_weight' => $hold->max_weight,
                'positions' => $hold->positions->map(fn($pos) => [
                    'id' => $pos->id,
                    'designation' => $pos->code,
                ])->toArray(),
            ];
        })->toArray();

        $this->containers = $this->flight->containers->map(function ($container) {
            return [
                'id' => $container->id,
                'uld_code' => $container->container_number,
                'type' => $container->pivot->type,
                'weight' => $container->pivot->weight,
                'pieces' => $container->pivot->pieces,
                'position' => $container->pivot->position_id,
                'position_code' => $container->pivot->position_id,
                'status' => $container->pivot->status,
                'destination' => $this->flight->arrival_airport,
                'updated_at' => now()->toDateTimeString(),
            ];
        })->toArray();

        // Add deadload items
        $this->addDeadloadItems();
    }

    public function getUnplannedContainersProperty()
    {
        return collect($this->containers)->filter(fn($container) => !$container['position'])->values()->toArray();
    }

    public function getTotalWeightProperty()
    {
        return collect($this->containers)->sum('weight');
    }

    public function selectContainer($containerId)
    {
        if ($this->selectedContainer === $containerId) {
            $this->selectedContainer = null;

            return;
        }
        $this->selectedContainer = $containerId;
    }

    public function handlePositionClick($positionId)
    {
        if ($this->selectedContainer) {
            if (!$this->canDropHere($positionId)) {
                // $this->dispatch('alert', icon: 'error', message: 'Invalid position for this container type');
                return;
            }

            $this->moveContainer($positionId);

            return;
        }

        if (!$this->isPositionOccupied($positionId)) {
            return;
        }

        $container = $this->getContainerInPosition($positionId);
        if (!$container) {
            return;
        }

        $this->selectedContainer = $container['id'];
    }

    public function handleDoubleClick($positionId)
    {
        $container = $this->getContainerInPosition($positionId);
        if (!$container) {
            return;
        }

        $this->updateContainerPosition($container['id'], null);
        $this->selectedContainer = null;
    }

    public function moveContainer($positionId)
    {
        try {
            DB::beginTransaction();

            $container = collect($this->containers)->firstWhere('id', $this->selectedContainer);
            if (!$container) {
                $this->dispatch('alert', icon: 'error', message: 'Container not found');

                return;
            }

            // Update container position
            $this->containers = collect($this->containers)->map(function ($container) use ($positionId) {
                if ($container['id'] === $this->selectedContainer) {
                    $container['position'] = $positionId;
                    $container['position_code'] = $positionId;
                    $container['status'] = 'loaded';
                    $container['updated_at'] = now()->toDateTimeString();
                }

                return $container;
            })->toArray();

            // Update container in database
            $this->flight->containers()->updateExistingPivot($this->selectedContainer, [
                'position_id' => $positionId,
                'status' => 'loaded',
            ]);

            // Update loadplan
            $formattedContainers = $this->formatContainers($this->containers);
            $this->loadplan = $this->flight->loadplans()->updateOrCreate(
                ['flight_id' => $this->flight->id],
                [
                    'loading' => $formattedContainers,
                    'last_modified_by' => auth()->id(),
                    'last_modified_at' => now()->toDateTimeString(),
                    'status' => 'draft',
                    'version' => $this->loadplan ? $this->loadplan->version : 1,
                ]
            );

            DB::commit();
            $this->selectedContainer = null;
            $this->dispatch('container_position_updated');
            $this->dispatch('alert', icon: 'success', message: 'Container loaded successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('alert', icon: 'error', message: 'Failed to load container');
            \Log::error('Failed to move container: ' . $e->getMessage());
        }
    }

    public function updateContainerPosition($containerId, $positionId)
    {
        $this->containers = collect($this->containers)->map(function ($container) use ($containerId, $positionId) {
            if ($container['id'] === $containerId) {
                $container['position'] = $positionId;
                $container['position_code'] = $positionId;
                $container['updated_at'] = now()->toDateTimeString();
            }

            return $container;
        })->toArray();

        $this->flight->containers()->updateExistingPivot($containerId, [
            'position_id' => $positionId,
            'status' => 'unloaded',
        ]);
    }

    public function isPositionOccupied($positionId)
    {
        return collect($this->containers)->contains('position', $positionId);
    }

    public function canDropHere($positionId)
    {
        if (!$this->selectedContainer) {
            return false;
        }

        return !$this->isPositionOccupied($positionId);
    }

    public function getContainerInPosition($positionId)
    {
        return collect($this->containers)->firstWhere('position', $positionId);
    }

    public function getHoldWeight($holdId)
    {
        $hold = collect($this->holds)->firstWhere('id', $holdId);

        return collect($this->containers)
            ->filter(fn($c) => collect($hold['positions'])->pluck('id')->contains($c['position']))
            ->sum('weight');
    }

    public function isHoldOverweight($holdId)
    {
        $hold = collect($this->holds)->firstWhere('id', $holdId);

        return $this->getHoldWeight($holdId) > $hold['max_weight'];
    }

    public function getHoldUtilization($holdId)
    {
        $hold = collect($this->holds)->firstWhere('id', $holdId);

        return ($this->getHoldWeight($holdId) / $hold['max_weight']) * 100;
    }

    public function toggleWeightSummary()
    {
        $this->showWeightSummary = !$this->showWeightSummary;
    }

    public function toggleAssignModal()
    {
        $this->showAssignModal = !$this->showAssignModal;
    }

    public function updatedSearchQuery()
    {
        $this->searchContainers();
    }

    public function searchContainers()
    {
        if (empty($this->searchQuery) || strlen($this->searchQuery) < 2) {
            $this->searchResults = [];

            return;
        }

        $airlineId = $this->flight->airline_id;

        $attachedContainerIds = collect($this->containers)->pluck('id')->toArray();

        $allMatchingContainers = Container::where('airline_id', $airlineId)
            ->where('container_number', 'like', "%{$this->searchQuery}%")
            ->where('serviceable', true)
            ->limit(15)
            ->get();

        $this->searchResults = $allMatchingContainers->map(function ($container) use ($attachedContainerIds) {
            return [
                'id' => $container->id,
                'container_number' => $container->container_number,
                'uld_type' => $container->uld_type,
                'tare_weight' => $container->tare_weight,
                'max_weight' => $container->max_weight,
                'is_attached' => in_array($container->id, $attachedContainerIds),
            ];
        })->toArray();
    }

    #[On('unplanned-items-selected')]
    public function handleUnplannedItemsSelected($type)
    {
        $this->unplannedType = $type;
    }

    #[On('unplanned-items-deselected')]
    public function handleUnplannedItemsDeselected()
    {
        $this->unplannedType = null;
    }

    public function resetLoadplan()
    {
        try {
            DB::beginTransaction();
            foreach ($this->flight->containers as $container) {
                $container->pivot->update([
                    'position_id' => null,
                    'status' => 'unloaded',
                ]);
            }

            $this->loadplan = $this->flight->loadplans()->updateOrCreate(
                [
                    'flight_id' => $this->flight->id,
                ],
                [
                    'loading' => null,
                    'last_modified_by' => auth()->id(),
                    'last_modified_at' => now()->toDateTimeString(),
                    'status' => 'draft',
                ]
            );

            DB::commit();
            $this->dispatch('resetAlpineState');
            $this->dispatch('container_position_updated');
            $this->dispatch('alert', icon: 'success', message: 'All containers unloaded and load plan reset successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('alert', icon: 'error', message: 'Failed to reset load plan');
            \Log::error('Failed to reset loadplan: ' . $e->getMessage());
        }
    }

    public function attachContainer($containerId, $type = 'baggage')
    {
        try {
            DB::beginTransaction();

            $container = Container::findOrFail($containerId);

            if ($this->flight->containers()->where('container_id', $containerId)->exists()) {
                $this->dispatch('alert', icon: 'error', message: 'Container is already attached to this flight');

                return;
            }

            $this->flight->containers()->attach($containerId, [
                'type' => $type,
                'weight' => $container->tare_weight,
                'pieces' => 0,
                'status' => 'unloaded',
            ]);

            $newContainer = [
                'id' => $container->id,
                'uld_code' => $container->container_number,
                'type' => $type,
                'weight' => $container->tare_weight,
                'pieces' => 0,
                'position' => null,
                'position_code' => null,
                'status' => 'unloaded',
                'destination' => $this->flight->arrival_airport,
                'updated_at' => now()->toDateTimeString(),
            ];

            $this->containers[] = $newContainer;

            $this->searchResults = collect($this->searchResults)
                ->map(function ($result) use ($containerId) {
                    if ($result['id'] == $containerId) {
                        $result['is_attached'] = true;
                    }

                    return $result;
                })->toArray();

            DB::commit();
            $this->dispatch('container_position_updated');
            $this->dispatch('alert', icon: 'success', message: 'Container attached successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('alert', icon: 'error', message: 'Failed to attach container: ' . $e->getMessage());
            \Log::error('Failed to attach container: ' . $e->getMessage());
        }
    }

    public function detachContainer($containerId)
    {
        try {
            DB::beginTransaction();

            $container = Container::findOrFail($containerId);

            $this->flight->containers()->updateExistingPivot($containerId, [
                'weight' => $container->tare_weight,
                'pieces' => 0,
                'position_id' => null,
                'status' => 'unloaded',
            ]);

            $this->containers = collect($this->containers)
                ->filter(function ($container) use ($containerId) {
                    return $container['id'] !== $containerId;
                })->toArray();

            $this->flight->containers()->detach($containerId);

            if ($this->loadplan) {
                $this->loadplan->update([
                    'loading' => collect($this->loadplan->loading ?? [])
                        ->filter(function ($item, $key) use ($containerId) {
                            return $key != $containerId;
                        })
                        ->toArray(),
                    'last_modified_by' => auth()->id(),
                    'last_modified_at' => now(),
                ]);
            }

            DB::commit();

            $this->dispatch('container_position_updated');
            $this->dispatch('alert', icon: 'success', message: 'Container detached successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('alert', icon: 'error', message: 'Failed to detach container');
            \Log::error('Failed to detach container: ' . $e->getMessage());
        }
    }

    public function previewLIRF()
    {
        if (isset($this->loadplan) && $this->loadplan->status !== 'released') {
            $this->dispatch('alert', icon: 'error', message: 'Loadplan not released yet.');

            return;
        }

        $this->loadInstructions = collect($this->flight->aircraft->type->holds()
            ->with('positions')
            ->get()
            ->flatMap(function ($hold) {
                return $hold->positions->map(function ($position) use ($hold) {
                    $containerData = collect($this->containers)
                        ->first(function ($container) use ($position) {
                            return $container['position'] === $position->id;
                        });

                    return [
                        'hold' => $hold->name,
                        'position' => $position->code,
                        'container_number' => $containerData['uld_code'] ?? 'NIL',
                        'content_type' => $containerData['type'] ?? 'NIL',
                        'weight' => $containerData['weight'] ?? 0,
                        'pieces' => $containerData['pieces'] ?? null,
                        'destination' => $containerData['destination'] ?? $this->flight->arrival_airport,
                        'is_empty' => is_null($containerData),
                    ];
                });
            }))
            ->sortBy([
                ['hold', 'asc'],
                ['position', 'asc'],
            ])->values()->toArray();

        $this->showLirfPreview = true;
        $this->dispatch('show-lirf-preview');
    }

    #[On('unplanned-items-added')]
    public function loadUnplanned($data)
    {
        try {
            DB::beginTransaction();

            $position = collect($this->holds)->flatMap(fn($hold) => $hold['positions'])->firstWhere('id', $data['positionId']);

            if (!$position) {
                $this->dispatch('alert', icon: 'error', message: 'Position not found');

                return;
            }

            // Create or update the bulk container for this position
            $existingContainer = collect($this->containers)->firstWhere(fn($c) => $c['position'] === $position['id']);

            if ($existingContainer) {
                $this->containers = collect($this->containers)->map(function ($container) use ($data, $position) {
                    if ($container['position'] === $position['id']) {
                        $container['pieces'] += $data['pieces'];
                        $container['weight'] += $data['weight'];
                    }

                    return $container;
                })->toArray();
            } else {
                // Create new bulk container
                $newContainer = [
                    'id' => $position['id'],
                    'uld_code' => 'BULK',
                    'type' => $data['type'],
                    'weight' => $data['weight'],
                    'pieces' => $data['pieces'],
                    'position' => $position['id'],
                    'position_code' => $position['designation'],
                    'status' => 'loaded',
                    'destination' => $this->flight->arrival_airport,
                    'updated_at' => now()->toDateTimeString(),
                ];

                $this->containers[] = $newContainer;
            }

            $formattedContainers = $this->formatContainers($this->containers);

            $this->loadplan = $this->flight->loadplans()->updateOrCreate(
                [
                    'flight_id' => $this->flight->id,
                ],
                [
                    'loading' => $formattedContainers,
                    'released_by' => auth()->id(),
                    'released_at' => now(),
                    'last_modified_by' => auth()->id(),
                    'last_modified_at' => now()->toDateTimeString(),
                    'status' => 'released',
                    'version' => $this->loadplan ? $this->loadplan->version + 1 : 1,
                ]
            );

            // Update the baggage/cargo records to mark them as loaded
            if ($data['type'] === 'baggage') {
                $this->flight->baggage()
                    ->whereNull('container_id')
                    ->limit($data['pieces'])
                    ->update(['container_id' => $position['id']]);
            } else {
                $this->flight->cargo()
                    ->whereNull('container_id')
                    ->limit($data['pieces'])
                    ->update(['container_id' => $position['id']]);
            }

            DB::commit();
            $this->dispatch('alert', icon: 'success', message: ucfirst($data['type']) . ' added successfully');
            $this->dispatch('container_position_updated');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('alert', icon: 'error', message: 'Failed to add ' . $data['type']);
            \Log::error('Failed to add unplanned items: ' . $e->getMessage());
        }
    }

    public function formatContainers($containers)
    {
        return collect($containers)->mapWithKeys(function ($container) {
            $hold = collect($this->holds)->first(function ($hold) use ($container) {
                return collect($hold['positions'])->pluck('id')->contains($container['position']);
            });

            $data = [
                'pieces' => $container['pieces'],
                'weight' => $container['weight'],
                'hold_name' => $hold ? $hold['name'] : null,
                'destination' => $container['destination'],
                'position_id' => $container['position'],
                'content_type' => $container['type'],
                'position_code' => $container['position_code'],
                'container_number' => $container['uld_code'],
                'updated_at' => now()->toDateTimeString(),
            ];

            // Add deadload specific fields if applicable
            if (isset($container['is_deadload']) && $container['is_deadload']) {
                $data['is_deadload'] = true;
                $data['name'] = $container['name'];
            }

            return [
                $container['id'] => $data,
            ];
        })->toArray();
    }

    #[On('container_position_updated')]
    public function refreshContainers()
    {
        $this->flight->load(['containers' => fn($q) => $q->withPivot(['type', 'pieces', 'weight', 'status', 'position_id'])]);

        $this->containers = $this->flight->containers->map(function ($container) {
            return [
                'id' => $container->id,
                'uld_code' => $container->container_number,
                'type' => $container->pivot->type,
                'weight' => $container->pivot->weight,
                'pieces' => $container->pivot->pieces,
                'position' => $container->pivot->position_id,
                'position_code' => $container->pivot->position_id,
                'status' => $container->pivot->status,
                'destination' => $this->flight->arrival_airport,
                'updated_at' => now()->toDateTimeString(),
            ];
        })->toArray();

        // Add deadload items as virtual containers
        $this->addDeadloadItems();
    }

    protected function addDeadloadItems()
    {
        // Load deadload items from settings
        $deadloadSetting = $this->flight->settings()
            ->where('key', 'manual_deadload')
            ->first();

        if ($deadloadSetting) {
            $deadloadItems = json_decode($deadloadSetting->value, true) ?: [];

            // Group deadload items by position
            $groupedItems = collect($deadloadItems)
                ->filter(fn($item) => !empty($item['position']))
                ->groupBy('position')
                ->toArray();

            foreach ($groupedItems as $positionId => $items) {
                // Calculate total weight and pieces for this position
                $totalWeight = collect($items)->sum(function ($item) {
                    return $item['weight'] * $item['pieces'];
                });

                $totalPieces = collect($items)->sum('pieces');

                // Get the types of items in this position
                $types = collect($items)->pluck('type')->unique()->implode('/');

                // Create a description of all items in this position
                $description = collect($items)->map(function ($item) {
                    return $item['pieces'] . ' × ' . $item['weight'] . 'kg ' . ucfirst($item['type']);
                })->implode(', ');

                // Add as a single container with combined values
                $this->containers[] = [
                    'id' => 'deadload_' . $positionId . '_' . md5(json_encode($items)),
                    'uld_code' => 'DEADLOAD',
                    'type' => $types,
                    'weight' => $totalWeight,
                    'pieces' => $totalPieces,
                    'position' => $positionId,
                    'position_code' => $positionId,
                    'status' => 'loaded',
                    'destination' => $this->flight->arrival_airport,
                    'updated_at' => now()->toDateTimeString(),
                    'is_deadload' => true,
                    'deadload_description' => $description,
                    'deadload_items' => $items,
                ];
            }
        }
    }

    public function finalizeLoadplan()
    {
        $positionedContainers = collect($this->containers)
            ->filter(fn($container) => !empty($container['position']))
            ->count();

        if ($positionedContainers === 0) {
            $this->dispatch('swal:confirm');
            return;
        }

        $this->finalizeLoadplanAction();
    }


    public function finalizeLoadplanAction()
    {
        $loadplan = $this->flight->loadplans()->latest()->first();

        if (!$loadplan) {
            $loadplan = $this->flight->loadplans()->create([
                'version' => 1,
                'status' => 'released',
                'released_by' => auth()->id(),
                'released_at' => now(),
            ]);
        } else {
            $loadplan->update([
                'status' => 'released',
                'released_by' => auth()->id(),
                'released_at' => now(),
            ]);
        }

        $this->dispatch('alert', icon: 'success', message: 'Load plan released successfully');
        $this->dispatch('loadplan-updated');
    }

    #[On('deadload-updated')]
    public function handleDeadloadUpdated()
    {
        // Refresh the containers to include deadload items
        $this->refreshContainers();
    }

    public function render()
    {
        // Debug: Check if deadload items are loaded
        $deadloadSetting = $this->flight->settings()
            ->where('key', 'manual_deadload')
            ->first();

        if ($deadloadSetting) {
            $deadloadItems = json_decode($deadloadSetting->value, true) ?: [];
            // Log the deadload items for debugging
            \Log::info('Deadload items found: ' . count($deadloadItems));

            // Check if any deadload items have positions
            $positionedItems = collect($deadloadItems)->filter(fn($item) => !empty($item['position']))->count();
            \Log::info('Positioned deadload items: ' . $positionedItems);

            // Check if any containers have is_deadload flag
            $deadloadContainers = collect($this->containers)->filter(fn($c) => isset($c['is_deadload']) && $c['is_deadload'])->count();
            \Log::info('Deadload containers: ' . $deadloadContainers);
        } else {
            \Log::info('No deadload setting found');
        }

        return view('livewire.flights.loading-manager', [
            'loadplan' => $this->loadplan,
        ]);
    }
}
