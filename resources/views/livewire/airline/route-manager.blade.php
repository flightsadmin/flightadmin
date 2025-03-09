<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title m-0">{{ $airline->name }} Routes</h4>
            <div class="d-flex gap-2">
                <div class="input-group">
                    <input wire:model.live="search" type="text" class="form-control form-control-sm" placeholder="Search routes...">
                    <button class="btn btn-sm btn-outline-secondary" type="button">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
                <button wire:click="createRoute" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg"></i> Add Route
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="mb-3 row">
                <div class="col-md-6">
                    <select wire:model.live="departureFilter" class="form-select form-select-sm">
                        <option value="">All Departure Stations</option>
                        @foreach ($stations as $station)
                            <option value="{{ $station->id }}">{{ $station->code }} - {{ $station->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <select wire:model.live="arrivalFilter" class="form-select form-select-sm">
                        <option value="">All Arrival Stations</option>
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
                            <th>Route</th>
                            <th>Flight Time</th>
                            <th>Distance</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($routes as $route)
                            <tr>
                                <td>
                                    <span class="badge bg-primary">{{ $route->departureStation->code }}</span>
                                    <i class="bi bi-arrow-right"></i>
                                    <span class="badge bg-success">{{ $route->arrivalStation->code }}</span>
                                    <div class="small text-muted">
                                        {{ $route->departureStation->name }} to {{ $route->arrivalStation->name }}
                                    </div>
                                </td>
                                <td>
                                    @if ($route->flight_time)
                                        {{ floor($route->flight_time / 60) }}h {{ $route->flight_time % 60 }}m
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if ($route->distance)
                                        {{ $route->distance }} km
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $route->is_active ? 'success' : 'danger' }}">
                                        {{ $route->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button wire:click="editRoute({{ $route->id }})" class="btn btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button wire:click="toggleActive({{ $route->id }})"
                                            class="btn btn-outline-{{ $route->is_active ? 'danger' : 'success' }}">
                                            <i class="bi bi-{{ $route->is_active ? 'x-circle' : 'check-circle' }}"></i>
                                        </button>
                                        <button wire:click="deleteRoute({{ $route->id }})"
                                            wire:confirm="Are you sure you want to delete this route? This may affect schedules and flights."
                                            class="btn btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No routes found for this airline</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $routes->links() }}
            </div>
        </div>
    </div>

    <!-- Route Modal -->
    @if ($showModal)
        <div class="modal show d-block" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editMode ? 'Edit Route' : 'Add Route' }}</h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit="save">
                            <div class="mb-3">
                                <label for="departure_station_id" class="form-label">Departure Station</label>
                                <select class="form-select" id="departure_station_id" wire:model="departure_station_id">
                                    <option value="">Select Departure Station</option>
                                    @foreach ($stations as $station)
                                        <option value="{{ $station->id }}">{{ $station->code }} - {{ $station->name }}</option>
                                    @endforeach
                                </select>
                                @error('departure_station_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="arrival_station_id" class="form-label">Arrival Station</label>
                                <select class="form-select" id="arrival_station_id" wire:model="arrival_station_id">
                                    <option value="">Select Arrival Station</option>
                                    @foreach ($stations as $station)
                                        <option value="{{ $station->id }}">{{ $station->code }} - {{ $station->name }}</option>
                                    @endforeach
                                </select>
                                @error('arrival_station_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="flight_time" class="form-label">Flight Time (minutes)</label>
                                        <input type="number" class="form-control" id="flight_time" wire:model="flight_time" min="1">
                                        <div class="text-muted small">e.g. 90 for 1h 30m</div>
                                        @error('flight_time')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="distance" class="form-label">Distance (km)</label>
                                        <input type="number" class="form-control" id="distance" wire:model="distance" min="1">
                                        @error('distance')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="notes" wire:model="notes" rows="3"></textarea>
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
