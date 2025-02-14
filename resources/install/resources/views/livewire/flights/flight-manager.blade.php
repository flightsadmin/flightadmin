<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="card-title m-0">Flights</h2>
            <div class="d-flex justify-content-between align-items-center gap-2">
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm"
                    placeholder="Search flights...">

                <input type="date" wire:model.live="date" class="form-control form-control-sm">

                <select wire:model.live="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="scheduled">Scheduled</option>
                    <option value="boarding">Boarding</option>
                    <option value="departed">Departed</option>
                    <option value="arrived">Arrived</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <div style="width: 450px;">
                    <button wire:click="createFlight" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                        data-bs-target="#flightFormModal">
                        <i class="bi bi-plus-lg"></i> Add Flight
                    </button>

                </div>
            </div>

        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Flight Number</th>
                            <th>Airline</th>
                            <th>Route</th>
                            <th>STD</th>
                            <th>STA</th>
                            <th>Aircraft</th>
                            <th>Status</th>
                            <th>Actions</th>

                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($flights as $flight)
                            <tr>
                                <td>
                                    <a wire:navigate href="{{ route('flights.show', $flight) }}"
                                        class="text-decoration-none">
                                        {{ $flight->flight_number }}
                                    </a>
                                </td>
                                <td>{{ $flight->airline->iata_code }}</td>
                                <td>{{ $flight->departure_airport }} → {{ $flight->arrival_airport }}</td>
                                <td> {{ $flight->scheduled_departure_time->format('d M Y H:i') }}</td>
                                <td> {{ $flight->scheduled_arrival_time->format('d M Y H:i') }}</td>
                                <td>{{ $flight->aircraft->registration_number }}</td>
                                <td>
                                    <div class="dropdown">
                                        <button
                                            class="btn btn-sm btn-{{ $flight->status === 'cancelled' ? 'danger' : ($flight->status === 'arrived' ? 'success' : 'warning') }} dropdown-toggle"
                                            type="button" data-bs-toggle="dropdown">
                                            {{ ucfirst($flight->status) }}
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <button wire:click="updateStatus({{ $flight->id }}, 'scheduled')"
                                                    class="dropdown-item">Scheduled</button>
                                            </li>
                                            <li>
                                                <button wire:click="updateStatus({{ $flight->id }}, 'boarding')"
                                                    class="dropdown-item">Boarding</button>
                                            </li>
                                            <li>
                                                <button wire:click="updateStatus({{ $flight->id }}, 'departed')"
                                                    class="dropdown-item">Departed</button>
                                            </li>
                                            <li>
                                                <button wire:click="updateStatus({{ $flight->id }}, 'arrived')"
                                                    class="dropdown-item">Arrived</button>
                                            </li>
                                            <li>
                                                <button wire:click="updateStatus({{ $flight->id }}, 'cancelled')"
                                                    class="dropdown-item">Cancelled</button>
                                            </li>
                                        </ul>
                                    </div>
                                </td>

                                <td>
                                    <button class="btn btn-sm btn-outline-primary"
                                        wire:click="editFlight({{ $flight->id }})" data-bs-toggle="modal"
                                        data-bs-target="#flightFormModal">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="{{ route('flights.show', $flight) }}"
                                        class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No flights found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $flights->links() }}
            </div>
        </div>
    </div>

    <!-- Flight Modal -->
    <div class="modal fade" id="flightFormModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $editMode ? 'Edit Flight' : 'Create Flight' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit="save">

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Flight Number</label>
                                    <input type="text" class="form-control" wire:model="flight_number">
                                    @error('flight_number')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Aircraft</label>
                                    <select class="form-select" wire:model="aircraft_id">
                                        <option value="">Select Aircraft</option>
                                        @foreach ($aircraft as $ac)
                                            <option value="{{ $ac->id }}">
                                                {{ $ac->airline->name }} - {{ $ac->registration_number }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('aircraft_id')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Airline</label>
                                    <select class="form-select" wire:model="airline_id">
                                        <option value="">Select Airline</option>
                                        @foreach ($airlines as $airline)
                                            <option value="{{ $airline->id }}">
                                                {{ $airline->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('airline_id')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Departure Airport</label>
                                    <input type="text" class="form-control" wire:model="departure_airport">
                                    @error('departure_airport')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Arrival Airport</label>
                                    <input type="text" class="form-control" wire:model="arrival_airport">
                                    @error('arrival_airport')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Departure Time</label>
                                    <input type="datetime-local" class="form-control"
                                        wire:model="scheduled_departure_time">
                                    @error('scheduled_departure_time')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Arrival Time</label>
                                    <input type="datetime-local" class="form-control"
                                        wire:model="scheduled_arrival_time">
                                    @error('scheduled_arrival_time')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer px-0 pb-0">
                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="bi bi-{{ $editMode ? 'pencil-square' : 'plus' }}"></i>
                                {{ $editMode ? 'Update' : 'Create' }} Flight
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @script
        <script>
            $wire.on('flight-saved', () => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('flightFormModal'));
                modal.hide();
            });
        </script>
    @endscript
</div>
