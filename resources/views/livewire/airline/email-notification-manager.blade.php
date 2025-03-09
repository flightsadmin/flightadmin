<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title m-0">{{ $airline->name }} Email Notifications</h4>
            <div class="d-flex gap-2">
                <div class="input-group">
                    <input wire:model.live="search" type="text" class="form-control form-control-sm" placeholder="Search notifications...">
                    <button class="btn btn-sm btn-outline-secondary" type="button">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
                <button wire:click="createNotification" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg"></i> Add Notification
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="mb-3 row">
                <div class="col-md-6">
                    <select wire:model.live="documentTypeFilter" class="form-select form-select-sm">
                        <option value="">All Document Types</option>
                        @foreach ($documentTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <select wire:model.live="stationFilter" class="form-select form-select-sm">
                        <option value="">All Stations</option>
                        @foreach ($stations as $station)
                            <option value="{{ $station->id }}">{{ $station->code }} - {{ $station->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Document Type</th>
                            <th>Scope</th>
                            <th>Recipients</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($notifications as $notification)
                            <tr>
                                <td>
                                    <span class="badge bg-info">
                                        {{ $documentTypes[$notification->document_type] ?? $notification->document_type }}
                                    </span>
                                </td>
                                <td>
                                    @if ($notification->route)
                                        <span class="badge bg-primary">{{ $notification->route->departureStation->code }}</span>
                                        <i class="bi bi-arrow-right"></i>
                                        <span class="badge bg-success">{{ $notification->route->arrivalStation->code }}</span>
                                    @elseif ($notification->station)
                                        <span class="badge bg-secondary">{{ $notification->station->code }}</span>
                                    @else
                                        <span class="badge bg-warning">All Stations</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ count($notification->email_addresses) }} recipients</span>
                                    @if (!empty($notification->cc_addresses))
                                        <span class="badge bg-light text-dark">{{ count($notification->cc_addresses) }} CC</span>
                                    @endif
                                    @if (!empty($notification->bcc_addresses))
                                        <span class="badge bg-dark">{{ count($notification->bcc_addresses) }} BCC</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $notification->is_active ? 'success' : 'danger' }}">
                                        {{ $notification->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button wire:click="editNotification({{ $notification->id }})" class="btn btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button wire:click="toggleActive({{ $notification->id }})"
                                            class="btn btn-outline-{{ $notification->is_active ? 'danger' : 'success' }}">
                                            <i class="bi bi-{{ $notification->is_active ? 'x-circle' : 'check-circle' }}"></i>
                                        </button>
                                        <button wire:click="deleteNotification({{ $notification->id }})"
                                            wire:confirm="Are you sure you want to delete this notification configuration?"
                                            class="btn btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No email notifications found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $notifications->links() }}
            </div>
        </div>
    </div>

    <!-- Email Notification Modal -->
    @if ($showModal)
        <div class="modal show d-block" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editMode ? 'Edit Email Notification' : 'Add Email Notification' }}</h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit="save">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="document_type" class="form-label">Document Type</label>
                                    <select class="form-select" id="document_type" wire:model="document_type">
                                        <option value="">Select Document Type</option>
                                        @foreach ($documentTypes as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('document_type')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="station_id" class="form-label">Station (Optional)</label>
                                    <select class="form-select" id="station_id" wire:model.live="station_id">
                                        <option value="">All Stations</option>
                                        @foreach ($stations as $station)
                                            <option value="{{ $station->id }}">{{ $station->code }} - {{ $station->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="text-muted small">Leave blank to apply to all stations</div>
                                    @error('station_id')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="route_id" class="form-label">Route (Optional)</label>
                                <select class="form-select" id="route_id" wire:model="route_id"
                                    @if (!$station_id) disabled @endif>
                                    <option value="">All Routes</option>
                                    @foreach ($routes as $route)
                                        <option value="{{ $route->id }}">
                                            {{ $route->departureStation->code }} - {{ $route->arrivalStation->code }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="text-muted small">
                                    @if (!$station_id)
                                        Select a station first to see available routes
                                    @else
                                        Leave blank to apply to all routes from/to this station
                                    @endif
                                </div>
                                @error('route_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email Recipients (To)</label>
                                <div class="input-group mb-2">
                                    <input type="email" class="form-control" wire:model="newEmail" wire:keydown.enter.prevent="addEmail"
                                        placeholder="Enter email address">
                                    <button class="btn btn-outline-primary" type="button" wire:click="addEmail">
                                        <i class="bi bi-plus"></i> Add
                                    </button>
                                </div>
                                @error('newEmail')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                                @error('email_addresses')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror

                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    @foreach ($email_addresses as $index => $email)
                                        <div class="badge bg-primary d-flex align-items-center p-2">
                                            {{ $email }}
                                            <button type="button" class="btn-close btn-close-white ms-2"
                                                wire:click="removeEmail({{ $index }})" style="font-size: 0.5rem;"></button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">CC Recipients (Optional)</label>
                                <div class="input-group mb-2">
                                    <input type="email" class="form-control" wire:model="newCc" wire:keydown.enter.prevent="addCc"
                                        placeholder="Enter CC email address">
                                    <button class="btn btn-outline-secondary" type="button" wire:click="addCc">
                                        <i class="bi bi-plus"></i> Add
                                    </button>
                                </div>
                                @error('newCc')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                                @error('cc_addresses')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror

                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    @foreach ($cc_addresses as $index => $email)
                                        <div class="badge bg-secondary d-flex align-items-center p-2">
                                            {{ $email }}
                                            <button type="button" class="btn-close btn-close-white ms-2"
                                                wire:click="removeCc({{ $index }})" style="font-size: 0.5rem;"></button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">BCC Recipients (Optional)</label>
                                <div class="input-group mb-2">
                                    <input type="email" class="form-control" wire:model="newBcc" wire:keydown.enter.prevent="addBcc"
                                        placeholder="Enter BCC email address">
                                    <button class="btn btn-outline-dark" type="button" wire:click="addBcc">
                                        <i class="bi bi-plus"></i> Add
                                    </button>
                                </div>
                                @error('newBcc')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                                @error('bcc_addresses')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror

                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    @foreach ($bcc_addresses as $index => $email)
                                        <div class="badge bg-dark d-flex align-items-center p-2">
                                            {{ $email }}
                                            <button type="button" class="btn-close btn-close-white ms-2"
                                                wire:click="removeBcc({{ $index }})" style="font-size: 0.5rem;"></button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes (Optional)</label>
                                <textarea class="form-control" id="notes" wire:model="notes" rows="2"></textarea>
                                @error('notes')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="is_active" wire:model="is_active">
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">Cancel</button>
                                <button type="submit" class="btn btn-primary">
                                    {{ $editMode ? 'Update' : 'Create' }} Notification
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
