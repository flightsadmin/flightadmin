<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Aircraft</h3>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#aircraftModal">
            <i class="bi bi-plus-lg"></i> Add Aircraft
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th>Registration</th>
                        <th>Basic Weight</th>
                        <th>Basic Index</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($aircraft as $plane)
                        <tr>
                            <td>{{ $plane->registration_number }}</td>
                            <td>{{ number_format($plane->basic_weight) }} kg</td>
                            <td>{{ number_format($plane->basic_index, 4) }}</td>
                            <td>
                                <span class="badge bg-{{ $plane->active ? 'success' : 'danger' }}">
                                    {{ $plane->active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary" 
                                    wire:click="editAircraft({{ $plane->id }})"
                                    data-bs-toggle="modal" data-bs-target="#aircraftModal">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-danger"
                                    wire:click="deleteAircraft({{ $plane->id }})"
                                    wire:confirm="Are you sure you want to delete this aircraft?">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No aircraft found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $aircraft->links() }}
        </div>
    </div>

    <!-- Aircraft Modal -->
    <div class="modal fade" tabindex="-1" id="aircraftModal" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $editingAircraft ? 'Edit' : 'Add' }} Aircraft</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="saveAircraft">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Registration Number</label>
                                    <input type="text" class="form-control form-control-sm" 
                                        wire:model="aircraftForm.registration_number" required>
                                    @error('aircraftForm.registration_number')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Basic Weight</label>
                                    <input type="number" class="form-control form-control-sm"
                                        wire:model="aircraftForm.basic_weight" required>
                                    @error('aircraftForm.basic_weight')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Basic Index</label>
                                    <input type="number" step="0.0001" class="form-control form-control-sm"
                                        wire:model="aircraftForm.basic_index" required>
                                    @error('aircraftForm.basic_index')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox"
                                            wire:model="aircraftForm.active">
                                        <label class="form-check-label">Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea class="form-control form-control-sm"
                                wire:model="aircraftForm.remarks" rows="3"></textarea>
                            @error('aircraftForm.remarks')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-secondary" 
                                data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-primary">
                                Save Aircraft
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
    $wire.on('aircraft-saved', () => {
        const modal = bootstrap.Modal.getInstance(document.getElementById('aircraftModal'));
        modal.hide();
    });
</script>
@endscript 