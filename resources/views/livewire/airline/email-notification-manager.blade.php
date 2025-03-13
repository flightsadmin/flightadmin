<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title m-0 d-flex align-items-center">
                <i class="bi bi-envelope me-2 text-primary"></i>
                {{ $airline->iata_code }} Notifications
            </h4>
            <div class="d-flex gap-2">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-search"></i>
                    </span>
                    <input wire:model.live="search" type="text" class="form-control border-start-0"
                        placeholder="Search notifications...">
                </div>
                <select wire:model.live="documentTypeFilter" id="documentTypeFilter" class="form-select form-select-sm">
                    <option value="">All Document Types</option>
                    @foreach ($documentTypes as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <select wire:model.live="stationFilter" id="stationFilter" class="form-select form-select-sm">
                    <option value="">All Stations</option>
                    @foreach ($stations as $station)
                        <option value="{{ $station->id }}">{{ $station->code }} - {{ $station->name }}</option>
                    @endforeach
                </select>
                <button wire:click="createNotification" class="btn btn-sm btn-primary d-inline-flex align-items-center nowrap">
                    <i class="bi bi-plus-circle me-1"></i><span class="text-nowrap">Add Notification</span>
                </button>
            </div>
        </div>
        <div class="card-body p-2">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Document Type</th>
                            <th>Scope</th>
                            <th>Recipients</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($notifications as $notification)
                            <tr>
                                <td class="ps-3">
                                    <span class="badge bg-info rounded-pill px-3">
                                        {{ $documentTypes[$notification->document_type] ?? $notification->document_type }}
                                    </span>
                                </td>
                                <td>
                                    @if ($notification->route)
                                        <div class="d-flex align-items-center">
                                            <span
                                                class="badge bg-primary me-1">{{ $notification->route->departureStation->code }}</span>
                                            <i class="bi bi-arrow-right text-muted mx-1"></i>
                                            <span
                                                class="badge bg-success">{{ $notification->route->arrivalStation->code }}</span>
                                        </div>
                                        <div class="small text-muted mt-1">Specific Route</div>
                                    @elseif ($notification->station)
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-secondary">{{ $notification->station->code }}</span>
                                        </div>
                                        <div class="small text-muted mt-1">Station: {{ $notification->station->name }}</div>
                                    @else
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-warning text-dark">All Stations</span>
                                        </div>
                                        <div class="small text-muted mt-1">Airline-wide default</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1 align-items-center">
                                        @if (!empty($notification->email_addresses))
                                            <span class="badge bg-secondary d-flex align-items-center">
                                                <i class="bi bi-envelope-fill me-1"></i>
                                                {{ count($notification->email_addresses) }} Emails
                                            </span>
                                        @endif
                                        @if (!empty($notification->sita_addresses))
                                            <span class="badge bg-primary d-flex align-items-center">
                                                <i class="bi bi-send me-1"></i> {{ count($notification->sita_addresses) }} SITA
                                            </span>
                                        @endif
                                    </div>
                                    <div class="small text-muted mt-1">
                                        @if (!empty($notification->email_addresses))
                                            <div class="text-truncate" style="max-width: 200px;"
                                                title="{{ implode(', ', $notification->email_addresses) }}">
                                                <i class="bi bi-envelope-fill me-1 small"></i>
                                                {{ implode(', ', array_slice($notification->email_addresses, 0, 2)) }}
                                                @if (count($notification->email_addresses) > 2)
                                                    <span>+{{ count($notification->email_addresses) - 2 }} more</span>
                                                @endif
                                            </div>
                                        @endif
                                        @if (!empty($notification->sita_addresses))
                                            <div class="text-truncate" style="max-width: 200px;"
                                                title="{{ implode(', ', $notification->sita_addresses) }}">
                                                <i class="bi bi-send me-1 small"></i>
                                                {{ implode(', ', array_slice($notification->sita_addresses, 0, 2)) }}
                                                @if (count($notification->sita_addresses) > 2)
                                                    <span>+{{ count($notification->sita_addresses) - 2 }} more</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span
                                        class="badge bg-{{ $notification->is_active ? 'success' : 'danger' }} rounded-pill px-3">
                                        {{ $notification->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <button wire:click="editNotification({{ $notification->id }})"
                                            class="btn btn-outline-primary" title="Edit Notification">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button wire:click="toggleActive({{ $notification->id }})"
                                            class="btn btn-outline-{{ $notification->is_active ? 'danger' : 'success' }}"
                                            title="{{ $notification->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i
                                                class="bi bi-{{ $notification->is_active ? 'x-circle' : 'check-circle' }}"></i>
                                        </button>
                                        <button wire:click="deleteNotification({{ $notification->id }})"
                                            wire:confirm="Are you sure you want to delete this notification configuration?"
                                            class="btn btn-outline-danger" title="Delete Notification">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bi bi-envelope display-6 mb-3 text-secondary"></i>
                                        <p class="mb-1">No email notifications found</p>
                                        <small>Click "Add Notification" to configure email recipients for documents</small>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($notifications->hasPages())
                <div class="px-3 py-2 border-top">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Email Notification Modal -->
    @if ($showModal)
        <div class="modal show d-block" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content shadow">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title">
                            <i class="bi bi-{{ $editMode ? 'pencil-square' : 'envelope-plus' }} me-2"></i>
                            {{ $editMode ? 'Edit Email Notification' : 'Add Email Notification' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <form wire:submit="save">
                        <div class="modal-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="document_type" class="form-label fw-medium">Document Type <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i
                                                class="bi bi-file-earmark-text"></i></span>
                                        <select class="form-select" id="document_type" wire:model="document_type">
                                            <option value="">Select Document Type</option>
                                            @foreach ($documentTypes as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('document_type')
                                        <div class="text-danger small mt-1">{{ $message ?? 'Document type is required' }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="station_id" class="form-label fw-medium">Station (Optional)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-geo-alt"></i></span>
                                        <select class="form-select" id="station_id" wire:model.live="station_id">
                                            <option value="">All Stations</option>
                                            @foreach ($stations as $station)
                                                <option value="{{ $station->id }}">{{ $station->code }} - {{ $station->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-text text-muted"><small>Leave blank to apply to all stations</small>
                                    </div>
                                    @error('station_id')
                                        <div class="text-danger small mt-1">{{ $message ?? 'Invalid station' }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="route_id" class="form-label fw-medium">Route (Optional)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="bi bi-signpost-split"></i></span>
                                            <select class="form-select" id="route_id" wire:model="route_id"
                                                @if (!$station_id) disabled @endif>
                                                <option value="">All Routes</option>
                                                @foreach ($routes as $route)
                                                    <option value="{{ $route->id }}">
                                                        {{ $route->departureStation->code }} - {{ $route->arrivalStation->code }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-text text-muted">
                                            <small>
                                                @if (!$station_id)
                                                    Select a station first to see available routes
                                                @else
                                                    Leave blank to apply to all routes from/to this station
                                                @endif
                                            </small>
                                        </div>
                                        @error('route_id')
                                            <div class="text-danger small mt-1">{{ $message ?? 'Invalid route' }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="is_active" class="form-label fw-medium">Active</label>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="is_active"
                                                wire:model="is_active">
                                            <label class="form-check-label" for="is_active">Active</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-3">
                            <h6 class="mb-3 d-flex align-items-center">
                                <i class="bi bi-envelope-paper me-2"></i> Notification Recipients
                            </h6>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label class="form-label fw-medium">Email Recipients</label>
                                        <div class="input-group mb-2">
                                            <span class="input-group-text bg-light"><i class="bi bi-envelope-fill"></i></span>
                                            <input type="email" class="form-control" wire:model="newEmail"
                                                wire:keydown.enter.prevent="addEmail" placeholder="Enter email address">
                                            <button class="btn btn-outline-primary" type="button" wire:click="addEmail">
                                                <i class="bi bi-plus"></i> Add
                                            </button>
                                        </div>
                                        @error('newEmail')
                                            <div class="text-danger small mt-1">{{ $message ?? 'Invalid email format' }}</div>
                                        @enderror
                                        @error('email_addresses')
                                            <div class="text-danger small mt-1">
                                                {{ $message ?? 'At least one recipient is required' }}
                                            </div>
                                        @enderror

                                        <div class="d-flex flex-wrap gap-2 mt-2">
                                            @foreach ($email_addresses as $index => $email)
                                                <div class="badge bg-primary d-flex align-items-center p-2">
                                                    <i class="bi bi-envelope-fill me-1"></i> {{ $email }}
                                                    <button type="button" class="btn-close btn-close-white ms-2"
                                                        wire:click="removeEmail({{ $index }})"
                                                        style="font-size: 0.5rem;"></button>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label class="form-label fw-medium">SITA Addresses</label>
                                        <div class="input-group mb-2">
                                            <span class="input-group-text bg-light"><i class="bi bi-send"></i></span>
                                            <input type="text" class="form-control" wire:model="newSita"
                                                wire:keydown.enter.prevent="addSita" placeholder="Enter SITA address (e.g. LHROPXH)">
                                            <button class="btn btn-outline-secondary" type="button" wire:click="addSita">
                                                <i class="bi bi-plus"></i> Add
                                            </button>
                                        </div>
                                        @error('newSita')
                                            <div class="text-danger small mt-1">{{ $message ?? 'Invalid SITA address format' }}</div>
                                        @enderror
                                        @error('sita_addresses')
                                            <div class="text-danger small mt-1">{{ $message ?? 'Invalid SITA addresses' }}</div>
                                        @enderror

                                        <div class="d-flex flex-wrap gap-2 mt-2">
                                            @foreach ($sita_addresses as $index => $sita)
                                                <div class="badge bg-secondary d-flex align-items-center p-2">
                                                    <i class="bi bi-send me-1"></i> {{ $sita }}
                                                    <button type="button" class="btn-close btn-close-white ms-2"
                                                        wire:click="removeSita({{ $index }})" style="font-size: 0.5rem;"></button>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer d-flex justify-content-between gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                wire:click="$set('showModal', false)">
                                <i class="bi bi-x-circle me-1"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="bi bi-{{ $editMode ? 'check-circle' : 'envelope-plus' }} me-1"></i>
                                {{ $editMode ? 'Update' : 'Create' }} Notification
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal-backdrop show"></div>
    @endif
</div>
