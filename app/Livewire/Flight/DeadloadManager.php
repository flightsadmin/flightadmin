<?php

namespace App\Livewire\Flight;

use App\Models\Flight;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;

class DeadloadManager extends Component
{
    public Flight $flight;
    public $deadloadItems = [];
    public $showDeadloadModal = false;
    public $showContainerModal = false;
    public $selectedDeadloadIds = [];
    public $newDeadload = [
        'name' => '',
        'type' => 'cargo',
        'subtype' => 'local',
        'pieces' => 1,
        'weight' => 10
    ];
    public $isEditing = false;
    public $editDeadloadId = null;
    public $editDeadload = [
        'name' => '',
        'type' => '',
        'pieces' => 1,
        'weight' => 10
    ];

    public function mount(Flight $flight)
    {
        $this->flight = $flight;
        $this->loadDeadloadItems();
        $this->selectedDeadloadIds = [];
    }

    #[Computed]
    public function totalDeadloadWeight()
    {
        return collect($this->deadloadItems)->sum('weight');
    }

    #[Computed]
    public function unassignedItems()
    {
        return collect($this->deadloadItems)->filter(function ($item) {
            return empty($item['position']) && empty($item['container_id']);
        })->values()->toArray();
    }

    #[Computed]
    public function assignedItems()
    {
        return collect($this->deadloadItems)->filter(function ($item) {
            return !empty($item['position']) || !empty($item['container_id']);
        })->values()->toArray();
    }

    protected function loadDeadloadItems()
    {
        $deadloadSetting = $this->flight->settings()
            ->where('key', 'manual_deadload')
            ->first();

        if ($deadloadSetting) {
            $this->deadloadItems = json_decode($deadloadSetting->value, true) ?: [];
        } else {
            $this->deadloadItems = [];
        }
    }

    #[On('open-deadload-modal')]
    public function openDeadloadModal()
    {
        $this->isEditing = false;
        $this->editDeadloadId = null;
        $this->newDeadload = [
            'name' => '',
            'type' => 'cargo',
            'subtype' => 'local',
            'pieces' => 1,
            'weight' => 10
        ];
        $this->showDeadloadModal = true;
    }

    #[On('open-deadload-container-modal')]
    public function openContainerModal()
    {
        $this->showContainerModal = true;
    }

    public function resetNewDeadload()
    {
        $this->newDeadload = [
            'id' => uniqid(),
            'type' => 'cargo',
            'subtype' => 'local',
            'pieces' => 1,
            'weight' => 10,
            'position' => null,
            'container_id' => null
        ];
    }

    public function updatedNewDeadloadType()
    {
        if ($this->newDeadload['type'] === 'cargo' || $this->newDeadload['type'] === 'mail') {
            $this->newDeadload['subtype'] = 'local';
        } else if ($this->newDeadload['type'] === 'baggage') {
            $this->newDeadload['subtype'] = 'rush';
        } else {
            $this->newDeadload['subtype'] = '';
        }
    }

    public function addDeadload()
    {
        $this->validate([
            'newDeadload.type' => 'required|in:cargo,baggage,mail,other',
            'newDeadload.subtype' => 'required_unless:newDeadload.type,other',
            'newDeadload.pieces' => 'required|integer|min:1',
            'newDeadload.weight' => 'required|numeric|min:0.1',
        ]);

        $displayName = ucfirst($this->newDeadload['type']);
        if (!empty($this->newDeadload['subtype'])) {
            $displayName .= ' (' . ucfirst($this->newDeadload['subtype']) . ')';
        }

        $this->newDeadload['name'] = $displayName;

        $this->deadloadItems[] = $this->newDeadload;

        $this->saveDeadloadItems();
        $this->resetNewDeadload();
        $this->showDeadloadModal = false;
        $this->dispatch('deadload-updated');
        $this->dispatch('alert', icon: 'success', message: 'Deadload item added successfully');
    }

