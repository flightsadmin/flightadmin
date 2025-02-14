<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Holds</h3>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#holdFormModal">
            <i class="bi bi-plus-lg"></i> Add Hold
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Position</th>
                        <th>Max Weight</th>
                        <th>Index</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($holds as $hold)
                        <tr>
                            <td>{{ $hold->name }}</td>
                            <td>{{ $hold->code }}</td>
                            <td>{{ $hold->position }}</td>
                            <td>{{ number_format($hold->max_weight) }} kg</td>
                            <td>{{ number_format($hold->index, 4) }}</td>
                            <td>
                                <span class="badge bg-{{ $hold->is_active ? 'success' : 'danger' }}">
                                    {{ $hold->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#holdFormModal" wire:click="editHold({{ $hold->id }})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-danger"
                                    wire:click="deleteHold({{ $hold->id }})"
                                    wire:confirm="Are you sure you want to delete this hold?">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No holds defined</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Hold Modal -->
    <div class="modal fade" tabindex="-1" id="holdFormModal" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $editingHold ? 'Edit' : 'Add' }} Hold</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="saveHold">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Hold Code</label>
                                    <select class="form-select form-select-sm" wire:model.live="holdForm.code" required>
                                        <option value="">Select Code</option>
                                        <option value="FH">Forward Hold (FH)</option>
                                        <option value="AH">Aft Hold (AH)</option>
                                        <option value="BH">Bulk Hold (BH)</option>
                                    </select>
                                    @error('holdForm.code')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Hold Name</label>
                                    <input type="text" class="form-control form-control-sm"
                                        wire:model="holdForm.name" required>
                                    @error('holdForm.name')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Position</label>
                                    <input type="number" class="form-control form-control-sm"
                                        wire:model="holdForm.position" required>
                                    @error('holdForm.position')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Max Weight (kg)</label>
                                    <input type="number" class="form-control form-control-sm"
                                        wire:model="holdForm.max_weight" required>
                                    @error('holdForm.max_weight')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Index</label>
                                    <input type="number" step="0.0001" class="form-control form-control-sm"
                                        wire:model="holdForm.index" required>
                                    @error('holdForm.index')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox"
                                    wire:model="holdForm.is_active">
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="card-title m-0">Hold Positions</h6>
                                <button type="button" class="btn btn-sm btn-primary" wire:click="addHoldPosition">
                                    <i class="bi bi-plus-lg"></i> Add Position
                                </button>
                            </div>
                            <div class="card-body p-2">
                                @if (empty($holdForm['positions']))
                                    <p class="text-center text-muted small mb-0">
                                        No positions defined. Click "Add Position" to create one.
                                    </p>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Row</th>
                                                    <th>Side</th>
                                                    <th>Max Weight</th>
                                                    <th>Index</th>
                                                    <th>Active</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($holdForm['positions'] as $index => $position)
                                                    <tr>
                                                        <td>
                                                            <input type="number" class="form-control form-control-sm"
                                                                wire:model="holdForm.positions.{{ $index }}.row">
                                                            @error("holdForm.positions.{$index}.row")
                                                                <div class="text-danger small">{{ $message }}</div>
                                                            @enderror
                                                        </td>
                                                        <td>
                                                            @if ($holdForm['code'] !== 'BH')
                                                                <select class="form-select form-select-sm"
                                                                    wire:model="holdForm.positions.{{ $index }}.side">
                                                                    <option value="L">Left</option>
                                                                    <option value="R">Right</option>
                                                                </select>
                                                            @endif
                                                            @error("holdForm.positions.{$index}.side")
                                                                <div class="text-danger small">{{ $message }}</div>
                                                            @enderror
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control form-control-sm"
                                                                wire:model="holdForm.positions.{{ $index }}.max_weight">
                                                            @error("holdForm.positions.{$index}.max_weight")
                                                                <div class="text-danger small">{{ $message }}</div>
                                                            @enderror
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.0001" class="form-control form-control-sm"
                                                                wire:model="holdForm.positions.{{ $index }}.index">
                                                            @error("holdForm.positions.{$index}.index")
                                                                <div class="text-danger small">{{ $message }}</div>
                                                            @enderror
                                                        </td>
                                                        <td>
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input" type="checkbox"
                                                                    wire:model="holdForm.positions.{{ $index }}.is_active">
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <button type="button" class="btn btn-sm btn-danger"
                                                                wire:click="removeHoldPosition({{ $index }})">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-secondary"
                                data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-primary">
                                Save Hold
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@script
    <script>
        $wire.on('hold-saved', () => {
            const modal = bootstrap.Modal.getInstance(document.getElementById('holdFormModal'));
            modal.hide();
        });
    </script>
@endscript
