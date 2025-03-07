<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="card-title m-0">Airlines</h2>
            <div class="d-flex justify-content-between align-items-center gap-2">
                <div>
                    <input type="text" wire:model.live.debounce.300ms="search"
                        class="form-control form-control-sm" placeholder="Search airlines...">
                </div>
                <div>
                    <button class="btn btn-primary btn-sm" wire:click="$toggle('showModal')" data-bs-toggle="modal"
                        data-bs-target="#airlineFormModal">
                        <i class="bi bi-plus-circle"></i> Add Airline
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>IATA Code</th>
                            <th>ICAO Code</th>
                            <th>Country</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Address</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($airlines as $airline)
                            <tr wire:key="{{ $airline->id }}">
                                <td>
                                    <a wire:navigate href="{{ route('airlines.show', $airline) }}"
                                        class="text-decoration-none">
                                        {{ $airline->name }}
                                    </a>
                                </td>
                                <td>{{ $airline->iata_code }}</td>
                                <td>{{ $airline->icao_code }}</td>
                                <td>{{ $airline->country }}</td>
                                <td>{{ $airline->phone }}</td>
                                <td>{{ $airline->email }}</td>
                                <td>{{ $airline->address }}</td>
                                <td>
                                    <span class="badge bg-{{ $airline->active ? 'success' : 'danger' }}">
                                        {{ $airline->active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button wire:click="toggleStatus({{ $airline->id }})"
                                            class="btn btn-sm btn-{{ $airline->active ? 'success' : 'danger' }}">
                                            <i class="bi bi-{{ $airline->active ? 'check' : 'x' }}"></i>
                                            {{ $airline->active ? 'Active' : 'Inactive' }}
                                        </button>
                                        <button class="btn btn-sm btn-primary" wire:click="edit({{ $airline->id }})"
                                            data-bs-toggle="modal" data-bs-target="#airlineFormModal">
                                            <i class="bi bi-pencil"></i>
                                            Edit
                                        </button>
                                        <button class="btn btn-sm btn-danger"
                                            wire:click="remove({{ $airline->id }})"
                                            wire:confirm="Are you sure you want to remove this airline?">
                                            <i class="bi bi-trash"></i>
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No airlines found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $airlines->links() }}
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="airlineFormModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form wire:submit="save">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editingAirline ? 'Edit' : 'Add' }} Airline</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Name</label>
                                    <input type="text" class="form-control form-control-sm" wire:model="form.name">
                                    @error('form.name')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">IATA Code</label>
                                    <input type="text" class="form-control form-control-sm"
                                        wire:model="form.iata_code">
                                    @error('form.iata_code')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">ICAO Code</label>
                                    <input type="text" class="form-control form-control-sm"
                                        wire:model="form.icao_code">
                                    @error('form.icao_code')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Country</label>
                                    <input type="text" class="form-control form-control-sm"
                                        wire:model="form.country">
                                    @error('form.country')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Phone</label>
                                    <input type="text" class="form-control form-control-sm"
                                        wire:model="form.phone">
                                    @error('form.phone')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control form-control-sm"
                                        wire:model="form.email">
                                    @error('form.email')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Address</label>
                                    <textarea class="form-control form-control-sm" wire:model="form.address" rows="2"></textarea>
                                    @error('form.address')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control form-control-sm" wire:model="form.description" rows="3"></textarea>
                                    @error('form.description')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-{{ $editingAirline ? 'pencil-square' : 'plus-circle' }}"></i>
                            {{ $editingAirline ? 'Update' : 'Create' }} Airline
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @script
        <script>
            $wire.on('airline-saved', () => {
                bootstrap.Modal.getInstance(document.getElementById('airlineFormModal')).hide();
            });
        </script>
    @endscript
</div>