    public function offloadDeadload($id)
    {
        try {
            DB::beginTransaction();

            // Find the deadload item
            $index = collect($this->deadloadItems)->search(function ($item) use ($id) {
                return $item['id'] == $id;
            });

            if ($index !== false) {
                $item = $this->deadloadItems[$index];

                // If the item was assigned to a container, update the container weight
                if (!empty($item['container_id'])) {
                    $containerId = $item['container_id'];

                    // Log for debugging
                    \Log::info('Offloading deadload from container', [
                        'deadloadId' => $id,
                        'containerId' => $containerId,
                        'deadloadWeight' => $item['weight']
                    ]);

                    // Only update real containers in the database (not virtual ones)
                    if (!str_starts_with($containerId, 'deadload_') && !str_starts_with($containerId, 'bulk_')) {
                        // Get the container from the database
                        $container = $this->flight->containers()->where('container_id', $containerId)->first();

                        if ($container) {
                            // Subtract the deadload weight from the container
                            $this->flight->containers()->updateExistingPivot($containerId, [
                                'weight' => max(0, $container->pivot->weight - $item['weight'])
                            ]);
                        }
                    }

                    // Update the item to remove container assignment
                    $this->deadloadItems[$index]['container_id'] = null;
                    $this->deadloadItems[$index]['position'] = null;

                    // Save the updated deadload items
                    $this->saveDeadloadItems();

                    DB::commit();

                    // Update the UI
                    $this->dispatch('deadload-updated');
                    $this->dispatch('alert', icon: 'success', message: 'Deadload item offloaded');
                } else if (!empty($item['position'])) {
                    // Item is assigned to a position, remove the assignment
                    $this->deadloadItems[$index]['position'] = null;

                    // Save the updated deadload items
                    $this->saveDeadloadItems();

                    DB::commit();

                    // Update the UI
                    $this->dispatch('deadload-updated');
                    $this->dispatch('alert', icon: 'success', message: 'Deadload item offloaded');
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('alert', icon: 'error', message: 'Failed to offload deadload: ' . $e->getMessage());
            \Log::error('Failed to offload deadload: ' . $e->getMessage());
        }
    }

    public function removeDeadload($id)
    {
        try {
            DB::beginTransaction();

            // Find the deadload item
            $index = collect($this->deadloadItems)->search(function ($item) use ($id) {
                return $item['id'] == $id;
            });

            if ($index !== false) {
                $item = $this->deadloadItems[$index];

                // Only allow deletion of unassigned items
                if (!empty($item['container_id']) || !empty($item['position'])) {
                    $this->dispatch('alert', icon: 'error', message: 'Please offload the item before deleting it');
                    DB::rollBack();
                    return;
                }

                // Remove the item from the array
                unset($this->deadloadItems[$index]);
                $this->deadloadItems = array_values($this->deadloadItems);

                // Save the updated deadload items
                $this->saveDeadloadItems();

                DB::commit();

                // Clear any selections and update the UI
                $this->selectedDeadloadIds = [];
                $this->dispatch('deadload-selection-changed', false);
                $this->dispatch('deadload-updated');
                $this->dispatch('alert', icon: 'success', message: 'Deadload item removed');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('alert', icon: 'error', message: 'Failed to remove deadload: ' . $e->getMessage());
            \Log::error('Failed to remove deadload: ' . $e->getMessage());
        }
    }

    public function toggleDeadloadSelection($id)
    {
        if (!is_array($this->selectedDeadloadIds)) {
            $this->selectedDeadloadIds = [];
        }

        if (in_array($id, $this->selectedDeadloadIds)) {
            $this->selectedDeadloadIds = array_diff($this->selectedDeadloadIds, [$id]);
        } else {
            $this->selectedDeadloadIds[] = $id;
        }

        $this->dispatch('deadload-selection-changed', !empty($this->selectedDeadloadIds));
    }

    public function selectAllUnassigned()
    {
        $this->selectedDeadloadIds = collect($this->unassignedItems)
            ->pluck('id')
            ->toArray();

        $this->dispatch('deadload-selection-changed', !empty($this->selectedDeadloadIds));
    }

    public function clearSelection()
    {
        $this->selectedDeadloadIds = [];
        $this->dispatch('deadload-selection-changed', false);
    }

    public function assignToContainer()
    {
        if (empty($this->selectedDeadloadIds)) {
            $this->dispatch('alert', icon: 'error', message: 'No deadload items selected');
            return;
        }

        $this->dispatch('assign-bulk-deadload-to-container', $this->selectedDeadloadIds);
        $this->showContainerModal = false;

        // Clear selection - this will be handled by the cancel-deadload-selection event
        // that is dispatched from the LoadingManager after successful assignment
    }

    #[On('cancel-deadload-selection')]
    public function cancelDeadloadSelection()
    {
        $this->selectedDeadloadIds = [];
        $this->dispatch('deadload-selection-changed', false);

        // Reload the component to ensure UI is updated
        $this->dispatch('refresh-deadload-component');

        // No need to show an alert message when cancellation is triggered programmatically
        // Only show the message when user explicitly cancels
        if (request()->hasHeader('X-Livewire-Is-Navigating-Back')) {
            $this->dispatch('alert', icon: 'info', message: 'Deadload selection cancelled');
        }
    }

    #[On('refresh-deadload-component')]
    public function refreshComponent()
    {
        // Reload deadload items from settings
        $this->loadDeadloadItems();

        // Clear any selections
        $this->selectedDeadloadIds = [];
    }

    #[On('get-selected-deadload-ids')]
    public function provideSelectedDeadloadIds($containerId)
    {
        if (!is_array($this->selectedDeadloadIds)) {
            $this->selectedDeadloadIds = [];
        }

        $this->dispatch('provide-selected-deadload-ids', [
            'ids' => $this->selectedDeadloadIds,
            'containerId' => $containerId
        ]);
    }

    protected function saveDeadloadItems()
    {
        $this->flight->settings()->updateOrCreate(
            ['key' => 'manual_deadload', 'airline_id' => $this->flight->airline_id],
            [
                'value' => json_encode(array_values($this->deadloadItems)),
                'type' => 'json',
                'description' => 'Manual deadload items'
            ]
        );
    }

    public function render()
    {
        if (!is_array($this->selectedDeadloadIds)) {
            $this->selectedDeadloadIds = [];
        }

        return view('livewire.flights.deadload-manager');
    }

    public function hydrate()
    {
        if (!is_array($this->selectedDeadloadIds)) {
            $this->selectedDeadloadIds = [];
        }
    }

    public function editDeadloadItem($id)
    {
        $index = collect($this->deadloadItems)->search(function ($item) use ($id) {
            return $item['id'] == $id;
        });

        if ($index !== false) {
            $item = $this->deadloadItems[$index];
            $this->editDeadloadId = $id;
            $this->newDeadload = [
                'name' => $item['name'],
                'type' => $item['type'],
                'subtype' => $item['subtype'] ?? 'local',
                'pieces' => $item['pieces'],
                'weight' => $item['weight']
            ];
            $this->isEditing = true;
            $this->showDeadloadModal = true;
        }
    }

    public function saveDeadload()
    {
        $this->validate([
            'newDeadload.type' => 'required|string|in:mail,cargo,baggage,company,other',
            'newDeadload.pieces' => 'required|integer|min:1',
            'newDeadload.weight' => 'required|numeric|min:0.1',
        ]);

        try {
            DB::beginTransaction();

            // Generate display name from type and subtype
            $displayName = ucfirst($this->newDeadload['type']);
            if (!empty($this->newDeadload['subtype'])) {
                $displayName .= ' (' . ucfirst($this->newDeadload['subtype']) . ')';
            }
            $this->newDeadload['name'] = $displayName;

            if ($this->isEditing) {
                // Update existing deadload item
                $index = collect($this->deadloadItems)->search(function ($item) {
                    return $item['id'] == $this->editDeadloadId;
                });

                if ($index !== false) {
                    $oldItem = $this->deadloadItems[$index];
                    $weightDifference = $this->newDeadload['weight'] - $oldItem['weight'];

                    // Update the item
                    $this->deadloadItems[$index]['name'] = $this->newDeadload['name'];
                    $this->deadloadItems[$index]['type'] = $this->newDeadload['type'];
                    $this->deadloadItems[$index]['subtype'] = $this->newDeadload['subtype'];
                    $this->deadloadItems[$index]['pieces'] = $this->newDeadload['pieces'];
                    $this->deadloadItems[$index]['weight'] = $this->newDeadload['weight'];

                    // If the item is assigned to a container, update the container weight
                    if (!empty($oldItem['container_id'])) {
                        $containerId = $oldItem['container_id'];

                        // Only update real containers in the database (not virtual ones)
                        if (!str_starts_with($containerId, 'deadload_') && !str_starts_with($containerId, 'bulk_')) {
                            // Get the container from the database
                            $container = $this->flight->containers()->where('container_id', $containerId)->first();

                            if ($container) {
                                // Update the container weight with the difference
                                $this->flight->containers()->updateExistingPivot($containerId, [
                                    'weight' => $container->pivot->weight + $weightDifference
                                ]);

                                // Log the update for debugging
                                \Log::info('Updated container weight after deadload edit', [
                                    'containerId' => $containerId,
                                    'oldWeight' => $container->pivot->weight,
                                    'weightDifference' => $weightDifference,
                                    'newWeight' => $container->pivot->weight + $weightDifference
                                ]);
                            }
                        }
                    }

                    $message = 'Deadload item updated successfully';
                } else {
                    $this->dispatch('alert', icon: 'error', message: 'Deadload item not found');
                    DB::rollBack();
                    return;
                }
            } else {
                // Add new deadload item
                $this->deadloadItems[] = [
                    'id' => uniqid('deadload_'),
                    'name' => $this->newDeadload['name'],
                    'type' => $this->newDeadload['type'],
                    'subtype' => $this->newDeadload['subtype'],
                    'pieces' => $this->newDeadload['pieces'],
                    'weight' => $this->newDeadload['weight'],
                    'container_id' => null,
                    'position' => null,
                    'created_at' => now()->toDateTimeString(),
                ];

                $message = 'Deadload item added successfully';
            }

            // Save the updated deadload items
            $this->saveDeadloadItems();

            DB::commit();

            // Reset the form and close the modal
            $this->showDeadloadModal = false;
            $this->isEditing = false;
            $this->editDeadloadId = null;
            $this->newDeadload = [
                'name' => '',
                'type' => 'cargo',
                'subtype' => 'local',
                'pieces' => 1,
                'weight' => 10
            ];

            // Update the UI
            $this->dispatch('deadload-updated');
            $this->dispatch('alert', icon: 'success', message: $message);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('alert', icon: 'error', message: 'Failed to save deadload item: ' . $e->getMessage());
            \Log::error('Failed to save deadload item: ' . $e->getMessage());
        }
    }

    public function cancelDeadloadModal()
    {
        $this->showDeadloadModal = false;
        $this->isEditing = false;
        $this->editDeadloadId = null;
        $this->newDeadload = [
            'name' => '',
            'type' => 'cargo',
            'subtype' => 'local',
            'pieces' => 1,
            'weight' => 10
        ];
    }
}