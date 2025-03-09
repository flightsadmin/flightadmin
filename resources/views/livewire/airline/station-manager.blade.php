<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title m-0">{{ $airline->name }} Stations</h4>
            <div class="d-flex gap-2">
                <div class="input-group">
                    <input wire:model.live="search" type="text" class="form-control form-control-sm" placeholder="Search stations...">
                    <button class="btn btn-sm btn-outline-secondary" type="button">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
                <button wire:click="openAssignModal" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg"></i> Assign Station
                </button>
                <livewire:admin.station-creator callbackEvent="station-saved" />
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Country</th>
                            <th>Hub</th>
                            <th>Contact</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($assignedStations as $station)
                            <tr>
                                <td>{{ $station->code }}</td>
                                <td>{{ $station->name }}</td>
                                <td>{{ $station->country }}</td>
                                <td>
                                    <span class="badge bg-{{ $station->pivot->is_hub ? 'success' : 'secondary' }}">
                                        {{ $station->pivot->is_hub ? 'Hub' : 'Spoke' }}
                                    </span>
                                </td>
                                <td>
                                    @if ($station->pivot->contact_email)
                                        <a href="mailto:{{ $station->pivot->contact_email }}" class="text-decoration-none">
                                            <i class="bi bi-envelope"></i> {{ $station->pivot->contact_email }}
                                        </a>
                                    @endif
                                    @if ($station->pivot->contact_phone)
                                        <div>
                                            <i class="bi bi-telephone"></i> {{ $station->pivot->contact_phone }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button wire:click="editAssignment({{ $station->id }})" class="btn btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button wire:click="toggleHub({{ $station->id }})"
                                            class="btn btn-outline-{{ $station->pivot->is_hub ? 'secondary' : 'success' }}">
                                            <i class="bi bi-{{ $station->pivot->is_hub ? 'star-fill' : 'star' }}"></i>
                                        </button>
                                        <button wire:click="removeStation({{ $station->id }})"
                                            wire:confirm="Are you sure you want to remove this station from {{ $airline->name }}?"
                                            class="btn btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No stations assigned to this airline</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $assignedStations->links() }}
            </div>
        </div>
    </div>

    <!-- Station Assignment Modal -->
    @if ($showAssignModal)
        <div class="modal show d-block" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $station_id ? 'Edit Station Assignment' : 'Assign Station' }}</h5>
                        <button type="button" class="btn-close" wire:click="$set('showAssignModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit="saveAssignment">
                            <div class="mb-3">
                                <label for="station_id" class="form-label">Station</label>
                                <select class="form-select" id="station_id" wire:model="station_id"
                                    @if ($station_id) disabled @endif>
                                    <option value="">Select Station</option>
                                    @foreach ($availableStations as $station)
                                        <option value="{{ $station->id }}">{{ $station->code }} - {{ $station->name }}</option>
                                    @endforeach
                                </select>
                                @error('station_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="is_hub" wire:model="is_hub">
                                <label class="form-check-label" for="is_hub">Hub Station</label>
                                <div class="text-muted small">Mark this as a hub station for {{ $airline->name }}</div>
                            </div>

                            <div class="mb-3">
                                <label for="contact_email" class="form-label">Contact Email</label>
                                <input type="email" class="form-control" id="contact_email" wire:model="contact_email"
                                    placeholder="operations@example.com">
                                @error('contact_email')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="contact_phone" class="form-label">Contact Phone</label>
                                <input type="text" class="form-control" id="contact_phone" wire:model="contact_phone"
                                    placeholder="+1 234 567 8900">
                                @error('contact_phone')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="notes" wire:model="notes" rows="3" placeholder="Additional information about this station"></textarea>
                                @error('notes')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-secondary" wire:click="$set('showAssignModal', false)">Cancel</button>
                                <button type="submit" class="btn btn-primary">
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
