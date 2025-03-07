<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Flight Schedules</h5>
            <div class="d-flex justify-content-between align-items-center gap-4">
                <div>
                    <input wire:model.live="search" type="text" class="form-control form-control-sm" placeholder="Search...">
                </div>
                <div>
                    <select wire:model.live="airline_id" class="form-select form-select-sm">
                        <option value="">All Airlines</option>
                        @foreach ($airlines as $airline)
                            <option value="{{ $airline->id }}">{{ $airline->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select wire:model.live="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <button wire:click="createSchedule" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-circle"></i> Create Schedule
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>Flight</th>
                            <th>Route</th>
                            <th>Aircraft</th>
                            <th>Times</th>
                            <th>Period</th>
                            <th>Days</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schedules as $schedule)
                            <tr>
                                <td>
                                    <span class="badge bg-primary">{{ $schedule->airline->iata_code }}</span>
                                    {{ $schedule->flight_number }}
                                </td>
                                <td>{{ $schedule->departure_airport }} - {{ $schedule->arrival_airport }}</td>
                                <td>
                                    @if ($schedule->aircraft)
                                        {{ $schedule->aircraft->registration_number }}
                                    @else
                                        <div class="text-warning">Not assigned</div>
                                    @endif
                                </td>
                                <td>
                                    {{ $schedule->departure_time->format('H:i') }} -
                                    {{ $schedule->arrival_time->format('H:i') }}
                                </td>
                                <td>
                                    {{ $schedule->start_date->format('d M Y') }} -
                                    {{ $schedule->end_date->format('d M Y') }}
                                </td>
                                <td>
                                    <small>
                                        {{ implode(', ', array_map(fn($day) => substr($dayOptions[$day], 0, 3), $schedule->days_of_week)) }}
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $schedule->is_active ? 'success' : 'danger' }}">
                                        {{ $schedule->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button wire:click="editSchedule({{ $schedule->id }})" class="btn btn-sm btn-primary"
                                            title="Edit Schedule" @disabled(!$schedule->is_active)>
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <button wire:click="showFlights({{ $schedule->id }})" class="btn btn-sm btn-info"
                                            title="View Flights" @disabled(!$schedule->is_active)>
                                            <i class="bi bi-airplane-engines"></i>
                                        </button>
                                        <button wire:click="generateFlights({{ $schedule->id }})" class="btn btn-sm btn-success"
                                            title="Generate Flights" @disabled(!$schedule->is_active)>
                                            <i class="bi bi-database-add"></i>
                                        </button>
                                        <button wire:click="toggleStatus({{ $schedule->id }})"
                                            class="btn btn-sm btn-{{ $schedule->is_active ? 'danger' : 'success' }}"
                                            title="{{ $schedule->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="bi bi-{{ $schedule->is_active ? 'ban' : 'check-circle-fill' }}"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No schedules found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $schedules->links() }}
            </div>
        </div>
    </div>

    <!-- Schedule Modal -->
    <div class="modal fade" wire:ignore.self id="scheduleModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $editMode ? 'Edit' : 'Create' }} Flight Schedule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit="save">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Airline</label>
                                <select wire:model="airline_id" class="form-select form-select-sm" required>
                                    <option value="">Select Airline</option>
                                    @foreach ($airlines as $airline)
                                        <option value="{{ $airline->id }}">{{ $airline->name }}</option>
                                    @endforeach
                                </select>
                                @error('airline_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Flight Number</label>
                                <input wire:model="flight_number" type="text" class="form-control form-control-sm" required>
                                @error('flight_number')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Aircraft (Optional)</label>
                                <select wire:model="aircraft_id" class="form-select form-select-sm">
                                    <option value="">No Aircraft Selected</option>
                                    @foreach ($aircraft as $ac)
                                        <option value="{{ $ac->id }}">{{ $ac->registration_number }} ({{ $ac->type->code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('aircraft_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch mt-4">
                                    <input wire:model="is_active" class="form-check-input" type="checkbox" id="activeSwitch">
                                    <label class="form-check-label" for="activeSwitch">Active</label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Departure Airport</label>
                                <input wire:model="departure_airport" type="text" class="form-control form-control-sm" maxlength="3"
                                    required>
                                @error('departure_airport')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Arrival Airport</label>
                                <input wire:model="arrival_airport" type="text" class="form-control form-control-sm" maxlength="3"
                                    required>
                                @error('arrival_airport')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Departure Time</label>
                                <input wire:model="departure_time" type="time" class="form-control form-control-sm" required>
                                @error('departure_time')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Arrival Time</label>
                                <input wire:model="arrival_time" type="time" class="form-control form-control-sm" required>
                                @error('arrival_time')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Start Date</label>
                                <input wire:model="start_date" type="date" class="form-control form-control-sm" required>
                                @error('start_date')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">End Date</label>
                                <input wire:model="end_date" type="date" class="form-control form-control-sm" required>
                                @error('end_date')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Days of Week</label>
                            <div class="d-flex flex-wrap gap-3">
                                @foreach ($dayOptions as $value => $day)
                                    <div class="form-check">
                                        <input wire:model="days_of_week" class="form-check-input form-check-input-sm" type="checkbox"
                                            value="{{ $value }}" id="day-{{ $value }}">
                                        <label class="form-check-label" for="day-{{ $value }}">
                                            {{ $day }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('days_of_week')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button wire:click="save" class="btn btn-sm btn-primary">
                        <i class="bi bi-{{ $editMode ? 'pencil-square' : 'plus-circle' }}"></i> {{ $editMode ? 'Update' : 'Create' }}
                        Schedule
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Flights Modal -->
    <div class="modal fade" wire:ignore.self id="flightsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        @if ($selectedSchedule)
                            Flights for {{ $selectedSchedule->airline->iata_code }} {{ $selectedSchedule->flight_number }}
                            ({{ $selectedSchedule->departure_airport }} - {{ $selectedSchedule->arrival_airport }})
                        @else
                            Schedule Flights
                        @endif
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if ($scheduleFlights && count($scheduleFlights) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Flight</th>
                                        <th>Date</th>
                                        <th>Departure</th>
                                        <th>Arrival</th>
                                        <th>Aircraft</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($scheduleFlights as $flight)
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary">{{ $flight->airline->iata_code }}</span>
                                                {{ $flight->flight_number }}
                                            </td>
                                            <td>{{ $flight->scheduled_departure_time->format('d M Y') }}</td>
                                            <td>
                                                {{ $flight->departure_airport }}
                                                <span
                                                    class="small text-muted">({{ $flight->scheduled_departure_time->format('H:i') }})
                                                </span>
                                            </td>
                                            <td>
                                                {{ $flight->arrival_airport }}
                                                <span
                                                    class="small text-muted">({{ $flight->scheduled_arrival_time->format('H:i') }})</span>
                                            </td>
                                            <td>
                                                @if ($flight->aircraft)
                                                    {{ $flight->aircraft->registration_number }}
                                                @else
                                                    <span class="text-muted">Not assigned</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span
                                                    class="badge bg-{{ $flight->status === 'scheduled'
                                                        ? 'secondary'
                                                        : ($flight->status === 'boarding'
                                                            ? 'warning'
                                                            : ($flight->status === 'departed'
                                                                ? 'info'
                                                                : ($flight->status === 'arrived'
                                                                    ? 'success'
                                                                    : 'danger'))) }}">
                                                    {{ ucfirst($flight->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('flights.show', $flight->id) }}"
                                                    class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            No flights have been generated for this schedule yet.
                            <button wire:click="generateFlights({{ $selectedSchedule ? $selectedSchedule->id : 0 }})"
                                class="btn btn-sm btn-primary ms-2"
                                {{ $selectedSchedule && $selectedSchedule->is_active ? '' : 'disabled' }}>
                                Generate Flights Now
                            </button>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                    @if ($selectedSchedule)
                        <a href="{{ route('flights.index') }}?schedule={{ $selectedSchedule->id }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-eye"></i> View All in Flights Manager
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @script
        <script>
            const scheduleModal = new bootstrap.Modal('#scheduleModal');
            const flightsModal = new bootstrap.Modal('#flightsModal');

            $wire.on('schedule-saved', () => {
                scheduleModal.hide();
            });

            $wire.$watch('showModal', (value) => {
                if (value) {
                    scheduleModal.show();
                } else {
                    scheduleModal.hide();
                }
            });

            $wire.$watch('showFlightsModal', (value) => {
                if (value) {
                    flightsModal.show();
                } else {
                    flightsModal.hide();
                }
            });

            document.getElementById('flightsModal').addEventListener('hidden.bs.modal', () => {
                $wire.set('showFlightsModal', false);
            });

            document.getElementById('scheduleModal').addEventListener('hidden.bs.modal', () => {
                $wire.set('showModal', false);
            });
        </script>
    @endscript
</div>
