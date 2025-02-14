<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="card-title m-0">Flight Details</h2>
        <div>
            <a wire:navigate href="{{ route('flights.index') }}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to flights
            </a>
        </div>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <th class="w-25">Flight Number</th>
                                <td>{{ $flight->flight_number }}
                                    <span class="dropdown d-inline float-end">
                                        <button
                                            class="btn btn-sm btn-{{ $flight->status === 'cancelled' ? 'danger' : ($flight->status === 'arrived' ? 'success' : 'warning') }} dropdown-toggle"
                                            type="button" data-bs-toggle="dropdown">
                                            {{ ucfirst($flight->status) }}
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <button class="dropdown-item"
                                                    wire:click="updateStatus('scheduled')">
                                                    <i class="bi bi-bookmark-check-fill text-secondary"></i> Scheduled
                                                </button>
                                            </li>
                                            <li>
                                                <button class="dropdown-item"
                                                    wire:click="updateStatus('boarding')">
                                                    <i class="bi bi-hourglass-split text-warning"></i> Boarding
                                                </button>
                                            </li>
                                            <li>
                                                <button class="dropdown-item"
                                                    wire:click="updateStatus('departed')">
                                                    <i class="bi bi-check-circle text-success"></i> Departed
                                                </button>
                                            </li>
                                            <li>
                                                <button class="dropdown-item"
                                                    wire:click="updateStatus('arrived')">
                                                    <i class="bi bi-arrow-down-right-circle-fill text-success"></i> Arrived
                                                </button>
                                            </li>
                                            <li>
                                                <button class="dropdown-item"
                                                    wire:click="updateStatus('cancelled')">
                                                    <i class="bi bi-x-circle text-danger"></i> Cancelled
                                                </button>
                                            </li>
                                        </ul>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Airline</th>
                                <td>{{ $flight->airline->name }}</td>
                            </tr>
                            <tr>
                                <th>Aircraft</th>
                                <td>{{ $flight->aircraft->registration_number }} ({{ $flight->aircraft->type->name }})

                                    <span class="dropdown d-inline float-end">
                                        <button
                                            class="btn btn-sm btn-secondary dropdown-toggle"
                                            type="button" data-bs-toggle="dropdown">
                                            {{ $flight->aircraft->registration_number }}
                                        </button>
                                        <ul class="dropdown-menu">
                                            @foreach ($flight->airline->aircraft as $reg)
                                                <li>
                                                    <button class="dropdown-item" wire:click="updateRegistration('{{ $reg->id }}')">
                                                        {{ ucfirst($reg->registration_number) }}
                                                    </button>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Route</th>
                                <td>{{ $flight->departure_airport }} → {{ $flight->arrival_airport }}</td>
                            </tr>
                            <tr>
                                <th>Schedule</th>
                                <td>
                                    <div>STD: {{ $flight->scheduled_departure_time->format('d M Y H:i') }}
                                        <span class="small text-muted float-end">
                                            Flight Duration:
                                            {{ $flight->scheduled_departure_time->diff($flight->scheduled_arrival_time)->format('%h:%I') }}
                                        </span>
                                    </div>
                                    <div>STA: {{ $flight->scheduled_arrival_time->format('d M Y H:i') }}</div>
                                </td>
                            </tr>
                            @if ($flight->notes)
                                <tr>
                                    <th>Notes</th>
                                    <td>{{ $flight->notes }}</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title m-0">Load Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Passengers</label>
                                    <h4>{{ $flight->passengers->count() }} PAX</h4>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Baggage</label>
                                    <h4>{{ $flight->baggage->count() }} pcs / {{ number_format($flight->baggage->sum('weight')) }} kg
                                    </h4>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Cargo</label>
                                    <h4>{{ $flight->cargo->count() }} pcs / {{ number_format($flight->cargo->sum('weight')) }} kg</h4>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Total Deadload Weight</label>
                                    <h4>{{ number_format($flight->baggage->sum('weight') + $flight->cargo->sum('weight')) }} kg</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
