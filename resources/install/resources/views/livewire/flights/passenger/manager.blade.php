<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="col-md-4">
            <input type="search" wire:model.live="search" class="form-control form-control-sm"
                placeholder="Search by name, ticket or seat number...">
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-primary">{{ $passengers->count() }} Passengers</span>
            <span class="badge bg-success">{{ $passengers->where('acceptance_status', 'accepted')->count() }} Accepted</span>
            <span class="badge bg-warning">{{ $passengers->where('acceptance_status', 'standby')->count() }} Standby</span>
            <span class="badge bg-danger">{{ $passengers->where('acceptance_status', 'offloaded')->count() }} Offloaded</span>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-primary">{{ $passengers->count() }} Passengers</span>
            <span class="badge bg-success">{{ $passengers->where('boarding_status', 'boarded')->count() }} Boarded</span>
            <span class="badge bg-danger">{{ $passengers->where('boarding_status', 'unboarded')->count() }} Unboarded</span>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary btn-sm" wire:click="$toggle('showForm')" data-bs-toggle="modal"
                data-bs-target="#passengerFormModal">
                <i class="bi bi-plus-circle"></i> Add Passenger
            </button>
        </div>
    </div>

    <div class="card-body table-responsive">
        <table class="table table-sm table-hover">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    @unless ($flight)
                        <th>Flight</th>
                    @endunless
                    <th>Ticket Number</th>
                    <th>Seat</th>
                    <th>Baggage</th>
                    <th>Acceptance</th>
                    <th>Boarding</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($passengers as $passenger)
                    <tr wire:key="{{ $passenger->id }}">
                        <td>
                            <a href="#" wire:click.prevent="showPassengerDetails({{ $passenger->id }})"
                                class="text-decoration-none text-reset" data-bs-toggle="modal"
                                data-bs-target="#passengerDetailsModal">
                                {{ $passenger->name }}
                            </a>
                        </td>
                        <td>{{ ucfirst($passenger->type ?? 'N/A') }}</td>
                        @unless ($flight)
                            <td>
                                @if ($passenger->flight)
                                    {{ $passenger->flight->flight_number }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        @endunless
                        <td>{{ $passenger->ticket_number }}</td>
                        <td>
                            <button class="btn btn-sm {{ $passenger->seat ? 'btn-success' : 'btn-outline-primary' }}"
                                wire:click="assignSeat({{ $passenger->id }})" data-bs-toggle="modal"
                                data-bs-target="#seatModal">
                                @if ($passenger->seat)
                                    <i class="bi bi-person-check"></i> {{ $passenger->seat->designation }}
                                @else
                                    <i class="bi bi-person-plus"></i> Assign Seat
                                @endif
                            </button>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-link text-decoration-none text-reset"
                                wire:click="editBaggage({{ $passenger->id }})" data-bs-toggle="modal"
                                data-bs-target="#baggageModal">
                                {{ $passenger->baggage_count }} <i class="bi bi-luggage-fill"></i> pcs
                            </button>
                        </td>
                        <td>
                            <div class="dropdown d-inline">
                                <button
                                    class="btn btn-sm btn-{{ $passenger->acceptance_status === 'accepted' ? 'success' : ($passenger->acceptance_status === 'standby' ? 'warning' : 'danger') }} dropdown-toggle"
                                    type="button" data-bs-toggle="dropdown">
                                    {{ str_replace('_', ' ', ucwords($passenger->acceptance_status)) }}
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <button class="dropdown-item" wire:click="updateAcceptanceStatus({{ $passenger->id }}, 'booked')">
                                            <i class="bi bi-bookmark-check-fill text-secondary"></i> Booked
                                        </button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item" wire:click="updateAcceptanceStatus({{ $passenger->id }}, 'standby')">
                                            <i class="bi bi-hourglass-split text-warning"></i> Standby
                                        </button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item"
                                            wire:click="updateAcceptanceStatus({{ $passenger->id }}, 'accepted')">
                                            <i class="bi bi-check-circle text-success"></i> Accepted
                                        </button>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <button class="dropdown-item"
                                            wire:click="updateAcceptanceStatus({{ $passenger->id }}, 'offloaded')">
                                            <i class="bi bi-x-circle text-danger"></i> Offloaded
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </td>
                        <td>
                            <div class="dropdown d-inline">
                                <button
                                    class="btn btn-sm btn-{{ $passenger->boarding_status === 'boarded' ? 'success' : ($passenger->boarding_status === 'unboarded' ? 'danger' : 'warning') }} dropdown-toggle"
                                    type="button" data-bs-toggle="dropdown">
                                    {{ str_replace('_', ' ', ucwords($passenger->boarding_status)) }}
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <button class="dropdown-item" wire:click="updateBoardingStatus({{ $passenger->id }}, 'boarded')">
                                            <i class="bi bi-check-circle text-success"></i> Boarded
                                        </button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item"
                                            wire:click="updateBoardingStatus({{ $passenger->id }}, 'unboarded')">
                                            <i class="bi bi-x-circle text-danger"></i> Unboarded
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-link" wire:click="edit({{ $passenger->id }})"
                                data-bs-toggle="modal" data-bs-target="#passengerFormModal">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-link text-danger" wire:click="delete({{ $passenger->id }})"
                                wire:confirm="Are you sure you want to remove this passenger?">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $flight ? 6 : 7 }}" class="text-center">No passengers found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center">
        {{ $passengers->links() }}
    </div>

    <!-- Modal -->
    <div class="modal fade" id="passengerFormModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form wire:submit="save">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editingPassenger ? 'Edit' : 'Add' }} Passenger</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control form-control-sm" wire:model="form.name">
                                @error('form.name')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Passenger Type</label>
                                <select class="form-select form-select-sm" wire:model="form.type">
                                    <option value="">Select Type</option>
                                    <option value="male">Adult Male</option>
                                    <option value="female">Adult Female</option>
                                    <option value="child">Child (2-11 yrs)</option>
                                    <option value="infant">Infant (0-2 yrs)</option>
                                </select>
                                @error('form.type')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Ticket Number</label>
                                <input type="text" class="form-control form-control-sm" wire:model="form.ticket_number">
                                @error('form.ticket_number')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary">Save Passenger</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="baggageModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Baggage</h5>
                </div>
                <form wire:submit="saveBaggage">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Pieces</label>
                                <input type="number" class="form-control form-control-sm" wire:model="pieces">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Weight</label>
                                <input type="number" class="form-control form-control-sm" wire:model="weight">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary">Save Baggage</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="passengerDetailsModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Passenger Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if ($selectedPassenger)
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-decoration-underline mb-3">Personal Information</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <th>Name:</th>
                                        <td>{{ $selectedPassenger->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Type:</th>
                                        <td>{{ ucfirst($selectedPassenger->type) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Ticket Number:</th>
                                        <td>{{ $selectedPassenger->ticket_number }}</td>
                                    </tr>
                                    <tr>
                                        <th>Seat Number:</th>
                                        <td>{{ $selectedPassenger->seat_number }}</td>
                                    </tr>
                                    <tr>
                                        <th>Acceptance Status:</th>
                                        <td>
                                            <span
                                                class="badge bg-{{ $selectedPassenger->acceptance_status === 'accepted'
                                                    ? 'success'
                                                    : ($selectedPassenger->acceptance_status === 'standby'
                                                        ? 'warning'
                                                        : 'danger') }}">
                                                {{ ucfirst($selectedPassenger->acceptance_status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Boarding Status:</th>
                                        <td>
                                            <span
                                                class="badge bg-{{ $selectedPassenger->boarding_status === 'boarded' ? 'success' : 'danger' }}">
                                                {{ ucfirst($selectedPassenger->boarding_status) }}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-decoration-underline mb-3">Baggage Information</h6>
                                @if ($selectedPassenger->baggage->count() > 0)
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Tag Number</th>
                                                <th>Weight</th>
                                                <th>Container</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($selectedPassenger->baggage as $bag)
                                                <tr>
                                                    <td>{{ $bag->tag_number }}</td>
                                                    <td>{{ number_format($bag->weight) }} kg</td>
                                                    <td>
                                                        @if ($bag->container)
                                                            {{ $bag->container->container_number }}
                                                        @else
                                                            <span class="text-muted">Not assigned</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span
                                                            class="badge bg-{{ $bag->status === 'loaded' ? 'success' : ($bag->status === 'checked' ? 'warning' : 'danger') }}">
                                                            {{ ucfirst($bag->status) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <div class="text-muted">No baggage found</div>
                                @endif
                            </div>
                        </div>
                        @if ($selectedPassenger->notes)
                            <div class="mt-3">
                                <h6 class="text-decoration-underline">Notes</h6>
                                <p class="mb-0">{{ $selectedPassenger->notes }}</p>
                            </div>
                        @endif
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary float-end" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Seat Assignment Modal -->
    <div class="modal fade" id="seatModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        Assign Seat - {{ $selectedPassenger?->name }}
                        @if ($selectedPassenger?->seat)
                            <small class="text-muted">(Current: {{ $selectedPassenger->seat->designation }})</small>
                        @endif
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if ($seats && $seats->count() > 0)
                        <div class="seat-map border">
                            @php
                                $columns = $seats->pluck('column')->unique()->sort();
                                $rows = $seats->pluck('row')->unique()->sort();
                            @endphp

                            <div class="seat-table">
                                <!-- Column Headers -->
                                <div class="seat-row header">
                                    @foreach ($columns as $column)
                                        <div class="seat-cell header">{{ $column }}</div>
                                    @endforeach
                                </div>

                                <!-- Seat Rows -->
                                @foreach ($rows as $row)
                                    <div class="seat-row">
                                        @foreach ($columns as $column)
                                            @php
                                                $seat = $seats->where('row', $row)->where('column', $column)->first();
                                            @endphp
                                            <div class="seat-cell {{ !$seat ? 'empty' : '' }} 
                                                {{ $seat && $seat->is_occupied ? 'bg-secondary bi bi-person-fill' : '' }}
                                                {{ $seat && $seat->is_blocked ? 'bg-danger' : '' }}
                                                {{ $seat && $selectedPassenger && $selectedPassenger->seat_id === $seat->id ? 'current-seat bg-success' : '' }}"
                                                @if ($seat && !$seat->is_occupied && !$seat->is_blocked) wire:click="selectSeat({{ $seat->id }})"
                                                    style="{{ $selectedSeat == $seat->id ? 'background-color: #0d6efd; color: white;' : '' }}" @endif
                                                {{ $seat && ($seat->is_occupied || $seat->is_blocked) ? 'style=cursor:not-allowed' : '' }}>
                                                @if ($seat)
                                                    <small>{{ $seat->designation }}</small>
                                                @else
                                                    <small>-</small>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mt-3">
                            <div class="d-flex gap-2 align-items-center small">
                                <span class="badge bg-warning">Exit Row</span>
                                <span class="badge bg-secondary">Occupied</span>
                                <span class="badge bg-outline-primary">Available</span>
                                <span class="badge bg-success text-white">Current Seat</span>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info">
                            No seats configured for this aircraft.
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-danger"
                        wire:click="removeSeatAssignment"
                        @if (!$selectedSeat) disabled @endif>
                        Remove Seat
                    </button>
                    <button type="button" class="btn btn-sm btn-secondary"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-sm btn-primary"
                        wire:click="saveSeatAssignment">Save</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .seat-table {
            display: table;
            margin: 0 auto;
            border-spacing: 2px;
            border-collapse: separate;
        }

        .seat-row {
            display: table-row;
        }

        .seat-row.header {
            font-weight: bold;
        }

        .seat-cell {
            display: table-cell;
            cursor: pointer;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
            transition: background-color 0.2s;
            text-align: center;
            min-width: 40px;
            height: 40px;
            vertical-align: middle;
            position: relative;
        }

        .seat-cell.header {
            border: none;
            font-weight: bold;
            cursor: default;
            background-color: #f8f9fa;
        }

        .seat-cell.empty {
            border: none;
            cursor: default;
        }

        .seat-cell:not(.header):not(.empty):hover:not(.bg-secondary):not(.bg-danger) {
            background-color: #e9ecef;
        }

        .seat-cell.bg-secondary {
            color: white;
        }

        .seat-cell.current-seat {
            border: 2px solid #0d6efd;
        }

        .seat-cell i {
            font-size: 0.875rem;
            position: absolute;
            bottom: 2px;
            right: 2px;
        }

        .seat-cell small {
            font-size: 0.75rem;
        }
    </style>

    @script
        <script>
            $wire.on('passenger-saved', () => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('passengerFormModal'));
                modal.hide();
            });
            $wire.on('baggage-saved', () => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('baggageModal'));
                modal.hide();
            });
            $wire.on('seat-saved', () => {
                const modal = new bootstrap.Modal(document.getElementById('seatModal'));
                modal.hide();
            });
        </script>
    @endscript
</div>
