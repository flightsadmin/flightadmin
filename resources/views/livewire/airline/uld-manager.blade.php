<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title m-0">ULD Types</h5>
            <button class="btn btn-primary btn-sm" wire:click="createUldType" data-bs-toggle="modal"
                data-bs-target="#uldTypeModal">
                <i class="bi bi-plus-lg"></i> Add ULD Type
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Weights</th>
                            <th>Positions</th>
                            <th>Allowed Holds</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($uldTypes as $key => $value)
                            <tr>
                                <td> <strong>{{ $value['code'] }}</strong> </td>
                                <td>{{ $value['name'] }}</td>
                                <td>
                                    <div class="small">
                                        <div>Max: {{ $value['max_gross_weight'] }}kg</div>
                                        <div class="text-muted">Tare: {{ $value['tare_weight'] }}kg</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="small">
                                        <div>Required: {{ $value['positions_required'] }}</div>
                                        <div class="text-muted">
                                            @if ($value['restrictions']['requires_adjacent_positions'])
                                                <span class="badge bg-info">Adjacent</span>
                                            @endif
                                            @if ($value['restrictions']['requires_vertical_positions'])
                                                <span class="badge bg-info">Vertical</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @foreach ($value['allowed_holds'] as $hold)
                                        <span class="badge bg-secondary">{{ $hold }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    <div class="d-flex justify-content-end gap-1">
                                        <button class="btn btn-sm btn-info"
                                            wire:click="showUldUnits('{{ $key }}')"
                                            title="Manage Units" data-bs-toggle="modal" data-bs-target="#uldUnitsModal">
                                            <i class="bi bi-boxes"></i>
                                        </button>
                                        <button class="btn btn-sm btn-primary"
                                            wire:click="editUldType('{{ $key }}')" data-bs-toggle="modal"
                                            title="Edit ULD Type" data-bs-target="#uldTypeModal">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger"
                                            wire:click="deleteUldType('{{ $key }}')"
                                            wire:confirm="Are you sure you want to delete this ULD type?">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ULD Type Modal -->
    <div class="modal fade" id="uldTypeModal" tabindex="-1" aria-labelledby="uldTypeModalLabel" aria-hidden="true"
        wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form wire:submit.prevent="saveUldType">
                    <div class="modal-header">
                        <h5 class="modal-title" id="uldTypeModalLabel">{{ $editingUldKey ? 'Edit' : 'Create' }} ULD Type</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Code</label>
                                <input type="text" class="form-control form-control-sm"
                                    wire:model="uldForm.code"
                                    {{ $editingUldKey ? 'readonly' : '' }}
                                    maxlength="3">
                                @error('uldForm.code')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control form-control-sm" wire:model="uldForm.name">
                                @error('uldForm.name')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tare Weight (kg)</label>
                                <input type="number" class="form-control form-control-sm"
                                    wire:model="uldForm.tare_weight" step="0.1">
                                @error('uldForm.tare_weight')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Max Gross Weight (kg)</label>
                                <input type="number" class="form-control form-control-sm"
                                    wire:model="uldForm.max_gross_weight" step="0.1">
                                @error('uldForm.max_gross_weight')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Required Positions</label>
                                <input type="number" class="form-control form-control-sm"
                                    wire:model="uldForm.positions_required" min="1" max="2">
                                @error('uldForm.positions_required')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Color</label>
                                <input type="color" class="form-control form-control-color w-100"
                                    wire:model="uldForm.color">
                                @error('uldForm.color')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Icon</label>
                                <select class="form-select form-select-sm" wire:model="uldForm.icon">
                                    <option value="box-seam">Box</option>
                                    <option value="luggage">Luggage</option>
                                    <option value="box-seam-fill">Box (Filled)</option>
                                    <option value="container">Container</option>
                                </select>
                                @error('uldForm.icon')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Allowed Holds</label>
                                <div class="d-flex gap-2">
                                    <div class="form-check">
                                        <input class="form-check-input form-check-input-sm" type="checkbox"
                                            wire:model="uldForm.allowed_holds" value="FWD">
                                        <label class="form-check-label">FWD</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input form-check-input-sm" type="checkbox"
                                            wire:model="uldForm.allowed_holds" value="AFT">
                                        <label class="form-check-label">AFT</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input form-check-input-sm" type="checkbox"
                                            wire:model="uldForm.allowed_holds" value="BULK">
                                        <label class="form-check-label">BULK</label>
                                    </div>
                                </div>
                                @error('uldForm.allowed_holds')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Restrictions</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input form-check-input-sm" type="checkbox"
                                            wire:model="uldForm.restrictions.requires_adjacent_positions">
                                        <label class="form-check-label">Requires Adjacent Positions</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input form-check-input-sm" type="checkbox"
                                            wire:model="uldForm.restrictions.requires_vertical_positions">
                                        <label class="form-check-label">Requires Vertical Positions</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg"></i>
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-save"></i>
                            {{ $editingUldKey ? 'Save Changes' : 'Create ULD Type' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ULD Units Modal -->
    <div class="modal fade" id="uldUnitsModal" tabindex="-1" aria-labelledby="uldUnitsModalLabel" aria-hidden="true"
        wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uldUnitsModalLabel">
                        @if ($selectedUldType)
                            Manage {{ $uldTypes[$selectedUldType]['name'] }} Units
                        @endif
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if ($selectedUldType)
                        <form wire:submit.prevent="createUldUnit" class="mb-4">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Container Number</label>
                                    <input type="text" class="form-control form-control-sm"
                                        wire:model="uldUnitForm.container_number" placeholder="Enter ULD Unit Number (e.g. PMC12345)"
                                        required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Serviceable</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input form-check-input-sm" type="checkbox"
                                            wire:model="uldUnitForm.serviceable" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    @if ($editingUldUnitKey)
                                        <button type="button" class="btn btn-sm btn-secondary" wire:click="resetUldUnitForm">
                                            <i class="bi bi-x-lg"></i>
                                            Cancel
                                        </button>
                                    @endif
                                    <button class="btn btn-sm btn-primary">
                                        <i class="bi bi-plus-lg"></i>
                                        {{ $editingUldUnitKey ? 'Update' : 'Add' }} Unit
                                    </button>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="alert alert-info small">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Tare weight ({{ $uldTypes[$selectedUldType]['tare_weight'] }}kg) and max weight
                                    ({{ $uldTypes[$selectedUldType]['max_gross_weight'] }}kg) will be automatically set from the ULD type.
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Container Number</th>
                                        <th>Weights</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($containers as $container)
                                        <tr>
                                            <td>{{ $container->container_number }}</td>
                                            <td>
                                                <div class="small">
                                                    <div>Max: {{ $container->max_weight }}kg</div>
                                                    <div class="text-muted">Tare: {{ $container->tare_weight }}kg</div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $container->serviceable ? 'success' : 'danger' }}">
                                                    {{ $container->serviceable ? 'Serviceable' : 'Unserviceable' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-end gap-1">
                                                    <button class="btn btn-sm btn-warning"
                                                        wire:click="toggleServiceability('{{ $container->container_number }}')"
                                                        title="{{ $container->serviceable ? 'Mark as unserviceable' : 'Mark as serviceable' }}">
                                                        <i class="bi bi-{{ $container->serviceable ? 'x-circle' : 'check-circle' }}"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-primary"
                                                        wire:click="editUldUnit('{{ $container->container_number }}')"
                                                        title="Edit container">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger"
                                                        wire:click="deleteUldUnit('{{ $container->container_number }}')"
                                                        wire:confirm="Are you sure you want to delete this container?"
                                                        title="Delete container">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center">No containers found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <div class="container-fluid p-0">
                        <div class="row g-2">
                            @if ($selectedUldType && isset($containerStats[$selectedUldType]))
                                <div class="col-md-12">
                                    <div class="d-flex justify-content-between small">
                                        <div>
                                            <span class="badge bg-secondary">Total:
                                                {{ $containerStats[$selectedUldType]['total'] }}</span>
                                        </div>
                                        <div>
                                            <span class="badge bg-success">Serviceable:
                                                {{ $containerStats[$selectedUldType]['serviceable'] }}</span>
                                        </div>
                                        <div>
                                            <span class="badge bg-danger">Unserviceable:
                                                {{ $containerStats[$selectedUldType]['unserviceable'] }}</span>
                                        </div>
                                        <div>
                                            <span class="badge bg-info">Available:
                                                {{ $containerStats[$selectedUldType]['available'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @script
        <script>
            $wire.on('uld-saved', () => {
                bootstrap.Modal.getInstance(document.getElementById('uldTypeModal')).hide();
            });
        </script>
    @endscript
</div>
