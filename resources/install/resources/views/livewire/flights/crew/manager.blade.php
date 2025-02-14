<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title m-0">Flight Crew</h5>
            <div class="d-flex gap-2">
                <div>
                    <input type="text" class="form-control form-control-sm" placeholder="Search crew..."
                        wire:model.live="search">
                </div>
                <div>
                    <select class="form-select form-select-sm" wire:model.live="position">
                        <option value="">All Positions</option>
                        <option value="captain">Captain</option>
                        <option value="first_officer">First Officer</option>
                        <option value="purser">Purser</option>
                        <option value="cabin_crew">Cabin Crew</option>
                    </select>
                </div>
                <button wire:click="showAvailableCrew" class="btn btn-success btn-sm me-2" data-bs-toggle="modal"
                    data-bs-target="#availableCrewModal">
                    <i class="bi bi-plus-circle"></i> Assign Crew
                </button>
                <button wire:click="createCrew" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                    data-bs-target="#crewFormModal">
                    <i class="bi bi-plus-circle"></i> Add New Crew
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Position</th>
                                            <th>Employee ID</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($assignedCrew as $crew)
                                            <tr>
                                                <td>{{ $crew->name }}</td>
                                                <td>
                                                    <span
                                                        class="btn btn-sm btn-{{ match ($crew->position) {
                                                            'captain' => 'primary',
                                                            'first_officer' => 'info',
                                                            'purser' => 'warning',
                                                            default => 'success',
                                                        } }}">
                                                        {{ ucwords(str_replace('_', ' ', $crew->position)) }}
                                                    </span>
                                                </td>
                                                <td>{{ $crew->employee_id }}</td>
                                                <td>
                                                    <button class="btn btn-sm btn-link"
                                                        wire:click="editCrew({{ $crew->id }})"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#crewFormModal">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-link text-danger"
                                                        wire:click="removeCrew({{ $crew->id }})"
                                                        wire:confirm="Are you sure you want to remove this crew member?">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center">No crew assigned</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Available Crew Modal -->
    <div class="modal fade" id="availableCrewModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between align-items-center">
                    <h5 class="modal-title me-4">Available Crew</h5>
                    <div class="d-flex gap-2">
                        <div class="col-md-auto">
                            <input type="text" class="form-control form-control-sm" placeholder="Search crew..."
                                wire:model.live="search">
                        </div>
                        <div class="col-md-auto">
                            <select class="form-select form-select-sm" wire:model.live="position">
                                <option value="">All Positions</option>
                                <option value="captain">Captain</option>
                                <option value="first_officer">First Officer</option>
                                <option value="purser">Purser</option>
                                <option value="cabin_crew">Cabin Crew</option>
                            </select>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Position</th>
                                    <th>Employee ID</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($availableCrew as $crew)
                                    <tr>
                                        <td>{{ $crew->name }}</td>
                                        <td>{{ ucwords(str_replace('_', ' ', $crew->position)) }}</td>
                                        <td>{{ $crew->employee_id }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-success"
                                                wire:click="assignCrew({{ $crew->id }})">
                                                <i class="bi bi-plus-circle"></i> Assign
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No crew available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Crew Form Modal -->
    <div class="modal fade" id="crewFormModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form wire:submit="saveCrew">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editingCrew ? 'Edit' : 'Add' }} Crew Member</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Name</label>
                                    <input type="text" class="form-control form-control-sm" wire:model="form.name">
                                    @error('form.name')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Position</label>
                                    <select class="form-select form-select-sm" wire:model="form.position">
                                        <option value="">Select Position</option>
                                        <option value="captain">Captain</option>
                                        <option value="first_officer">First Officer</option>
                                        <option value="purser">Purser</option>
                                        <option value="cabin_crew">Cabin Crew</option>
                                    </select>
                                    @error('form.position')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Employee ID</label>
                                    <input type="text" class="form-control form-control-sm"
                                        wire:model="form.employee_id">
                                    @error('form.employee_id')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Notes</label>
                                    <textarea class="form-control form-control-sm" wire:model="form.notes"
                                        rows="3"></textarea>
                                    @error('form.notes')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-save"></i> Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @script
        <script>
            $wire.on('crew-modal-saved', () => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('crewFormModal'));
                modal.hide();
            });
        </script>
    @endscript
</div>
