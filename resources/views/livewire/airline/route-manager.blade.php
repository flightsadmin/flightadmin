<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title m-0 d-flex align-items-center">
                <i class="bi bi-signpost-split me-2 text-primary"></i>
                {{ $airline->name }} Routes
            </h4>
            <div class="d-flex gap-2">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-search"></i>
                    </span>
                    <input wire:model.live="search" type="text" class="form-control border-start-0"
                        placeholder="Search routes...">
                </div>
                <select wire:model.live="departureFilter" id="departureFilter"
                    class="form-select form-select-sm">
                    <option value="">All Departure Stations</option>
                    @foreach ($stations as $station)
                        <option value="{{ $station->id }}">{{ $station->code }} - {{ $station->name }}</option>
                    @endforeach
                </select>
                <select wire:model.live="arrivalFilter" id="arrivalFilter" class="form-select form-select-sm">
                    <option value="">All Arrival Stations</option>
                    @foreach ($stations as $station)
                        <option value="{{ $station->id }}">{{ $station->code }} - {{ $station->name }}</option>
                    @endforeach
                </select>
                <button wire:click="createRoute" class="btn btn-sm btn-primary d-inline-flex align-items-center nowrap">
                    <i class="bi bi-plus-circle"></i>
                    <span class="text-nowrap"> Add Route</span>
                </button>
            </div>
        </div>
        <div class="card-body p-2">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Route</th>
                            <th>Flight Time</th>
                            <th>Distance</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($routes as $route)
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-primary me-2">{{ $route->departureStation->code }}</span>
                                        <i class="bi bi-arrow-right text-muted mx-1"></i>
                                        <span class="badge bg-success">{{ $route->arrivalStation->code }}</span>
                                    </div>
                                    <div class="small text-muted mt-1">
                                        {{ $route->departureStation->name }} to {{ $route->arrivalStation->name }}
                                    </div>
                                </td>
                                <td>
                                    @if ($route->flight_time)
                                        <span class="d-flex align-items-center">
                                            <i class="bi bi-clock me-1"></i>
                                            {{ floor($route->flight_time / 60) }}h {{ $route->flight_time % 60 }}m
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($route->distance)
                                        <span class="d-flex align-items-center">
                                            <i class="bi bi-rulers me-1"></i>
                                            {{ number_format($route->distance) }} km
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $route->is_active ? 'success' : 'danger' }} rounded-pill px-3">
                                        {{ $route->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <button wire:click="editRoute({{ $route->id }})" class="btn btn-outline-primary"
                                            title="Edit Route">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button wire:click="toggleActive({{ $route->id }})"
                                            class="btn btn-outline-{{ $route->is_active ? 'danger' : 'success' }}"
                                            title="{{ $route->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="bi bi-{{ $route->is_active ? 'x-circle' : 'check-circle' }}"></i>
                                        </button>
                                        <button wire:click="deleteRoute({{ $route->id }})"
                                            wire:confirm="Are you sure you want to delete this route? This may affect schedules and flights."
                                            class="btn btn-outline-danger" title="Delete Route">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bi bi-signpost-split display-6 mb-3 text-secondary"></i>
                                        <p class="mb-1">No routes found for this airline</p>
                                        <small>Click "Add Route" to create a new route</small>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($routes->hasPages())
                <div class="px-3 py-2 border-top">
                    {{ $routes->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Route Modal -->
    @if ($showModal)
        <div class="modal show d-block" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content shadow">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title">
                            <i class="bi bi-{{ $editMode ? 'pencil-square' : 'signpost-split' }} me-2"></i>
                            {{ $editMode ? 'Edit Route' : 'Add New Route' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit="save">
                            <div class="mb-3">
                                <label for="departure_station_id" class="form-label fw-medium">Departure Station <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-airplane-fill"></i></span>
                                    <select class="form-select" id="departure_station_id" wire:model="departure_station_id">
                                        <option value="">Select Departure Station</option>
                                        @foreach ($stations as $station)
                                            <option value="{{ $station->id }}">{{ $station->code }} - {{ $station->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('departure_station_id')
                                    <div class="text-danger small mt-1">{{ $message ?? 'Departure station is required' }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="arrival_station_id" class="form-label fw-medium">Arrival Station <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-airplane-engines"></i></span>
                                    <select class="form-select" id="arrival_station_id" wire:model="arrival_station_id">
                                        <option value="">Select Arrival Station</option>
                                        @foreach ($stations as $station)
                                            <option value="{{ $station->id }}">{{ $station->code }} - {{ $station->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('arrival_station_id')
                                    <div class="text-danger small mt-1">{{ $message ?? 'Arrival station is required' }}</div>
                                @enderror
                            </div>

                            <hr class="my-3">
                            <h6 class="mb-3">Route Details</h6>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="flight_time" class="form-label fw-medium">Flight Time (minutes)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="bi bi-clock"></i></span>
                                            <input type="number" class="form-control" id="flight_time"
                                                wire:model="flight_time" min="1">
                                        </div>
                                        <div class="form-text text-muted"><small>e.g. 90 for 1h 30m</small></div>
                                        @error('flight_time')
                                            <div class="text-danger small mt-1">{{ $message ?? 'Invalid flight time' }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="distance" class="form-label fw-medium">Distance (km)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="bi bi-rulers"></i></span>
                                            <input type="number" class="form-control" id="distance" wire:model="distance"
                                                min="1">
                                        </div>
                                        @error('distance')
                                            <div class="text-danger small mt-1">{{ $message ?? 'Invalid distance' }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label fw-medium">Notes</label>
                                <textarea class="form-control" id="notes" wire:model="notes" rows="3"
                                    placeholder="Additional information about this route"></textarea>
                                @error('notes')
                                    <div class="text-danger small mt-1">{{ $message ?? 'Invalid notes format' }}</div>
                                @enderror
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="is_active" wire:model="is_active">
                                <label class="form-check-label" for="is_active">Active</label>
                                <div class="form-text text-muted"><small>Inactive routes won't be available for
                                        scheduling</small></div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-outline-secondary"
                                    wire:click="$set('showModal', false)">
                                    <i class="bi bi-x-circle me-1"></i> Cancel
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-{{ $editMode ? 'check-circle' : 'plus-circle' }} me-1"></i>
                                    {{ $editMode ? 'Update' : 'Create' }} Route
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
