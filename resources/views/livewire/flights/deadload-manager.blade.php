<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0">Deadload ({{ count($this->deadloadItems) }} items, {{ $this->totalDeadloadWeight }}kg)</h6>
    </div>

    <!-- Selection controls when items are selected -->
    @if (is_array($this->selectedDeadloadIds) && count($this->selectedDeadloadIds) > 0)
        <div class="d-flex justify-content-between mb-2">
            <span class="badge bg-warning text-dark">
                {{ count($this->selectedDeadloadIds) }} item(s) selected
            </span>
            <button class="btn btn-sm btn-outline-secondary" wire:click="cancelDeadloadSelection">
                <i class="bi bi-x-circle"></i> Cancel Selection
            </button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <!-- Unassigned Deadload Items -->
            <div class="card mb-3">
                <div class="card-header py-2 d-flex justify-content-between align-items-center">
                    <h6 class="card-title m-0">Unassigned Deadload</h6>
                    <div>
                        <button class="btn btn-sm btn-outline-primary" wire:click="selectAllUnassigned"
                            @if (count($this->unassignedItems) === 0) disabled @endif>
                            <i class="bi bi-check-all"></i> Select All
                        </button>
                    </div>
                </div>
                <div class="card-body p-2">
                    @if (count($this->unassignedItems) > 0)
                        <div class="deadload-items-list">
                            @foreach ($this->unassignedItems as $item)
                                <div
                                    class="deadload-item d-flex align-items-center mb-2 
                            {{ is_array($this->selectedDeadloadIds) && in_array($item['id'], $this->selectedDeadloadIds) ? 'selected' : '' }}">
                                    <div class="form-check me-2">
                                        <input class="form-check-input" type="checkbox"
                                            wire:click="toggleDeadloadSelection('{{ $item['id'] }}')"
                                            {{ is_array($this->selectedDeadloadIds) && in_array($item['id'], $this->selectedDeadloadIds) ? 'checked' : '' }}>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between">
                                            <span class="item-name">{{ $item['name'] }}</span>
                                            <span class="item-weight">{{ $item['pieces'] }} pcs, {{ $item['weight'] }}kg</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <small class="text-muted">{{ ucfirst($item['type']) }}
                                                {{ !empty($item['subtype']) ? '(' . ucfirst($item['subtype']) . ')' : '' }}</small>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-link text-primary p-0 me-2"
                                                    wire:click="editDeadloadItem('{{ $item['id'] }}')">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-sm btn-link text-danger p-0"
                                                    wire:click="removeDeadload('{{ $item['id'] }}')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-3">
                            No unassigned deadload items
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <!-- Assigned Deadload Items -->
            <div class="card">
                <div class="card-header py-2">
                    <h6 class="card-title m-0">Assigned Deadload Items</h6>
                </div>
                <div class="card-body p-2">
                    @if (count($this->assignedItems) > 0)
                        <div class="deadload-items-list">
                            @foreach ($this->assignedItems as $item)
                                <div class="deadload-item d-flex align-items-center mb-2">
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between">
                                            <span class="item-name">{{ $item['name'] }}</span>
                                            <span class="item-weight">{{ $item['pieces'] }} pcs, {{ $item['weight'] }}kg</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <small class="text-muted">
                                                @if (!empty($item['position']))
                                                    <span class="badge bg-info">Position: {{ $item['position'] }}</span>
                                                @elseif (!empty($item['container_id']))
                                                    <span class="badge bg-success">Container:
                                                        {{ substr($item['container_id'], 0, 8) }}</span>
                                                @endif
                                            </small>
                                            <button class="btn btn-sm btn-link text-decoration-none text-danger p-0"
                                                wire:click="offloadDeadload('{{ $item['id'] }}')">
                                                <i class="bi bi-box-arrow-up"></i> Offload
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-3">
                            No assigned deadload items
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Combined Add/Edit Deadload Modal -->
    @if ($showDeadloadModal)
        <div class="modal show d-block" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $isEditing ? 'Edit' : 'Add' }} Deadload Item</h5>
                        <button type="button" class="btn-close" wire:click="cancelDeadloadModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveDeadload">
                            <!-- Type Selection -->
                            <div class="mb-3">
                                <label class="form-label">Item Type</label>
                                <div class="btn-group btn-group-sm w-100" role="group">
                                    <input type="radio" class="btn-check" name="itemType" id="typeBaggage" value="baggage"
                                        wire:model.live="newDeadload.type" autocomplete="off">
                                    <label class="btn btn-outline-primary" for="typeBaggage">Baggage</label>

                                    <input type="radio" class="btn-check" name="itemType" id="typeCargo" value="cargo"
                                        wire:model.live="newDeadload.type" autocomplete="off">
                                    <label class="btn btn-outline-primary" for="typeCargo">Cargo</label>

                                    <input type="radio" class="btn-check" name="itemType" id="typeMail" value="mail"
                                        wire:model.live="newDeadload.type" autocomplete="off">
                                    <label class="btn btn-outline-primary" for="typeMail">Mail</label>

                                    <input type="radio" class="btn-check" name="itemType" id="typeOther" value="other"
                                        wire:model.live="newDeadload.type" autocomplete="off">
                                    <label class="btn btn-outline-primary" for="typeOther">Other</label>
                                </div>
                                @error('newDeadload.type')
                                    <div class="text-danger mt-1">{{ $errors->first('newDeadload.type') }}</div>
                                @enderror
                            </div>

                            <!-- Subtype Selection for Cargo/Mail -->
                            @if ($newDeadload['type'] === 'cargo' || $newDeadload['type'] === 'mail')
                                <div class="mb-3">
                                    <label class="form-label">Category</label>
                                    <div class="btn-group btn-group-sm w-100" role="group">
                                        <input type="radio" class="btn-check" name="subType" id="subtypeLocal" value="local"
                                            wire:model.live="newDeadload.subtype" autocomplete="off">
                                        <label class="btn btn-outline-secondary" for="subtypeLocal">Local</label>

                                        <input type="radio" class="btn-check" name="subType" id="subtypeTransfer" value="transfer"
                                            wire:model.live="newDeadload.subtype" autocomplete="off">
                                        <label class="btn btn-outline-secondary" for="subtypeTransfer">Transfer</label>
                                    </div>
                                    @error('newDeadload.subtype')
                                        <div class="text-danger mt-1">{{ $errors->first('newDeadload.subtype') }}</div>
                                    @enderror
                                </div>
                            @endif

                            <!-- Subtype Selection for Baggage -->
                            @if ($newDeadload['type'] === 'baggage')
                                <div class="mb-3">
                                    <label class="form-label">Category</label>
                                    <div class="btn-group btn-group-sm w-100" role="group">
                                        <input type="radio" class="btn-check" name="subType" id="subtypeRush" value="rush"
                                            wire:model.live="newDeadload.subtype" autocomplete="off">
                                        <label class="btn btn-outline-secondary" for="subtypeRush">Rush</label>

                                        <input type="radio" class="btn-check" name="subType" id="subtypeBT" value="bt"
                                            wire:model.live="newDeadload.subtype" autocomplete="off">
                                        <label class="btn btn-outline-secondary" for="subtypeBT">BT</label>

                                        <input type="radio" class="btn-check" name="subType" id="subtypeBY" value="by"
                                            wire:model.live="newDeadload.subtype" autocomplete="off">
                                        <label class="btn btn-outline-secondary" for="subtypeBY">BY</label>
                                    </div>
                                    @error('newDeadload.subtype')
                                        <div class="text-danger mt-1">{{ $errors->first('newDeadload.subtype') }}</div>
                                    @enderror
                                </div>
                            @endif

                            <!-- Pieces -->
                            <div class="mb-3">
                                <label for="pieces" class="form-label">Number of Pieces</label>
                                <input type="number" class="form-control" id="pieces" wire:model="newDeadload.pieces"
                                    min="1">
                                @error('newDeadload.pieces')
                                    <div class="text-danger mt-1">{{ $errors->first('newDeadload.pieces') }}</div>
                                @enderror
                            </div>

                            <!-- Total Weight -->
                            <div class="mb-3">
                                <label for="weight" class="form-label">Total Weight (kg)</label>
                                <input type="number" class="form-control" id="weight" wire:model="newDeadload.weight"
                                    step="0.1" min="0.1">
                                @error('newDeadload.weight')
                                    <div class="text-danger mt-1">{{ $errors->first('newDeadload.weight') }}</div>
                                @enderror
                            </div>

                            <!-- Preview of generated name -->
                            <div class="mb-3">
                                <label class="form-label">Item Name (Auto-generated)</label>
                                <div class="form-control bg-light">
                                    {{ ucfirst($newDeadload['type']) }}{{ !empty($newDeadload['subtype']) ? ' (' . ucfirst($newDeadload['subtype']) . ')' : '' }}
                                </div>
                                <small class="text-muted">This name will be automatically generated from the type and category.</small>
                            </div>

                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-sm btn-secondary"
                                    wire:click="cancelDeadloadModal">Cancel</button>
                                <button type="submit" class="btn btn-sm btn-primary">
                                    {{ $isEditing ? 'Save Changes' : 'Add Deadload' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop show"></div>
    @endif

    <!-- Assign to Container Modal -->
    @if ($showContainerModal)
        <div class="modal show d-block" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Assign to Container</h5>
                        <button type="button" class="btn-close" wire:click="$set('showContainerModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <p>You are about to assign {{ count($this->selectedDeadloadIds) }} deadload item(s) to the selected container.</p>
                        <p>Please make sure you have selected a container in the Available ULDs section.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary"
                            wire:click="$set('showContainerModal', false)">Cancel</button>
                        <button type="button" class="btn btn-sm btn-primary" wire:click="assignToContainer"
                            @if (!is_array($this->selectedDeadloadIds) || count($this->selectedDeadloadIds) === 0) disabled @endif>
                            Assign to Container
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
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

        .deadload-item.selected {
            background-color: #d1e7dd;
            border-color: #198754;
        }

        .modal.show {
            display: block;
            background-color: rgba(0, 0, 0, 0.5);
        }

        /* Style for edit buttons */
        .btn-group .btn-link {
            padding: 0;
            font-size: 0.75rem;
        }

        .btn-link.text-primary:hover {
            color: #0a58ca !important;
        }

        .btn-link.text-danger:hover {
            color: #b02a37 !important;
        }
    </style>
</div>

@script
    <script>
        $wire.on('refresh-deadload-component', () => {
            // Force a refresh of the component
            $wire.call('refreshComponent');
        });
    </script>
@endscript
