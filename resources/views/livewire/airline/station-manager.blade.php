<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title m-0 d-flex align-items-center">
                <i class="bi bi-geo-alt me-2 text-primary"></i>
                {{ $airline->name }} Stations
            </h4>
            <div class="d-flex align-items-center gap-2">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-search"></i>
                    </span>
                    <input wire:model.live="search" type="text" class="form-control border-start-0"
                        placeholder="Search stations...">
                </div>
                <button wire:click="openAssignModal" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center nowrap">
                    <i class="bi bi-link me-1"></i><span class="text-nowrap">Assign Station</span>
                </button>
                <livewire:admin.station-creator callbackEvent="station-saved" />
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Code</th>
                            <th>Name</th>
                            <th>Country</th>
                            <th class="text-center">Hub</th>
                            <th>Contact</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($assignedStations as $station)
                            <tr>
                                <td class="ps-3">
                                    <span class="badge bg-primary">{{ $station->code }}</span>
                                </td>
                                <td>{{ $station->name }}</td>
                                <td>
                                    @if ($station->country)
                                        <span class="d-flex align-items-center">
                                            <i class="bi bi-globe me-1"></i> {{ $station->country }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($station->pivot->is_hub)
                                        <span class="badge bg-success rounded-pill px-3">
                                            <i class="bi bi-star-fill me-1"></i> Hub
                                        </span>
                                    @else
                                        <span class="badge bg-secondary rounded-pill px-3">Spoke</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($station->pivot->contact_email)
                                        <a href="mailto:{{ $station->pivot->contact_email }}"
                                            class="text-decoration-none d-flex align-items-center text-truncate" style="max-width: 200px;">
                                            <i class="bi bi-envelope me-1"></i> {{ $station->pivot->contact_email }}
                                        </a>
                                    @endif
                                    @if ($station->pivot->contact_phone)
                                        <div class="d-flex align-items-center text-truncate" style="max-width: 200px;">
                                            <i class="bi bi-telephone me-1"></i> {{ $station->pivot->contact_phone }}
                                        </div>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <button wire:click="editAssignment({{ $station->id }})" class="btn btn-outline-primary"
                                            title="Edit Assignment">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button wire:click="toggleHub({{ $station->id }})"
                                            class="btn btn-outline-{{ $station->pivot->is_hub ? 'secondary' : 'success' }}"
                                            title="{{ $station->pivot->is_hub ? 'Remove Hub Status' : 'Set as Hub' }}">
                                            <i class="bi bi-{{ $station->pivot->is_hub ? 'star-fill' : 'star' }}"></i>
                                        </button>
                                        <button wire:click="removeStation({{ $station->id }})"
                                            wire:confirm="Are you sure you want to remove this station from {{ $airline->name }}?"
                                            class="btn btn-outline-danger" title="Remove Station">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bi bi-geo-alt display-6 mb-3 text-secondary"></i>
                                        <p class="mb-1">No stations assigned to this airline</p>
                                        <small>Click "Assign Station" to add stations where this airline operates</small>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($assignedStations->hasPages())
                <div class="px-3 py-2 border-top">
                    {{ $assignedStations->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Station Assignment Modal -->
    @if ($showAssignModal)
        <div class="modal show d-block" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content shadow">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title">
                            <i class="bi bi-{{ $station_id ? 'pencil-square' : 'link' }} me-2"></i>
                            {{ $station_id ? 'Edit Station Assignment' : 'Assign Station to ' . $airline->name }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showAssignModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit="saveAssignment">
                            <div class="mb-3">
                                <label for="station_id" class="form-label fw-medium">Station <span class="text-danger">*</span></label>
                                <select class="form-select" id="station_id" wire:model="station_id"
                                    @if ($station_id) disabled @endif>
                                    <option value="">Select Station</option>
                                    @foreach ($availableStations as $station)
                                        <option value="{{ $station->id }}">{{ $station->code }} - {{ $station->name }}</option>
                                    @endforeach
                                </select>
                                @if (count($availableStations) === 0 && !$station_id)
                                    <div class="alert alert-info mt-2 mb-0 py-2">
                                        <small>
                                            <i class="bi bi-info-circle me-1"></i>
                                            No available stations found. Create a new station first.
                                        </small>
                                    </div>
                                @endif
                                @error('station_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="is_hub" wire:model="is_hub">
                                <label class="form-check-label" for="is_hub">Hub Station</label>
                                <div class="form-text text-muted">
                                    <small>Mark this as a hub station for {{ $airline->name }}</small>
                                </div>
                            </div>

                            <hr class="my-3">
                            <h6 class="mb-3">Contact Information</h6>

                            <div class="mb-3">
                                <label for="contact_email" class="form-label fw-medium">Contact Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control" id="contact_email" wire:model="contact_email"
                                        placeholder="operations@example.com">
                                </div>
                                @error('contact_email')
                                    <div class="text-danger small mt-1">{{ $message ?? 'Invalid email address' }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="contact_phone" class="form-label fw-medium">Contact Phone</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-telephone"></i></span>
                                    <input type="text" class="form-control" id="contact_phone" wire:model="contact_phone"
                                        placeholder="+1 234 567 8900">
                                </div>
                                @error('contact_phone')
                                    <div class="text-danger small mt-1">{{ $message ?? 'Invalid phone number' }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label fw-medium">Notes</label>
                                <textarea class="form-control" id="notes" wire:model="notes" rows="3"
                                    placeholder="Additional information about this station"></textarea>
                                @error('notes')
                                    <div class="text-danger small mt-1">{{ $message ?? 'Notes are required' }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-outline-secondary" wire:click="$set('showAssignModal', false)">
                                    <i class="bi bi-x-circle me-1"></i> Cancel
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-{{ $station_id ? 'check-circle' : 'link' }} me-1"></i>
                                    {{ $station_id ? 'Update' : 'Assign' }} Station
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
