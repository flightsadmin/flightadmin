<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <h4 class="card-title m-0">Load Plan</h4>
                <span class="badge {{ $this->totalWeight > 0 ? 'bg-primary' : 'bg-secondary' }}">
                    Total: {{ $this->totalWeight }}kg
                </span>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-success" wire:click="toggleAssignModal">
                    <i class="bi bi-plus-circle"></i> Manage Containers
                </button>
                <button class="btn btn-sm btn-outline-primary" wire:click="toggleWeightSummary">
                    <i class="bi bi-clipboard-data"></i> Weight Summary
                </button>
                <button class="btn btn-sm btn-outline-success"
                    wire:click="finalizeLoadplan"
                    @disabled($loadplan && $loadplan->status !== 'released')>
                    <i class="bi bi-check-circle"></i> Finalize Load Plan
                </button>
                <button class="btn btn-sm btn-outline-primary" wire:click="previewLIRF"
                    @disabled(!isset($loadplan))>
                    <i class="bi bi-eye"></i> Preview LIRF
                </button>
                <button class="btn btn-sm btn-outline-danger" wire:click="resetLoadplan">
                    <i class="bi bi-arrow-counterclockwise"></i> Offload All
                </button>
            </div>
        </div>

        <div class="card-body">
            <div class="container-wrapper">
                <!-- Weight Summary Modal -->
                @if ($showWeightSummary)
                    <div class="modal show d-block" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Weight Summary</h5>
                                    <button type="button" class="btn-close" wire:click="toggleWeightSummary"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Hold</th>
                                                    <th>Current</th>
                                                    <th>Maximum</th>
                                                    <th>Utilization</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($holds as $hold)
                                                    <tr>
                                                        <td>{{ $hold['name'] }}</td>
                                                        <td>{{ $this->getHoldWeight($hold['id']) }}kg</td>
                                                        <td>{{ $hold['max_weight'] }}kg</td>
                                                        <td>
                                                            <div class="progress" style="height: 15px;">
                                                                <div class="progress-bar 
                                                            @if ($this->getHoldUtilization($hold['id']) < 80) bg-success
                                                            @elseif($this->getHoldUtilization($hold['id']) < 99) bg-warning
                                                            @else bg-danger @endif"
                                                                    style="width: {{ $this->getHoldUtilization($hold['id']) }}%">
                                                                    {{ round($this->getHoldUtilization($hold['id'])) }}%
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Holds Layout -->
                <div class="card">
                    <div class="card-body">
                        <div class="holds-wrapper-scroll">
                            <div class="holds-wrapper">
                                @foreach ($holds as $hold)
                                    <div class="hold-container {{ str_contains($hold['name'], 'Bulk') ? 'bulk' : '' }}">
                                        <div class="hold-header">
                                            <div class="d-flex justify-content-between align-items-center px-2">
                                                <span>{{ $hold['name'] }}</span>
                                                <div class="weight-badge {{ $this->isHoldOverweight($hold['id']) ? 'text-danger' : '' }}">
                                                    {{ $this->getHoldWeight($hold['id']) }}/{{ $hold['max_weight'] }}kg
                                                </div>
                                            </div>
                                            <div class="progress mt-1" style="height: 4px;">
                                                <div class="progress-bar 
                                                @if ($this->getHoldUtilization($hold['id']) < 80) bg-success
                                                @elseif($this->getHoldUtilization($hold['id']) < 99) bg-warning
                                                @else bg-danger @endif"
                                                    style="width: {{ $this->getHoldUtilization($hold['id']) }}%">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="cargo-row {{ str_contains($hold['name'], 'Bulk') ? 'bulk' : '' }}">
                                            @if (!str_contains($hold['name'], 'Bulk'))
                                                <!-- Left Side Positions -->
                                                <div class="position-column left">
                                                    @foreach (collect($hold['positions'])->filter(fn($p) => str_ends_with($p['designation'], 'L')) as $position)
                                                        <div class="cargo-slot
                                                    {{ $this->isPositionOccupied($position['id']) ? 'occupied' : '' }}
                                                    {{ $selectedContainer && $this->canDropHere($position['id']) ? 'drop-target' : '' }}
                                                    {{ $this->getContainerInPosition($position['id']) && $this->getContainerInPosition($position['id'])['id'] === $selectedContainer ? 'selected' : '' }}
                                                    {{ $this->getContainerInPosition($position['id'])['type'] ?? '' }}"
                                                            wire:click="handlePositionClick('{{ $position['id'] }}')"
                                                            wire:dblclick="handleDoubleClick('{{ $position['id'] }}')">
                                                            @if ($container = $this->getContainerInPosition($position['id']))
                                                                <div class="container-info">
                                                                    <span class="position-number">{{ $position['designation'] }}</span>
                                                                    <div class="container-id">{{ $container['uld_code'] }}</div>
                                                                    <div class="container-type">
                                                                        {{ $container['pieces'] > 0 ? $container['type'] . ' (' . $container['pieces'] . 'pcs)' : 'Empty' }}
                                                                    </div>
                                                                    <div class="container-weight">
                                                                        <i
                                                                            class="bi {{ $container['type'] === 'baggage' ? 'bi-luggage' : 'bi-box-seam' }}"></i>
                                                                        <span>{{ $container['weight'] }}kg</span>
                                                                    </div>
                                                                </div>
                                                            @else
                                                                <span class="position-code">{{ $position['designation'] }}</span>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>

                                                <!-- Right Side Positions -->
                                                <div class="position-column right">
                                                    @foreach (collect($hold['positions'])->filter(fn($p) => str_ends_with($p['designation'], 'R')) as $position)
                                                        <div class="cargo-slot
                                                    {{ $this->isPositionOccupied($position['id']) ? 'occupied' : '' }}
                                                    {{ $selectedContainer && $this->canDropHere($position['id']) ? 'drop-target' : '' }}
                                                    {{ $this->getContainerInPosition($position['id']) && $this->getContainerInPosition($position['id'])['id'] === $selectedContainer ? 'selected' : '' }}
                                                    {{ $this->getContainerInPosition($position['id'])['type'] ?? '' }}"
                                                            wire:click="handlePositionClick('{{ $position['id'] }}')"
                                                            wire:dblclick="handleDoubleClick('{{ $position['id'] }}')">
                                                            @if ($container = $this->getContainerInPosition($position['id']))
                                                                <div class="container-info">
                                                                    <span class="position-number">{{ $position['designation'] }}</span>
                                                                    <div class="container-id">{{ $container['uld_code'] }}</div>
                                                                    <div class="container-type">
                                                                        {{ $container['pieces'] > 0 ? $container['type'] . ' (' . $container['pieces'] . 'pcs)' : 'Empty' }}
                                                                    </div>
                                                                    <div class="container-weight">
                                                                        <i
                                                                            class="bi {{ $container['type'] === 'baggage' ? 'bi-luggage' : 'bi-box-seam' }}"></i>
                                                                        <span>{{ $container['weight'] }}kg</span>
                                                                    </div>
                                                                </div>
                                                            @else
                                                                <span class="position-code">{{ $position['designation'] }}</span>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <!-- Bulk Positions -->
                                                <div class="position-column center">
                                                    @foreach ($hold['positions'] as $position)
                                                        <div class="cargo-slot
                                                    {{ $this->isPositionOccupied($position['id']) ? 'occupied' : '' }}
                                                    {{ !$selectedContainer && $unplannedType && !$this->isPositionOccupied($position['id']) ? 'drop-target' : '' }}
                                                    {{ $this->getContainerInPosition($position['id']) && $this->getContainerInPosition($position['id'])['id'] === $selectedContainer ? 'selected' : '' }}
                                                    {{ $this->getContainerInPosition($position['id'])['type'] ?? '' }}
                                                    {{ isset($this->getContainerInPosition($position['id'])['is_deadload']) ? 'deadload' : '' }}
                                                    {{ $unplannedType && !$this->isPositionOccupied($position['id']) ? 'hover-pointer' : '' }}"
                                                            @if (!$this->isPositionOccupied($position['id']) && $unplannedType) wire:click="$dispatch('open-pieces-modal', { positionId: '{{ $position['id'] }}', type: '{{ $unplannedType }}' })" @endif>
                                                            @if ($container = $this->getContainerInPosition($position['id']))
                                                                <div class="container-info">
                                                                    <span class="position-number">{{ $position['designation'] }}</span>
                                                                    <div class="container-id">
                                                                        @if (isset($container['is_deadload']) && $container['is_deadload'])
                                                                            DEADLOAD
                                                                        @else
                                                                            BULK
                                                                        @endif
                                                                    </div>
                                                                    <div class="container-type">
                                                                        @if (isset($container['is_deadload']) && $container['is_deadload'])
                                                                            <div class="deadload-items-count">
                                                                                {{ count($container['deadload_items'] ?? []) }} item(s)
                                                                            </div>
                                                                            <div class="deadload-description">
                                                                                {{ $container['deadload_description'] }}</div>
                                                                        @else
                                                                            {{ $container['type'] . ' (' . $container['pieces'] . 'pcs)' }}
                                                                        @endif
                                                                    </div>
                                                                    <div class="container-weight">
                                                                        <i
                                                                            class="bi {{ $container['type'] === 'baggage' ? 'bi-luggage' : 'bi-box-seam' }}"></i>
                                                                        <span>{{ $container['weight'] }}kg</span>
                                                                    </div>
                                                                </div>
                                                            @else
                                                                <span class="position-code">{{ $position['designation'] }}</span>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Unplanned Items -->
                <div class="unplanned-section mt-3">
                    <div class="row g-3">
                        <!-- Available ULDs Column -->
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-header py-2">
                                    <h6 class="card-title m-0">Available ULDs</h6>
                                </div>
                                <div class="card-body p-2">
                                    <div class="row g-2">
                                        @foreach ($this->unplannedContainers as $container)
                                            <div class="col-sm-6 col-md-6 col-lg-4">
                                                <div class="uld-container
                                                {{ $selectedContainer === $container['id'] ? 'selected' : '' }}
                                                {{ $container['type'] === 'baggage' ? 'baggage-container' : 'cargo-container' }}"
                                                    wire:click="selectContainer('{{ $container['id'] }}')">
                                                    <div class="container-info">
                                                        <div class="container-id">{{ $container['uld_code'] }}</div>
                                                        <div class="container-type">
                                                            {{ $container['pieces'] > 0 ? $container['type'] . ' (' . $container['pieces'] . 'pcs)' : 'Empty' }}
                                                        </div>
                                                        <div class="container-weight">
                                                            <i
                                                                class="bi {{ $container['type'] === 'baggage' ? 'bi-luggage' : 'bi-box-seam' }}"></i>
                                                            <span>{{ $container['weight'] }}kg</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                        @if (count($this->unplannedContainers) === 0)
                                            <div class="col-12">
                                                <div class="text-center text-muted">No unplanned containers</div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Unplanned Manager Column -->
                        <div class="col-md-4">
                            <livewire:flight.unplanned-manager :flight="$flight" />
                        </div>

                        <!-- Deadload Manager Column -->
                        <div class="col-md-4">
                            <livewire:flight.deadload-manager :flight="$flight" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Container Assignment Modal -->
    @if ($showAssignModal)
        <div class="modal show d-block" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Manage Containers</h5>
                        <button type="button" class="btn-close" wire:click="toggleAssignModal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Container Type Selection -->
                        <div class="mb-3">
                            <label class="form-label">Container Type</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="containerType" id="typeBaggage" value="baggage"
                                    wire:model.live="selectedType" autocomplete="off" checked>
                                <label class="btn btn-outline-primary" for="typeBaggage">Baggage</label>

                                <input type="radio" class="btn-check" name="containerType" id="typeCargo" value="cargo"
                                    wire:model.live="selectedType" autocomplete="off">
                                <label class="btn btn-outline-primary" for="typeCargo">Cargo</label>
                            </div>
                        </div>

                        <!-- Container Search Section -->
                        <div class="mb-3">
                            <label for="searchQuery" class="form-label">Search Containers</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="searchQuery"
                                    wire:model.live.debounce.300ms="searchQuery"
                                    placeholder="Enter container number...">
                                <button class="btn btn-outline-secondary" type="button" wire:click="$set('searchQuery', '')">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                            <small class="text-muted">Type at least 2 characters to search</small>
                        </div>

                        <!-- Search Results -->
                        @if (count($searchResults) > 0)
                            <div class="search-results mb-3">
                                <!-- Available Containers -->
                                @php
                                    $availableContainers = collect($searchResults)->where('is_attached', false);
                                    $attachedContainers = collect($searchResults)->where('is_attached', true);
                                @endphp

                                @if ($availableContainers->count() > 0)
                                    <h6 class="border-bottom pb-2 mb-2">Available Containers</h6>
                                    <div class="list-group mb-3">
                                        @foreach ($availableContainers as $result)
                                            <div
                                                class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong>{{ $result['container_number'] }}</strong>
                                                    <span class="badge bg-secondary ms-2">{{ $result['uld_type'] }}</span>
                                                    <small class="d-block text-muted">Tare: {{ $result['tare_weight'] }}kg | Max:
                                                        {{ $result['max_weight'] }}kg</small>
                                                </div>
                                                <button class="btn btn-sm btn-primary"
                                                    wire:click="attachContainer({{ $result['id'] }}, '{{ $selectedType }}')">
                                                    <i class="bi bi-plus-lg"></i> Assign
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                <!-- Attached Containers -->
                                @if ($attachedContainers->count() > 0)
                                    <h6 class="border-bottom pb-2 mb-2">Attached Containers</h6>
                                    <div class="list-group">
                                        @foreach ($attachedContainers as $result)
                                            <div
                                                class="list-group-item list-group-item-action d-flex justify-content-between align-items-center bg-light">
                                                <div>
                                                    <strong>{{ $result['container_number'] }}</strong>
                                                    <span class="badge bg-secondary ms-2">{{ $result['uld_type'] }}</span>
                                                    <small class="d-block text-muted">Tare: {{ $result['tare_weight'] }}kg | Max:
                                                        {{ $result['max_weight'] }}kg</small>
                                                </div>
                                                <button class="btn btn-sm btn-danger"
                                                    wire:click="detachContainer({{ $result['id'] }})">
                                                    <i class="bi bi-trash"></i> Remove
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @elseif(strlen($searchQuery) >= 2)
                            <div class="alert alert-info">
                                No matching containers found
                            </div>
                        @endif

                        <!-- Currently Attached Containers Summary -->
                        <div class="mt-4">
                            <h6 class="border-bottom pb-2 mb-2">Flight Containers ({{ count($this->containers) }})</h6>
                            <div class="row g-2">
                                @foreach (collect($this->containers)->take(8) as $container)
                                    <div class="col-md-6">
                                        <div class="card bg-light">
                                            <div class="card-body p-2">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <strong>{{ $container['uld_code'] }}</strong>
                                                        <span
                                                            class="badge {{ $container['type'] === 'baggage' ? 'bg-warning' : 'bg-info' }}">
                                                            {{ ucfirst($container['type']) }}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <span class="badge bg-secondary">{{ $container['weight'] }}kg</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                @if (count($this->containers) > 8)
                                    <div class="col-12 text-center">
                                        <small class="text-muted">And {{ count($this->containers) - 8 }} more containers...</small>
                                    </div>
                                @endif

                                @if (count($this->containers) === 0)
                                    <div class="col-12">
                                        <div class="alert alert-info">
                                            No containers attached to this flight yet.
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- LIRF Preview Modal -->
    <div class="modal fade" id="lirfPreviewModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Loading Instruction Report Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="lirfPrintArea">
                    @if ($showLirfPreview)
                        @include('livewire.flights.loading-instruction')
                    @endif
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-sm btn-primary" onclick="generatePDF()">
                        <i class="bi bi-printer"></i> Print LIRF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('show-lirf-preview', function() {
            var modal = new bootstrap.Modal(document.getElementById('lirfPreviewModal'));
            modal.show();
        });
    </script>
    @script
        <script>
            // Listen for container position updates
            $wire.on('container_position_updated', () => {
                $wire.refreshContainers();
            });

            // Listen for container attachment
            $wire.on('container-attached', () => {
                $wire.refreshContainers();
            });

            // Listen for unplanned items selection
            document.addEventListener('unplanned-items-selected', (event) => {
                $wire.handleUnplannedItemsSelected(event.detail.type);
            });

            // Listen for unplanned items deselection
            document.addEventListener('unplanned-items-deselected', () => {
                $wire.handleUnplannedItemsDeselected();
            });

            // Listen for open-pieces-modal event from bulk positions
            $wire.on('open-pieces-modal', (data) => {
                // Forward the event to the UnplannedManager component
                Livewire.dispatch('open-pieces-modal', data);
            });
        </script>
    @endscript

    <style>
        .modal.show {
            display: block;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-backdrop.show {
            opacity: 0;
        }

        /* Container Wrapper Styles */
        .container-wrapper {
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        .holds-wrapper-scroll {
            width: 100%;
            overflow-x: auto;
            padding: 10px 0;
            scroll-behavior: smooth;
            scrollbar-width: thin;
            scrollbar-color: #dee2e6 #f8f9fa;
            display: flex;
            align-items: flex-start;
        }

        .holds-wrapper-scroll::-webkit-scrollbar {
            height: 8px;
            background: #f8f9fa;
            border-radius: 4px;
        }

        .holds-wrapper-scroll::-webkit-scrollbar-thumb {
            background: #dee2e6;
            border-radius: 4px;
        }

        .holds-wrapper-scroll::-webkit-scrollbar-thumb:hover {
            background: #adb5bd;
        }

        .holds-wrapper {
            display: flex;
            gap: 10px;
            padding: 0 10px;
            width: max-content;
            align-items: flex-start;
        }

        /* Hold Container Styles */
        .hold-container {
            border: 1px solid #0d6efd;
            border-radius: 4px;
            padding: 6px;
            background: #fff;
            min-width: 300px;
            width: fit-content;
            align-items: center;
        }

        .hold-container.bulk {
            min-width: 180px;
            width: fit-content;
        }

        .hold-header {
            padding: 3px;
            margin: -6px -6px 6px;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            font-size: 0.8rem;
        }

        .weight-badge {
            font-size: 0.75rem;
            font-weight: 500;
        }

        /* Cargo Row Styles */
        .cargo-row {
            display: flex;
            flex-direction: column;
            gap: 4px;
            height: 140px;
            align-items: center;
            justify-content: center;
        }

        .position-column {
            display: flex;
            gap: 4px;
            width: 100%;
            justify-content: center;
            height: 65px;
            padding: 0 4px;
        }

        .position-column.left {
            order: 2;
            justify-content: flex-start;
            align-self: flex-end;
        }

        .position-column.right {
            order: 1;
            justify-content: flex-start;
            align-self: flex-start;
        }

        .position-column.center {
            order: 1;
            flex-wrap: nowrap;
            height: 140px;
            justify-content: center;
            align-items: center;
            gap: 4px;
            overflow-x: auto;
            scrollbar-width: thin;
            scrollbar-color: #dee2e6 #f8f9fa;
            padding: 0 10px;
        }

        .position-column.center::-webkit-scrollbar {
            height: 6px;
            background: #f8f9fa;
            border-radius: 4px;
        }

        .position-column.center::-webkit-scrollbar-thumb {
            background: #dee2e6;
            border-radius: 4px;
        }

        .position-column.center::-webkit-scrollbar-thumb:hover {
            background: #adb5bd;
        }

        /* Cargo Slot Styles */
        .cargo-slot {
            border: 1px solid #dee2e6;
            border-radius: 3px;
            padding: 3px;
            background: #e7f1ff;
            height: 65px;
            width: 85px;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .cargo-slot.occupied {
            background-color: #fff3cd;
        }

        .cargo-slot.selected {
            border-color: #0d6efd;
            background-color: #e7f1ff;
        }

        .cargo-slot.occupied.cargo {
            background-color: #abb2f0;
            border-color: #0c22ee;
        }

        .cargo-slot.occupied.baggage {
            background-color: #ffd7b5;
            border-color: #0c22ee;
        }

        .cargo-slot.drop-target {
            border: 2px dashed #198754;
            background-color: #d1e7dd;
        }

        .cargo-slot.hover-pointer:hover {
            background-color: #e9ecef;
        }

        /* Container Info Styles */
        .container-info {
            width: 100%;
            text-align: center;
            line-height: 1;
            padding: 2px;
            padding-top: 12px;
            position: relative;
        }

        .position-number {
            position: absolute;
            top: -4px;
            right: -2px;
            font-size: 0.6rem;
            padding: 1px;
            color: #6c757d;
            font-weight: lighter;
            line-height: 1;
            z-index: 1;
        }

        .container-id {
            font-size: 0.65rem;
            font-weight: lighter;
        }

        .container-type,
        .container-weight {
            font-size: 0.55rem;
        }

        .position-code {
            font-size: 0.5rem;
        }

        /* ULD Container Styles */
        .uld-container {
            border: 1px solid #0d6efd;
            border-radius: 3px;
            padding: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            background: white;
            height: 100%;
        }

        .uld-container:hover {
            transform: translateY(-2px);
            background: #a4c0dd;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .uld-container.selected {
            background-color: #93bdf8;
            border-color: #0d6efd;
        }

        .uld-container.baggage-container {
            background-color: #ffd7b5;
            border-color: #198754 !important;
        }

        .uld-container.cargo-container {
            background-color: #abb2f0;
            border-color: #0c22ee !important;
        }

        /* Bulk Position Styles */
        .cargo-row.bulk {
            height: 140px;
            justify-content: center;
        }

        .cargo-row.bulk .position-column {
            height: 100px;
        }

        .cargo-row.bulk .cargo-slot {
            width: 90px;
            height: 80px;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .hold-container {
                min-width: 180px;
            }

            .cargo-slot {
                min-height: 60px;
            }

            .container-id {
                font-size: 0.8rem;
            }

            .container-type,
            .container-weight {
                font-size: 0.7rem;
            }
        }

        /* Deadload Styles */
        .cargo-slot.occupied.deadload {
            background-color: #ffc107;
            border-color: #ff9800;
        }

        .deadload-item {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 3px;
            padding: 4px 6px;
            margin-bottom: 4px;
            font-size: 0.7rem;
        }

        .deadload-item .item-name {
            font-weight: 500;
        }

        .deadload-item .item-weight {
            font-size: 0.65rem;
            color: #6c757d;
        }

        /* Multiple Deadload Items Styles */
        .deadload-items-count {
            font-size: 0.6rem;
            font-weight: bold;
            color: #d63384;
        }

        .deadload-description {
            font-size: 0.55rem;
            max-height: 2.2em;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .cargo-slot.occupied.deadload:hover .container-info {
            position: relative;
        }

        .cargo-slot.occupied.deadload:hover .deadload-description {
            position: absolute;
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 3px;
            padding: 4px;
            z-index: 10;
            max-height: none;
            -webkit-line-clamp: unset;
            width: 150px;
            left: 50%;
            transform: translateX(-50%);
            top: 100%;
            text-align: left;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
    </style>
</div>
