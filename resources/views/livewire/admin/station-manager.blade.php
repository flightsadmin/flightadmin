<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title m-0 d-flex align-items-center">
                <i class="bi bi-geo-alt me-2 text-primary"></i>
                Stations
            </h4>
            <div class="d-flex gap-2">
                <div class="d-flex align-items-center">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-search"></i>
                        </span>
                        <input wire:model.live="search" type="text" class="form-control border-start-0"
                            placeholder="Search stations...">
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <livewire:admin.station-creator />
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">Code</th>
                            <th>Name</th>
                            <th>Country</th>
                            <th>Timezone</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stations as $station)
                            <tr>
                                <td class="ps-3">
                                    <span class="badge bg-primary">{{ $station->code }}</span>
                                </td>
                                <td>{{ $station->name }}</td>
                                <td>
                                    @if($station->country)
                                        <span class="d-flex align-items-center">
                                            <i class="bi bi-globe me-1"></i> {{ $station->country }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($station->timezone)
                                        <span class="d-flex align-items-center">
                                            <i class="bi bi-clock me-1"></i> {{ $station->timezone }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span
                                        class="badge bg-{{ $station->is_active ? 'success' : 'danger' }} rounded-pill px-3">
                                        {{ $station->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <button wire:click="editStation({{ $station->id }})" class="btn btn-outline-primary"
                                            title="Edit Station">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button wire:click="toggleActive({{ $station->id }})"
                                            class="btn btn-outline-{{ $station->is_active ? 'danger' : 'success' }}"
                                            title="{{ $station->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="bi bi-{{ $station->is_active ? 'x-circle' : 'check-circle' }}"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bi bi-geo-alt display-6 mb-3 text-secondary"></i>
                                        <p class="mb-1">No stations found</p>
                                        <small>Click "Create Station" to add a new station</small>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-3 py-2 border-top-2">
                {{ $stations->links() }}
            </div>
        </div>
    </div>

    <!-- Station Modal -->
    @if ($showModal)
        <div class="modal show d-block" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editMode ? 'Edit Station' : 'Add Station' }}</h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit="save">
                            <div class="mb-3">
                                <label for="code" class="form-label">Station Code</label>
                                <input type="text" class="form-control" id="code" wire:model="code" maxlength="3"
                                    placeholder="e.g. LHR">
                                @error('code')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="name" class="form-label">Station Name</label>
                                <input type="text" class="form-control" id="name" wire:model="name"
                                    placeholder="e.g. London Heathrow">
                                @error('name')
                                    <div class="text-danger small">{{ $message ?? 'Station name is required' }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="country" class="form-label">Country</label>
                                <input type="text" class="form-control" id="country" wire:model="country"
                                    placeholder="e.g. United Kingdom">
                                @error('country')
                                    <div class="text-danger small">{{ $message ?? 'Invalid country' }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="timezone" class="form-label">Timezone</label>
                                <input type="text" class="form-control" id="timezone" wire:model="timezone"
                                    placeholder="e.g. Europe/London">
                                @error('timezone')
                                    <div class="text-danger small">{{ $message ?? 'Invalid timezone' }}</div>
                                @enderror
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="is_active" wire:model="is_active">
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-secondary"
                                    wire:click="$set('showModal', false)">Cancel</button>
                                <button type="submit" class="btn btn-primary">
                                    {{ $editMode ? 'Update' : 'Create' }} Station
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop show"></div>
    @endif
</div>