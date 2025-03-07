<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <input type="search" wire:model.live="search" class="form-control form-control-sm"
                placeholder="Search by name, ticket or seat number...">
        </div>
        <div>
            <select wire:model.live="type" class="form-select form-select-sm">
                <option value="">All Types</option>
                <option value="male">Adult Male</option>
                <option value="female">Adult Female</option>
                <option value="child">Child</option>
                <option value="infant">Infant</option>
            </select>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-primary">{{ $flight->passengers_count }} Passengers</span>
            <span class="badge bg-success">{{ $flight->accepted_count }} Accepted</span>
            <span class="badge bg-warning">{{ $flight->standby_count }} Standby</span>
            <span class="badge bg-danger">{{ $flight->offloaded_count }} Offloaded</span>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#passengerFormModal">
                <i class="bi bi-plus-circle"></i> Add Passenger
            </button>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th class="text-center">Specials</th>
                        <th>PNR</th>
                        <th>Ticket Number</th>
                        <th>Seat</th>
                        <th>Baggage</th>
                        <th>Acceptance</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($passengers as $passenger)
                        <tr wire:key="{{ $passenger->id }}">
                            <td>
                                <a href="#" wire:click.prevent="showPassengerDetails({{ $passenger->id }})"
                                    class="text-decoration-none" data-bs-toggle="modal"
                                    data-bs-target="#passengerDetailsModal">
                                    {{ $passenger->name }}
                                </a>
                            </td>
                            <td>
                                {{ ucfirst($passenger->type ?? 'N/A') }}
                            </td>
                            <td class="text-center">
                                @if ($passenger->special_requirements)
                                    @foreach ($passenger->special_requirements as $key => $value)
                                        @if ($value && $key !== 'infant_name')
                                            <i class="bi bi-{{ match ($key) {
                                                'wchr', 'wchs', 'wchc' => 'person-wheelchair',
                                                'exst' => 'door-open',
                                                'stcr' => 'h-circle-fill',
                                                'deaf' => 'ear',
                                                'blind' => 'eye-slash-fill',
                                                'dpna' => 'person-arms-up',
                                                'meda' => 'heart-pulse-fill',
                                                'infant' => 'person-standing-dress',
                                                default => 'person-check',
                                            } }}"
                                                title="{{ strtoupper($key) }}{{ $key === 'infant' ? ' - ' . $passenger->special_requirements['infant_name'] : '' }}"></i>
                                        @endif
                                    @endforeach
                                @endif
                            </td>
                            <td>{{ $passenger->pnr }}</td>
                            <td>{{ $passenger->ticket_number }}</td>
                            <td>
                                <button class="btn btn-sm btn-primary"
                                    wire:click="showSeatModal({{ $passenger->id }})" data-bs-toggle="modal"
                                    data-bs-target="#seatModal">
                                    <i class="bi bi-person-check"></i> {{ $passenger->seat->designation ?? 'Assign' }}
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
                                <button class="btn btn-sm btn-{{ $passenger->acceptance_status === 'accepted' ? 'success' : 'warning' }}"
                                    wire:click="startAcceptance({{ $passenger->id }})" data-bs-toggle="modal"
                                    data-bs-target="#acceptanceModal">
                                    <i class="bi bi-{{ $passenger->acceptance_status === 'accepted' ? 'check-lg' : 'person-check' }}"></i>
                                    {{ ucfirst($passenger->acceptance_status) }}
                                </button>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary" wire:click="editPassenger({{ $passenger->id }})"
                                    data-bs-toggle="modal" data-bs-target="#passengerFormModal">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" wire:click="deletePassenger({{ $passenger->id }})"
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
        <div>
            {{ $passengers->links() }}
        </div>
    </div>

    <!-- Passenger Form Modal -->
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
                                <input type="text" class="form-control form-control-sm" wire:model="passengerForm.name">
                                @error('passengerForm.name')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Passenger Type</label>
                                <select class="form-select form-select-sm" wire:model="passengerForm.type">
                                    <option value="">Select Type</option>
                                    <option value="male">Adult Male</option>
                                    <option value="female">Adult Female</option>
                                    <option value="child">Child (2-11 yrs)</option>
                                    <option value="infant">Infant (0-2 yrs)</option>
                                </select>
                                @error('passengerForm.type')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Ticket Number</label>
                                <input type="text" class="form-control form-control-sm" wire:model="passengerForm.ticket_number">
                                @error('passengerForm.ticket_number')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">PNR</label>
                                <input type="text" class="form-control form-control-sm" wire:model="passengerForm.pnr">
                                @error('passengerForm.pnr')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Special Requirements</label>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input"
                                        wire:model.live="passengerForm.special_requirements.infant">
                                    <label class="form-check-label">Infant</label>
                                    @error('passengerForm.special_requirements.infant')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            @if ($passengerForm['special_requirements']['infant'])
                                <div class="col-md-6">
                                    <label class="form-label">Infant Name</label>
                                    <input type="text" class="form-control form-control-sm"
                                        wire:model.live="passengerForm.special_requirements.infant_name">
                                    @error('passengerForm.special_requirements.infant_name')
                                        <div class="text-danger small">{{ 'Please enter the name of the infant' }}</div>
                                    @enderror
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-check-lg"></i> Save Passenger
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Baggage Modal -->
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
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-check-lg"></i> Save Baggage
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Passenger Details Modal -->
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
                                        <td>{{ $selectedPassenger->seat->designation }}</td>
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
                        @if ($selectedPassenger->special_requirements)
                            <div class="mt-3">
                                <h6 class="text-decoration-underline">Special Requirements</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach ($selectedPassenger->special_requirements as $key => $value)
                                        @if ($value && $key !== 'infant_name')
                                            <span class="badge bg-info">
                                                {{ strtoupper($key) }}
                                                @if ($key === 'infant')
                                                    - {{ $selectedPassenger->special_requirements['infant_name'] }}
                                                @endif
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary float-end" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Seat Assignment Modal -->
    <div class="modal fade" id="seatModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between align-items-center">
                    <h5 class="modal-title mt-0">Assign Seat</h5>
                    <div class="d-flex gap-3 ms-auto">
                        <span class="badge text-bg-secondary">Occupied</span>
                        <span class="badge text-bg-danger">Blocked</span>
                        <span class="badge text-bg-primary">Selected</span>
                        <span class="badge text-bg-light">Available</span>
                    </div>
                    <button type="button" class="btn-close mt-0" data-bs-dismiss="modal"></button>
                </div>
                <form wire:submit.prevent="assignSeat">
                    <div class="modal-body">
                        <div class="seat-container">
                            <div class="seat-grid">
                                @php
                                    $allSeats = $seats
                                        ->pluck('seats')
                                        ->flatten()
                                        ->sortBy(['row', 'column']);
                                @endphp
                                @foreach ($allSeats->groupBy('row') as $row => $rowSeats)
                                    <div class="seat-row">
                                        @foreach ($rowSeats->sortBy('column') as $seat)
                                            <div class="seat-cell 
                                                {{ $seat->is_occupied ? 'occupied' : '' }}
                                                {{ $seat->is_blocked ? 'blocked' : '' }}
                                                {{ $selectedSeat == $seat->id ? 'selected' : '' }}"
                                                @if (!$seat->is_occupied) wire:contextmenu.prevent="toggleSeatBlock({{ $seat->id }})"
                                                    wire:click="selectSeat({{ $seat->id }})" @endif
                                                title="{{ $seat->is_blocked ? 'Right click to unblock' : 'Right click to block' }}">
                                                {{ $seat->designation }}
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between align-items-center">
                        <div class="text-muted small text-start">
                            <i class="bi bi-info-circle"></i> Right click on a seat to block/unblock it
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-lg"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-sm btn-primary" @if (!$selectedSeat) disabled @endif>
                                <i class="bi bi-check-lg"></i> Assign Seat
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Acceptance Modal -->
    <div class="modal fade" id="acceptanceModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Passenger Check-in</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if ($acceptingPassenger)
                        <div class="passenger-info mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6>Passenger Information</h6>
                                @if ($acceptingPassenger->special_requirements['infant'])
                                    <div class="text-muted">
                                        <i class="bi bi-person-standing-dress"></i> Infant:
                                        {{ $acceptingPassenger->special_requirements['infant_name'] }}
                                    </div>
                                @endif
                            </div>
                            <div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-sm">
                                            <tr>
                                                <th>Name:</th>
                                                <td>{{ $acceptingPassenger->name }}</td>
                                            </tr>
                                            <tr>
                                                <th>PNR:</th>
                                                <td>{{ $acceptingPassenger->pnr }}</td>
                                            </tr>
                                            <tr>
                                                <th>Ticket:</th>
                                                <td>{{ $acceptingPassenger->ticket_number }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-sm">
                                            <tr>
                                                <th>Type:</th>
                                                <td>{{ ucfirst($acceptingPassenger->type) }}</td>
                                            </tr>
                                            <tr>
                                                <th>Seat:</th>
                                                <td>{{ $acceptingPassenger->flight_seat }}</td>
                                            </tr>
                                            <tr>
                                                <th>Baggage:</th>
                                                <td>{{ $acceptingPassenger->baggage->count() }}
                                                    {{ $acceptingPassenger->baggage->count() > 1 ? 'bags' : 'bag' }}
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="documents-section mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6>Travel Document</h6>
                                    @if (empty($acceptanceForm['documents']['travel_documents']))
                                        <button type="button" class="btn btn-sm btn-secondary" wire:click="addTravelDocument">
                                            <i class="bi bi-plus-lg"></i> Add Travel Document
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-sm btn-danger" wire:click="removeTravelDocument">
                                            <i class="bi bi-trash"></i> Remove Travel Document
                                        </button>
                                    @endif
                                </div>

                                @forelse ($acceptanceForm['documents']['travel_documents'] as $index => $document)
                                    <div class="card mb-2">
                                        <div class="card-body">
                                            <div class="row g-2">
                                                <div class="col-md-4">
                                                    <label class="form-label">Type</label>
                                                    <select class="form-select form-select-sm"
                                                        wire:model="acceptanceForm.documents.travel_documents.{{ $index }}.type">
                                                        <option value="passport">Passport</option>
                                                        <option value="national_id">National ID</option>
                                                        <option value="residence_permit">Residence Permit</option>
                                                    </select>
                                                    @error('acceptanceForm.documents.travel_documents.{{ $index }}.type')
                                                        <div class="text-danger small">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Number</label>
                                                    <input type="text" class="form-control form-control-sm"
                                                        wire:model="acceptanceForm.documents.travel_documents.{{ $index }}.number">
                                                    @error('acceptanceForm.documents.travel_documents.{{ $index }}.number')
                                                        <div class="text-danger small">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Nationality</label>
                                                    <input type="text" class="form-control form-control-sm"
                                                        wire:model="acceptanceForm.documents.travel_documents.{{ $index }}.nationality">
                                                    @error('acceptanceForm.documents.travel_documents.{{ $index }}.nationality')
                                                        <div class="text-danger small">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Issuing Country</label>
                                                    <input type="text" class="form-control form-control-sm"
                                                        wire:model="acceptanceForm.documents.travel_documents.{{ $index }}.issuing_country">
                                                    @error('acceptanceForm.documents.travel_documents.{{ $index }}.issuing_country')
                                                        <div class="text-danger small">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Issue Date</label>
                                                    <input type="date" class="form-control form-control-sm"
                                                        wire:model="acceptanceForm.documents.travel_documents.{{ $index }}.issue_date">
                                                    @error('acceptanceForm.documents.travel_documents.{{ $index }}.issue_date')
                                                        <div class="text-danger small">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Expiry Date</label>
                                                    <input type="date" class="form-control form-control-sm"
                                                        wire:model="acceptanceForm.documents.travel_documents.{{ $index }}.expiry_date">
                                                    @error('acceptanceForm.documents.travel_documents.{{ $index }}.expiry_date')
                                                        <div class="text-danger small">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-muted">No travel documents added</div>
                                @endforelse
                            </div>

                            <div class="special-requirements mb-2">
                                <h6>Special Requirements</h6>
                                <div class="row g-2">
                                    @foreach (['wchr', 'wchs', 'wchc', 'exst', 'stcr', 'deaf', 'blind', 'dpna', 'meda'] as $requirement)
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input"
                                                    wire:model.live="acceptanceForm.special_requirements.{{ $requirement }}">
                                                <label class="form-check-label">
                                                    <i
                                                        class="bi bi-{{ match ($requirement) {
                                                            'wchr', 'wchs', 'wchc' => 'person-wheelchair',
                                                            'exst' => 'door-open',
                                                            'stcr' => 'h-circle-fill',
                                                            'deaf' => 'ear',
                                                            'blind' => 'eye-slash-fill',
                                                            'dpna' => 'person-arms-up',
                                                            'meda' => 'heart-pulse-fill',
                                                            default => 'person-check',
                                                        } }}"></i>
                                                    {{ strtoupper($requirement) }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                    @endif
                </div>
                <div class="modal-footer d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg"></i> Close
                    </button>
                    <button type="button" class="btn btn-sm btn-success" wire:click="acceptPassenger">
                        <i class="bi bi-check-lg"></i> Accept Passenger
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .seat-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
        }

        .seat-grid {
            display: table;
            border-spacing: 2px;
            border-collapse: separate;
            margin: 0 auto;
        }

        .seat-row {
            display: table-row;
        }

        .row-number {
            display: table-cell;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            padding-right: 4px;
            font-size: 0.80rem;
        }

        .seat-cell {
            display: table-cell;
            cursor: pointer;
            padding: 2px;
            border-radius: 3px;
            border: 1px solid #dee2e6;
            transition: all 0.2s;
            text-align: center;
            width: 30px;
            height: 30px;
            min-width: 30px;
            min-height: 30px;
            vertical-align: middle;
            font-size: 10px;
            border-radius: 4px;
            font-weight: 100;
        }

        .seat-cell:hover:not(.occupied):not(.blocked) {
            background-color: #e9ecef;
        }

        .seat-cell.occupied {
            background-color: #6c757d;
            color: white;
            cursor: not-allowed;
        }

        .seat-cell.blocked {
            background-color: #dc3545;
            color: white;
            cursor: not-allowed;
        }

        .seat-cell.selected {
            background-color: #0d6efd;
            color: white;
            border-color: #0d6efd;
        }
    </style>

    @script
        <script>
            $wire.on('passenger-saved', () => {
                bootstrap.Modal.getInstance(document.getElementById('passengerFormModal')).hide();
            });
            $wire.on('baggage-saved', () => {
                bootstrap.Modal.getInstance(document.getElementById('baggageModal')).hide();
            });
            $wire.on('seat-saved', () => {
                bootstrap.Modal.getInstance(document.getElementById('seatModal')).hide();
            });
            $wire.on('passenger-accepted', () => {
                bootstrap.Modal.getInstance(document.getElementById('acceptanceModal')).hide();
            });
        </script>
    @endscript
</div>
