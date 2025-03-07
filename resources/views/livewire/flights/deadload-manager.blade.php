<div class="card h-100">
    <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <h6 class="card-title m-0">Deadload Items</h6>
        <div class="btn-group">
            <button class="btn btn-sm btn-primary" wire:click="openModal">
                <i class="bi bi-plus-circle"></i> Add Item
            </button>
            <button class="btn btn-sm btn-success" wire:click="saveDeadloadItems"
                @if (!$unsavedChanges) disabled @endif>
                <i class="bi bi-save"></i> Save Changes
            </button>
        </div>
    </div>
    <div class="card-body p-2">
        @if (count($deadloadItems) > 0)
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>Pieces</th>
                            <th>Type</th>
                            <th>Weight</th>
                            <th>Total</th>
                            <th>Position</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($deadloadItems as $index => $item)
                            <tr>
                                <td>{{ $item['pieces'] }}</td>
                                <td>
                                    <span class="badge {{ $item['type'] === 'baggage' ? 'bg-primary' : 'bg-warning' }}">
                                        {{ ucfirst($item['type']) }}
                                    </span>
                                </td>
                                <td>{{ $item['weight'] }} kg</td>
                                <td>{{ $item['weight'] * $item['pieces'] }} kg</td>
                                <td>
                                    @if ($item['position'])
                                        @php
                                            $position = collect($positions)->firstWhere('id', $item['position']);
                                        @endphp
                                        {{ $position ? $position['code'] : 'Unknown' }}
                                        @php
                                            $samePositionCount = collect($deadloadItems)
                                                ->filter(function ($i) use ($item) {
                                                    return $i['position'] == $item['position'] && !empty($item['position']);
                                                })
                                                ->count();
                                        @endphp
                                        @if ($samePositionCount > 1)
                                            <span class="badge bg-info">+{{ $samePositionCount - 1 }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted">Not assigned</span>
                                    @endif
                                </td>
                                <td>
                                    <div>
                                        <button class="btn btn-sm btn-link" wire:click="openModal({{ $index }})">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-link" wire:click="removeDeadloadItem({{ $index }})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($unsavedChanges)
                <div class="alert alert-warning mt-2 mb-0 py-2">
                    <small><i class="bi bi-exclamation-triangle"></i> You have unsaved changes. Click "Save Changes" to apply them.</small>
                </div>
            @endif
        @else
            <div class="alert alert-info">
                No deadload items added. Click "Add Item" to add deadload for weight and balance calculations.
            </div>
        @endif
    </div>

    <!-- Deadload Modal -->
    @if ($showModal)
        <div class="modal show d-block" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editingIndex !== null ? 'Edit' : 'Add' }} Deadload Item</h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Number of Pieces</label>
                            <input type="number" class="form-control" wire:model.live="newItem.pieces" min="1" step="1">
                            @error('newItem.pieces')
                                <div class="text-danger">{{ $message ?? 'Invalid input' }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Weight per Piece (kg)</label>
                            <input type="number" class="form-control" wire:model.live="newItem.weight" step="0.1" min="0.1">
                            @error('newItem.weight')
                                <div class="text-danger">{{ $message ?? 'Invalid input' }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="itemType" id="typeCargo" value="cargo"
                                    wire:model.live="newItem.type">
                                <label class="btn btn-outline-warning" for="typeCargo">
                                    <i class="bi bi-box-seam"></i> Cargo
                                </label>
                                <input type="radio" class="btn-check" name="itemType" id="typeBaggage" value="baggage"
                                    wire:model.live="newItem.type">
                                <label class="btn btn-outline-primary" for="typeBaggage">
                                    <i class="bi bi-luggage"></i> Baggage
                                </label>
                            </div>
                            @error('newItem.type')
                                <div class="text-danger">{{ $message ?? 'Invalid input' }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Position (Optional)</label>
                            <select class="form-select" wire:model.live="newItem.position">
                                <option value="">Not assigned</option>
                                @foreach (collect($positions)->groupBy('hold_name') as $holdName => $holdPositions)
                                    <optgroup label="{{ $holdName }}">
                                        @foreach ($holdPositions as $position)
                                            <option value="{{ $position['id'] }}">{{ $position['code'] }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            <small class="text-muted">Multiple deadload items can be placed in the same position.</small>
                            @error('newItem.position')
                                <div class="text-danger">{{ $message ?? 'Invalid input' }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-info">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-info-circle me-2"></i>
                                <div>
                                    <strong>Total Weight: {{ $newItem['pieces'] * $newItem['weight'] }} kg</strong>
                                    <div><small>{{ $newItem['pieces'] }} pieces × {{ $newItem['weight'] }} kg each</small></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="addDeadloadItem">
                            {{ $editingIndex !== null ? 'Update' : 'Add' }} Item
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop show"></div>
    @endif
</div>
