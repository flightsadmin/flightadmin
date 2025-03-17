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

    public $showBulkPositionModal = false;

    public $bulkPositionData = [];

    public $deadloadSelectionActive = false;

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
        // If deadload selection is active, assign deadload to this container
        if ($this->deadloadSelectionActive) {
            // Get the selected deadload IDs from the DeadloadManager component
            $this->dispatch('get-selected-deadload-ids', $containerId);
            return;
        }

        // Normal container selection behavior
        if ($this->selectedContainer === $containerId) {
            $this->selectedContainer = null;
            return;
        }
        $this->selectedContainer = $containerId;
    }

    #[On('provide-selected-deadload-ids')]
    public function receiveSelectedDeadloadIds($data)
    {
        $deadloadIds = $data['ids'] ?? [];
        $containerId = $data['containerId'] ?? null;

        if (empty($deadloadIds)) {
            $this->dispatch('alert', icon: 'error', message: 'No deadload items selected');
            return;
        }

        if (!$containerId) {
            $this->dispatch('alert', icon: 'error', message: 'No container selected');
            return;
        }

        $this->assignBulkDeadloadToContainer($deadloadIds, $containerId);

        // The selection will be cleared in the assignBulkDeadloadToContainer method
    }

    public function handlePositionClick($positionId)
    {
        // If deadload selection is active, handle it differently
        if ($this->deadloadSelectionActive) {
            // Handle deadload assignment to position
            return;
        }

        // If a container is selected, try to drop it here
        if ($this->selectedContainer) {
            // Check if the clicked position contains the currently selected container
            $containerInPosition = $this->getContainerInPosition($positionId);
            if ($containerInPosition && $containerInPosition['id'] === $this->selectedContainer) {
                // If clicking on the already selected container, deselect it
                $this->selectedContainer = null;
                return;
            }

            // Otherwise try to drop the selected container here
            if ($this->canDropHere($positionId)) {
                $this->assignContainerToPosition($this->selectedContainer, $positionId);
                $this->selectedContainer = null;
            }
            return;
        }

        // If unplanned type is selected, handle it
        if ($this->unplannedType) {
            // Check if the position is in a bulk hold
            $position = $this->getPositionById($positionId);
            $hold = $this->getHoldByPositionId($positionId);

            if ($hold && str_contains($hold['name'], 'Bulk') && !$this->isPositionOccupied($positionId)) {
                // Open the pieces modal for bulk positions
                $this->dispatch('open-pieces-modal', [
                    'positionId' => $positionId,
                    'type' => $this->unplannedType
                ]);
            }
            return;
        }

        // Otherwise, select the container in this position
        $container = $this->getContainerInPosition($positionId);
        if ($container) {
            $this->selectedContainer = $container['id'];
        }
    }

    public function handleDoubleClick($positionId)
    {
        $container = $this->getContainerInPosition($positionId);
        if (!$container) {
            return;
        }

        // Check if this is a bulk container or a regular container
        $isBulkContainer = str_starts_with($container['id'], 'bulk_');
        $isDeadloadContainer = str_starts_with($container['id'], 'deadload_');

        // For bulk containers, we need special handling
        if ($isBulkContainer) {
            try {
                DB::beginTransaction();

                // Remove the bulk container from the containers array
                $this->containers = collect($this->containers)
                    ->filter(function ($c) use ($container) {
                        return $c['id'] !== $container['id'];
                    })->toArray();

                // Update the loadplan to reflect the removal
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

                // Log the offload
                \Log::info('Bulk container offloaded', [
                    'containerId' => $container['id'],
                    'positionId' => $positionId,
                    'type' => $container['type'],
                    'weight' => $container['weight']
                ]);

                DB::commit();

                // Clear selection if this was the selected container
                if ($this->selectedContainer === $container['id']) {
                    $this->selectedContainer = null;
                }

                $this->dispatch('container_position_updated');
                $this->dispatch('alert', icon: 'success', message: 'Bulk container offloaded successfully');

            } catch (\Exception $e) {
                DB::rollBack();
                $this->dispatch('alert', icon: 'error', message: 'Failed to offload bulk container: ' . $e->getMessage());
                \Log::error('Failed to offload bulk container: ' . $e->getMessage());
            }

            return;
        }

        // For deadload containers, we should not handle them here
        if ($isDeadloadContainer) {
            $this->dispatch('alert', icon: 'info', message: 'Deadload items must be offloaded from the Deadload Manager');
            return;
        }

        // For regular containers, use the existing logic
        $this->updateContainerPosition($container['id'], null);

        // Clear selection if this was the selected container
        if ($this->selectedContainer === $container['id']) {
            $this->selectedContainer = null;
        }

        // Dispatch an event to refresh the UI
        $this->dispatch('container_position_updated');
        $this->dispatch('alert', icon: 'success', message: 'Container offloaded successfully');
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
        try {
            DB::beginTransaction();

            // Update the container position in memory
            $this->containers = collect($this->containers)->map(function ($container) use ($containerId, $positionId) {
                if ($container['id'] === $containerId) {
                    $container['position'] = $positionId;
                    $container['position_code'] = $positionId;
                    $container['status'] = $positionId ? 'loaded' : 'unloaded';
                    $container['updated_at'] = now()->toDateTimeString();
                }

                return $container;
            })->toArray();

            // Update the container in the database
            $this->flight->containers()->updateExistingPivot($containerId, [
                'position_id' => $positionId,
                'status' => $positionId ? 'loaded' : 'unloaded',
            ]);

            // Update the loadplan
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

            // Log the position update
            \Log::info('Container position updated', [
                'containerId' => $containerId,
                'newPositionId' => $positionId,
                'status' => $positionId ? 'loaded' : 'unloaded'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('alert', icon: 'error', message: 'Failed to update container position: ' . $e->getMessage());
            \Log::error('Failed to update container position: ' . $e->getMessage());
        }
    }

    public function isPositionOccupied($positionId)
    {
        return collect($this->containers)->contains('position', $positionId);
    }

    public function canDropHere($positionId)
    {
        // If no container is selected, nothing can be dropped
        if (!$this->selectedContainer) {
            return false;
        }

        // Get the position
        $position = $this->getPositionById($positionId);
        if (!$position) {
            return false;
        }

        // Check if the position is already occupied
        if ($this->isPositionOccupied($positionId)) {
            return false;
        }

        // Get the container
        $container = collect($this->containers)->firstWhere('id', $this->selectedContainer);
        if (!$container) {
            return false;
        }

        // Get the hold for this position
        $hold = collect($this->holds)->first(function ($hold) use ($positionId) {
            return collect($hold['positions'])->contains('id', $positionId);
        });

        if (!$hold) {
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
        $this->unplannedType = is_array($type) ? $type['type'] : $type;
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

            // Log the received data for debugging
            \Log::info('Received unplanned-items-added with data:', $data);

            // Ensure positionId is properly formatted
            $positionId = $data['positionId'] ?? null;

            if (!$positionId) {
                $this->dispatch('alert', icon: 'error', message: 'Position ID is missing');
                DB::rollBack();
                return;
            }

            // Find the position in the holds
            $position = null;
            foreach ($this->holds as $hold) {
                foreach ($hold['positions'] as $pos) {
                    if ($pos['id'] == $positionId) {
                        $position = $pos;
                        break 2;
                    }
                }
            }

            if (!$position) {
                $this->dispatch('alert', icon: 'error', message: 'Position not found: ' . $positionId);
                DB::rollBack();
                return;
            }

            // Create or update the bulk container for this position
            $existingContainer = collect($this->containers)->firstWhere(fn($c) => $c['position'] == $positionId);

            if ($existingContainer) {
                $this->containers = collect($this->containers)->map(function ($container) use ($data, $positionId) {
                    if ($container['position'] == $positionId) {
                        $container['pieces'] += $data['pieces'];
                        $container['weight'] += $data['weight'];
                    }

                    return $container;
                })->toArray();
            } else {
                // Create new bulk container
                $newContainer = [
                    'id' => 'bulk_' . $positionId . '_' . uniqid(),
                    'uld_code' => 'BULK',
                    'type' => $data['type'],
                    'weight' => $data['weight'],
                    'pieces' => $data['pieces'],
                    'position' => $positionId,
                    'position_code' => $position['designation'] ?? $positionId,
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
                    ->update(['container_id' => $positionId]);
            } else {
                $this->flight->cargo()
                    ->whereNull('container_id')
                    ->limit($data['pieces'])
                    ->update(['container_id' => $positionId]);
            }

            DB::commit();
            $this->dispatch('alert', icon: 'success', message: ucfirst($data['type']) . ' added successfully');
            $this->dispatch('container_position_updated');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('alert', icon: 'error', message: 'Failed to add ' . ($data['type'] ?? 'items') . ': ' . $e->getMessage());
            \Log::error('Failed to add unplanned items: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
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
        // Log the current containers for debugging
        \Log::info('Refreshing containers - before refresh', [
            'containerCount' => count($this->containers),
            'containerWeights' => collect($this->containers)->pluck('weight', 'id')->toArray(),
            'containerPositions' => collect($this->containers)->pluck('position', 'id')->toArray()
        ]);

        // Reload containers from database
        $this->flight->load(['containers' => fn($q) => $q->withPivot(['type', 'pieces', 'weight', 'status', 'position_id'])]);

        // Clear the containers array to avoid duplicates
        $this->containers = [];

        // Rebuild the containers array from the database
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

        // Log the refreshed containers for debugging
        \Log::info('Refreshing containers - after refresh', [
            'containerCount' => count($this->containers),
            'containerWeights' => collect($this->containers)->pluck('weight', 'id')->toArray(),
            'containerPositions' => collect($this->containers)->pluck('position', 'id')->toArray()
        ]);
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
                // Calculate total weight for this position
                $totalWeight = collect($items)->sum('weight');
                $totalPieces = collect($items)->sum('pieces');

                // Get the types of items in this position
                $types = collect($items)->pluck('type')->unique()->implode('/');

                // Create a description of all items in this position
                $description = collect($items)->map(function ($item) {
                    return $item['pieces'] . ' pcs, ' . $item['weight'] . 'kg ' . ucfirst($item['type']);
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

            // Add deadload items assigned to containers
            $containerItems = collect($deadloadItems)
                ->filter(fn($item) => !empty($item['container_id']))
                ->groupBy('container_id')
                ->toArray();

            // Log for debugging
            \Log::info('Processing deadload items assigned to containers', [
                'containerCount' => count($containerItems),
                'containers' => array_keys($containerItems)
            ]);

            foreach ($containerItems as $containerId => $items) {
                // Find the container
                $containerIndex = collect($this->containers)->search(function ($c) use ($containerId) {
                    return $c['id'] == $containerId;
                });

                if ($containerIndex !== false) {
                    $container = $this->containers[$containerIndex];

                    // Get the types of deadload items
                    $deadloadTypes = collect($items)->pluck('type')->unique()->implode('/');

                    // Create a description of all deadload items
                    $deadloadDescription = collect($items)->map(function ($item) {
                        return $item['pieces'] . ' pcs, ' . $item['weight'] . 'kg ' . ucfirst($item['type']);
                    })->implode(', ');

                    // IMPORTANT: Don't add the weight again here, as it's already in the database
                    // Just add the deadload info to the container
                    $this->containers[$containerIndex]['deadload_items'] = $items;
                    $this->containers[$containerIndex]['has_deadload'] = true;
                    $this->containers[$containerIndex]['deadload_types'] = $deadloadTypes;
                    $this->containers[$containerIndex]['deadload_description'] = $deadloadDescription;
                    $this->containers[$containerIndex]['pieces'] = max(1, $container['pieces']); // Ensure container shows as non-empty

                    // Log for debugging
                    \Log::info('Added deadload info to container without changing weight', [
                        'containerId' => $containerId,
                        'itemsCount' => count($items),
                        'containerWeight' => $container['weight']
                    ]);
                } else {
                    \Log::warning('Container not found for deadload items', [
                        'containerId' => $containerId,
                        'itemsCount' => count($items)
                    ]);
                }
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

    public function handleBulkPositionClick($positionId, $type)
    {
        // Log for debugging
        \Log::info('Handling bulk position click', ['positionId' => $positionId, 'type' => $type]);

        // Find the position in the holds to verify it exists
        $position = null;
        foreach ($this->holds as $hold) {
            foreach ($hold['positions'] as $pos) {
                if ($pos['id'] == $positionId) {
                    $position = $pos;
                    break 2;
                }
            }
        }

        if (!$position) {
            $this->dispatch('alert', icon: 'error', message: 'Position not found: ' . $positionId);
            return;
        }

        // Now that we've verified the position exists, dispatch the event with the position ID
        $this->dispatch('open-pieces-modal', [
            'positionId' => $positionId,
            'type' => $type,
            'designation' => $position['designation'] ?? 'Unknown'
        ]);
    }

    public function openBulkPositionModal($positionId, $type)
    {
        // Log for debugging
        \Log::info('Opening bulk position modal', ['positionId' => $positionId, 'type' => $type]);

        // Find the position in the holds
        $position = null;
        foreach ($this->holds as $hold) {
            foreach ($hold['positions'] as $pos) {
                if ($pos['id'] == $positionId) {
                    $position = $pos;
                    break 2;
                }
            }
        }

        if (!$position) {
            $this->dispatch('alert', icon: 'error', message: 'Position not found: ' . $positionId);
            return;
        }

        // Show a modal directly in this component instead of dispatching to another component
        $this->showBulkPositionModal = true;
        $this->bulkPositionData = [
            'positionId' => $positionId,
            'type' => $type,
            'pieces' => 1,
            'weight' => $type === 'baggage' ? 15 : 50
        ];
    }

    public function loadItemsIntoPosition($positionId, $type, $pieces, $weight)
    {
        try {
            DB::beginTransaction();

            // Find the position in the holds
            $position = null;
            foreach ($this->holds as $hold) {
                foreach ($hold['positions'] as $pos) {
                    if ($pos['id'] == $positionId) {
                        $position = $pos;
                        break 2;
                    }
                }
            }

            if (!$position) {
                $this->dispatch('alert', icon: 'error', message: 'Position not found: ' . $positionId);
                DB::rollBack();
                return;
            }

            // Create or update the bulk container for this position
            $existingContainer = collect($this->containers)->firstWhere(fn($c) => $c['position'] == $positionId);

            if ($existingContainer) {
                $this->containers = collect($this->containers)->map(function ($container) use ($pieces, $weight, $positionId) {
                    if ($container['position'] == $positionId) {
                        $container['pieces'] += $pieces;
                        $container['weight'] += $weight;
                    }

                    return $container;
                })->toArray();
            } else {
                // Create new bulk container
                $newContainer = [
                    'id' => 'bulk_' . $positionId . '_' . uniqid(),
                    'uld_code' => 'BULK',
                    'type' => $type,
                    'weight' => $weight,
                    'pieces' => $pieces,
                    'position' => $positionId,
                    'position_code' => $position['designation'] ?? $positionId,
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
                    'last_modified_by' => auth()->id(),
                    'last_modified_at' => now()->toDateTimeString(),
                    'status' => 'draft',
                    'version' => $this->loadplan ? $this->loadplan->version : 1,
                ]
            );

            // Update the baggage/cargo records to mark them as loaded
            if ($type === 'baggage') {
                $this->flight->baggage()
                    ->whereNull('container_id')
                    ->limit($pieces)
                    ->update(['container_id' => $positionId]);
            } else {
                $this->flight->cargo()
                    ->whereNull('container_id')
                    ->limit($pieces)
                    ->update(['container_id' => $positionId]);
            }

            DB::commit();
            $this->dispatch('alert', icon: 'success', message: ucfirst($type) . ' added successfully');
            $this->dispatch('container_position_updated');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('alert', icon: 'error', message: 'Failed to add ' . $type . ': ' . $e->getMessage());
            \Log::error('Failed to add items to position: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
        }
    }

    public function bulkLoadContainers()
    {
        // Show a modal for bulk container loading
        $this->dispatch('open-bulk-container-modal');
    }

    #[On('bulk-containers-added')]
    public function processBulkContainers($data)
    {
        try {
            DB::beginTransaction();

            $containerType = $data['type'] ?? 'baggage';
            $count = $data['count'] ?? 1;
            $airlineId = $this->flight->airline_id;

            // Find available containers
            $availableContainers = Container::where('airline_id', $airlineId)
                ->where('serviceable', true)
                ->whereNotIn('id', collect($this->containers)->pluck('id'))
                ->limit($count)
                ->get();

            if ($availableContainers->count() === 0) {
                $this->dispatch('alert', icon: 'error', message: 'No available containers found');
                DB::rollBack();
                return;
            }

            $addedCount = 0;

            foreach ($availableContainers as $container) {
                // Attach container to flight
                $this->flight->containers()->attach($container->id, [
                    'type' => $containerType,
                    'weight' => $container->tare_weight,
                    'pieces' => 0,
                    'status' => 'unloaded',
                ]);

                // Add to local containers array
                $this->containers[] = [
                    'id' => $container->id,
                    'uld_code' => $container->container_number,
                    'type' => $containerType,
                    'weight' => $container->tare_weight,
                    'pieces' => 0,
                    'position' => null,
                    'position_code' => null,
                    'status' => 'unloaded',
                    'destination' => $this->flight->arrival_airport,
                    'updated_at' => now()->toDateTimeString(),
                ];

                $addedCount++;
            }

            DB::commit();
            $this->dispatch('container_position_updated');
            $this->dispatch('alert', icon: 'success', message: $addedCount . ' containers added successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('alert', icon: 'error', message: 'Failed to add containers: ' . $e->getMessage());
            \Log::error('Failed to add bulk containers: ' . $e->getMessage());
        }
    }

    #[On('find-empty-bulk-positions')]
    public function findEmptyBulkPositions($data)
    {
        try {
            DB::beginTransaction();

            $type = $data['type'] ?? 'baggage';
            $pieces = $data['pieces'] ?? 1;
            $weight = $data['weight'] ?? 0;

            // Find all empty bulk positions
            $emptyBulkPositions = [];
            foreach ($this->holds as $hold) {
                if (str_contains($hold['name'], 'Bulk')) {
                    foreach ($hold['positions'] as $position) {
                        if (!$this->isPositionOccupied($position['id'])) {
                            $emptyBulkPositions[] = $position;
                        }
                    }
                }
            }

            if (count($emptyBulkPositions) === 0) {
                $this->dispatch('alert', icon: 'error', message: 'No empty bulk positions found');
                DB::rollBack();
                return;
            }

            // Use the first empty position
            $position = $emptyBulkPositions[0];

            // Create new bulk container
            $newContainer = [
                'id' => 'bulk_' . $position['id'] . '_' . uniqid(),
                'uld_code' => 'BULK',
                'type' => $type,
                'weight' => $weight,
                'pieces' => $pieces,
                'position' => $position['id'],
                'position_code' => $position['designation'],
                'status' => 'loaded',
                'destination' => $this->flight->arrival_airport,
                'updated_at' => now()->toDateTimeString(),
            ];

            $this->containers[] = $newContainer;

            $formattedContainers = $this->formatContainers($this->containers);

            $this->loadplan = $this->flight->loadplans()->updateOrCreate(
                [
                    'flight_id' => $this->flight->id,
                ],
                [
                    'loading' => $formattedContainers,
                    'last_modified_by' => auth()->id(),
                    'last_modified_at' => now()->toDateTimeString(),
                    'status' => 'draft',
                    'version' => $this->loadplan ? $this->loadplan->version : 1,
                ]
            );

            // Update the baggage/cargo records to mark them as loaded
            if ($type === 'baggage') {
                $this->flight->baggage()
                    ->whereNull('container_id')
                    ->limit($pieces)
                    ->update(['container_id' => $position['id']]);
            } else {
                $this->flight->cargo()
                    ->whereNull('container_id')
                    ->limit($pieces)
                    ->update(['container_id' => $position['id']]);
            }

            DB::commit();
            $this->dispatch('alert', icon: 'success', message: ucfirst($type) . ' added to bulk position successfully');
            $this->dispatch('container_position_updated');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('alert', icon: 'error', message: 'Failed to add items to bulk position: ' . $e->getMessage());
            \Log::error('Failed to add items to bulk position: ' . $e->getMessage());
        }
    }

    #[On('assign-deadload-to-container')]
    public function assignDeadloadToContainer($deadloadId, $containerId = null)
    {
        try {
            DB::beginTransaction();

            // If no container ID is provided, use the currently selected container
            if (!$containerId && $this->selectedContainer) {
                $containerId = $this->selectedContainer;
            }

            if (!$containerId) {
                $this->dispatch('alert', icon: 'error', message: 'No container selected');
                DB::rollBack();
                return;
            }

            // Get the container
            $container = collect($this->containers)->firstWhere('id', $containerId);
            if (!$container) {
                $this->dispatch('alert', icon: 'error', message: 'Container not found');
                DB::rollBack();
                return;
            }

            // Get the deadload settings
            $deadloadSetting = $this->flight->settings()
                ->where('key', 'manual_deadload')
                ->first();

            if (!$deadloadSetting) {
                $this->dispatch('alert', icon: 'error', message: 'No deadload items found');
                DB::rollBack();
                return;
            }

            $deadloadItems = json_decode($deadloadSetting->value, true) ?: [];

            // Find the deadload item
            $deadloadItem = collect($deadloadItems)->firstWhere('id', $deadloadId);
            if (!$deadloadItem) {
                $this->dispatch('alert', icon: 'error', message: 'Deadload item not found');
                DB::rollBack();
                return;
            }

            // Check if this item is already assigned to this container
            if (isset($deadloadItem['container_id']) && $deadloadItem['container_id'] == $containerId) {
                $this->dispatch('alert', icon: 'error', message: 'This item is already assigned to this container');
                DB::rollBack();
                return;
            }

            // Log for debugging
            \Log::info('Assigning single deadload item to container', [
                'containerId' => $containerId,
                'deadloadId' => $deadloadId,
                'deadloadWeight' => $deadloadItem['weight'],
                'containerCurrentWeight' => $container['weight']
            ]);

            // Update the deadload item to assign it to the container
            $deadloadItems = collect($deadloadItems)->map(function ($item) use ($deadloadId, $containerId) {
                if ($item['id'] == $deadloadId) {
                    $item['container_id'] = $containerId;
                    $item['position'] = null; // Remove from position if it was assigned
                }
                return $item;
            })->toArray();

            // Save the updated deadload settings
            $this->flight->settings()
                ->where('key', 'manual_deadload')
                ->update(['value' => json_encode($deadloadItems)]);

            // Update the container weight - use the weight directly
            $deadloadWeight = $deadloadItem['weight'];

            // If it's a real container (not a virtual one), update in database
            if (!str_starts_with($containerId, 'deadload_') && !str_starts_with($containerId, 'bulk_')) {
                // Get the current container from the database to ensure we have the latest weight
                $dbContainer = $this->flight->containers()->where('container_id', $containerId)->first();

                if ($dbContainer) {
                    // Update with the exact weight calculation to avoid any rounding issues
                    $this->flight->containers()->updateExistingPivot($containerId, [
                        'weight' => $dbContainer->pivot->weight + $deadloadWeight,
                    ]);

                    // Log the update for debugging
                    \Log::info('Updated container weight in database', [
                        'containerId' => $containerId,
                        'oldWeight' => $dbContainer->pivot->weight,
                        'addedWeight' => $deadloadWeight,
                        'newWeight' => $dbContainer->pivot->weight + $deadloadWeight
                    ]);
                }
            }

            DB::commit();

            // Clear the deadload selection and refresh the component
            $this->dispatch('cancel-deadload-selection');
            $this->dispatch('refresh-deadload-component');
            $this->dispatch('deadload-updated');
            $this->dispatch('alert', icon: 'success', message: 'Deadload assigned to container successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('alert', icon: 'error', message: 'Failed to assign deadload: ' . $e->getMessage());
            \Log::error('Failed to assign deadload to container: ' . $e->getMessage());
        }
    }

    #[On('assign-bulk-deadload-to-container')]
    public function assignBulkDeadloadToContainer($deadloadIds, $containerId = null)
    {
        try {
            DB::beginTransaction();

            // If no container ID is provided, use the currently selected container
            if (!$containerId && $this->selectedContainer) {
                $containerId = $this->selectedContainer;
            }

            if (!$containerId) {
                $this->dispatch('alert', icon: 'error', message: 'No container selected');
                DB::rollBack();
                return;
            }

            // Get the container
            $container = collect($this->containers)->firstWhere('id', $containerId);
            if (!$container) {
                $this->dispatch('alert', icon: 'error', message: 'Container not found');
                DB::rollBack();
                return;
            }

            // Get the deadload settings
            $deadloadSetting = $this->flight->settings()
                ->where('key', 'manual_deadload')
                ->first();

            if (!$deadloadSetting) {
                $this->dispatch('alert', icon: 'error', message: 'No deadload items found');
                DB::rollBack();
                return;
            }

            $deadloadItems = json_decode($deadloadSetting->value, true) ?: [];

            // Check if any of these items are already assigned to this container
            $alreadyAssigned = collect($deadloadItems)
                ->filter(function ($item) use ($deadloadIds, $containerId) {
                    return in_array($item['id'], $deadloadIds) && $item['container_id'] == $containerId;
                })
                ->count();

            if ($alreadyAssigned > 0) {
                $this->dispatch('alert', icon: 'error', message: 'One or more items are already assigned to this container');
                DB::rollBack();
                return;
            }

            // Calculate total weight to add
            $totalWeight = 0;

            // Ensure deadloadIds is an array
            if (!is_array($deadloadIds)) {
                $deadloadIds = [$deadloadIds];
            }

            // Get the deadload items that will be assigned
            $itemsToAssign = collect($deadloadItems)
                ->filter(function ($item) use ($deadloadIds) {
                    return in_array($item['id'], $deadloadIds);
                })
                ->values()
                ->toArray();

            // Calculate the total weight of items to assign
            $totalWeight = collect($itemsToAssign)->sum('weight');

            // Log for debugging
            \Log::info('Assigning deadload items to container', [
                'containerId' => $containerId,
                'itemsCount' => count($itemsToAssign),
                'totalWeight' => $totalWeight,
                'containerCurrentWeight' => $container['weight'],
                'itemWeights' => collect($itemsToAssign)->pluck('weight')->toArray()
            ]);

            // Update the deadload items to assign them to the container
            $deadloadItems = collect($deadloadItems)->map(function ($item) use ($deadloadIds, $containerId) {
                if (in_array($item['id'], $deadloadIds)) {
                    $item['container_id'] = $containerId;
                    $item['position'] = null; // Remove from position if it was assigned
                }
                return $item;
            })->toArray();

            // Save the updated deadload settings
            $this->flight->settings()
                ->where('key', 'manual_deadload')
                ->update(['value' => json_encode($deadloadItems)]);

            // If it's a real container (not a virtual one), update in database
            if (!str_starts_with($containerId, 'deadload_') && !str_starts_with($containerId, 'bulk_')) {
                // Get the current container from the database to ensure we have the latest weight
                $dbContainer = $this->flight->containers()->where('container_id', $containerId)->first();

                if ($dbContainer) {
                    // Update with the exact weight calculation to avoid any rounding issues
                    $this->flight->containers()->updateExistingPivot($containerId, [
                        'weight' => $dbContainer->pivot->weight + $totalWeight,
                    ]);

                    // Log the update for debugging
                    \Log::info('Updated container weight in database', [
                        'containerId' => $containerId,
                        'oldWeight' => $dbContainer->pivot->weight,
                        'addedWeight' => $totalWeight,
                        'newWeight' => $dbContainer->pivot->weight + $totalWeight
                    ]);
                }
            }

            DB::commit();

            // Clear the deadload selection in the DeadloadManager component
            $this->dispatch('cancel-deadload-selection');

            // Reset the deadload selection active state
            $this->deadloadSelectionActive = false;

            // Refresh the containers to update the view
            $this->dispatch('deadload-updated');
            $this->dispatch('alert', icon: 'success', message: 'Deadload items assigned to container successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('alert', icon: 'error', message: 'Failed to assign deadload: ' . $e->getMessage());
            \Log::error('Failed to assign bulk deadload to container: ' . $e->getMessage());
        }
    }

    #[On('deadload-selection-changed')]
    public function handleDeadloadSelectionChanged($isActive)
    {
        $this->deadloadSelectionActive = $isActive;
    }

    public function openDeadloadModal()
    {
        $this->dispatch('open-deadload-modal');
    }

    /**
     * Assign a container to a position
     */
    public function assignContainerToPosition($containerId, $positionId)
    {
        try {
            DB::beginTransaction();

            // Get the container
            $container = collect($this->containers)->firstWhere('id', $containerId);
            if (!$container) {
                $this->dispatch('alert', icon: 'error', message: 'Container not found');
                DB::rollBack();
                return;
            }

            // Get the position
            $position = $this->getPositionById($positionId);
            if (!$position) {
                $this->dispatch('alert', icon: 'error', message: 'Position not found');
                DB::rollBack();
                return;
            }

            // Check if the position is already occupied
            if ($this->isPositionOccupied($positionId)) {
                $this->dispatch('alert', icon: 'error', message: 'Position is already occupied');
                DB::rollBack();
                return;
            }

            // Get the hold for this position
            $hold = $this->getHoldByPositionId($positionId);
            if (!$hold) {
                $this->dispatch('alert', icon: 'error', message: 'Hold not found for position');
                DB::rollBack();
                return;
            }

            // Check if the hold is a bulk hold - if so, handle it differently
            $isBulkHold = str_contains($hold['name'], 'Bulk');

            // Update the container's position in the database - for ALL containers, including bulk positions
            $this->flight->containers()->updateExistingPivot($containerId, [
                'position_id' => $positionId,
                'status' => 'loaded'
            ]);

            // Update the container's position in memory
            $this->containers = collect($this->containers)->map(function ($c) use ($containerId, $positionId) {
                if ($c['id'] == $containerId) {
                    $c['position'] = $positionId;
                    $c['position_code'] = $positionId;
                    $c['status'] = 'loaded';
                }
                return $c;
            })->toArray();

            // Update the loadplan to include this container assignment
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

            // Log the assignment
            if ($isBulkHold) {
                \Log::info('Container assigned to bulk position', [
                    'containerId' => $containerId,
                    'positionId' => $positionId,
                    'holdName' => $hold['name']
                ]);
                $message = 'Container assigned to bulk position';
            } else {
                \Log::info('Container assigned to regular position', [
                    'containerId' => $containerId,
                    'positionId' => $positionId,
                    'holdName' => $hold['name']
                ]);
                $message = 'Container assigned to position';
            }

            DB::commit();

            $this->dispatch('container_position_updated');
            $this->dispatch('alert', icon: 'success', message: $message);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('alert', icon: 'error', message: 'Failed to assign container: ' . $e->getMessage());
            \Log::error('Failed to assign container to position: ' . $e->getMessage());
        }
    }

    /**
     * Get a position by its ID
     */
    public function getPositionById($positionId)
    {
        foreach ($this->holds as $hold) {
            foreach ($hold['positions'] as $position) {
                if ($position['id'] == $positionId) {
                    return $position;
                }
            }
        }
        return null;
    }

    /**
     * Get the hold that contains a specific position
     */
    public function getHoldByPositionId($positionId)
    {
        foreach ($this->holds as $hold) {
            foreach ($hold['positions'] as $position) {
                if ($position['id'] == $positionId) {
                    return $hold;
                }
            }
        }
        return null;
    }
}
