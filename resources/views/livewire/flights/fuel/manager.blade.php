<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="card-title m-0">Fuel Record Details</h4>
            <button type="button" class="btn btn-sm btn-primary m-0" data-bs-toggle="modal"
                data-bs-target="#fuelModal">
                <i class="bi bi-fuel-pump"></i> {{ $flight->fuel ? 'Update' : 'Add' }} Fuel
            </button>
        </div>
    </div>
    <div class="card-body">
        @if ($fuel)
            <div class="row mb-4">
                <div class="col-md-6">
                    <h4>Flight Information</h4>
                    <p class="mb-1">
                        <strong>Flight Number:</strong>
                        <a wire:navigate href="{{ route('flights.show', $fuel->flight) }}" class="text-decoration-none">
                            {{ $fuel->flight->flight_number }}
                        </a>
                    </p>
                    <p class="mb-1">
                        <strong>Route:</strong>
                        {{ $fuel->flight->departure_airport }} - {{ $fuel->flight->arrival_airport }}
                    </p>
                    <p class="mb-1">
                        <strong>Scheduled Departure:</strong>
                        {{ $fuel->flight->scheduled_departure_time->format('D, M d, Y H:i') }}
                    </p>
                </div>
                <div class="col-md-6">
                    <h4>Total Fuel</h4>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="display-6 text-primary mb-2">
                            {{ number_format($fuel->block_fuel) }} kg
                        </div>
                        <span class="badge bg-warning text-muted text-sm">
                            Max Capacity: {{ number_format($fuel->flight->aircraft->type->max_fuel_capacity) }} kg
                        </span>
                    </div>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar bg-primary" role="progressbar"
                            style="width: {{ ($fuel->block_fuel / $fuel->flight->aircraft->type->max_fuel_capacity ?? 0) * 100 }}%">
                            {{ number_format(($fuel->block_fuel / $fuel->flight->aircraft->type->max_fuel_capacity ?? 0) * 100, 1) }}%
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title m-0">Primary Fuel</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Block Fuel:</span>
                                    <strong>{{ number_format($fuel->block_fuel ?? 0) }} kg</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Take Off Fuel:</span>
                                    <strong>{{ number_format($fuel->take_off_fuel ?? 0) }} kg</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Taxi Fuel:</span>
                                    <strong>{{ number_format($fuel->taxi_fuel ?? 0) }} kg</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Trip Fuel:</span>
                                    <strong>{{ number_format($fuel->trip_fuel ?? 0) }} kg</strong>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title m-0">Crew & Pantry</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Crew:</span>
                                    <strong>{{ $fuel->crew ?? '-' }}</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Pantry:</span>
                                    <strong>{{ $fuel->pantry ?? '-' }}</strong>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <p class="alert alert-success">Fuel data not found for this flight.</p>
        @endif
    </div>

    <div>
        <!-- Bootstrap Modal -->
        <div class="modal fade" id="fuelModal" tabindex="-1" aria-labelledby="fuelModalLabel" aria-hidden="true"
            wire:ignore.self>
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="fuelModalLabel">{{ $flight->fuel ? 'Update' : 'Add' }} Fuel Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form wire:submit="save">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Block Fuel (kg)</label>
                                        <input type="number" wire:model="block_fuel"
                                            min="0" max="{{ $flight->aircraft->type->max_fuel_capacity }}"
                                            class="form-control form-control-sm" required>
                                        @error('block_fuel')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Taxi Fuel (kg)</label>
                                        <input type="number" wire:model="taxi_fuel" class="form-control form-control-sm" required>
                                        @error('taxi_fuel')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Trip Fuel (kg)</label>
                                        <input type="number" wire:model="trip_fuel" class="form-control form-control-sm" required>
                                        @error('trip_fuel')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Crew</label>
                                        <select wire:model="crew" class="form-select form-select-sm" required>
                                            <option value="" selected>Select Crew</option>
                                            @foreach ($crewOptions as $crew)
                                                <option value="{{ $crew }}">
                                                    {{ $crew }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('crew')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Pantry</label>
                                        <select class="form-select form-select-sm" wire:model="pantry">
                                            <option value="">Select Pantry</option>
                                            @foreach ($pantryOptions as $code => $pantry)
                                                <option value="{{ $code }}">{{ $pantry['name'] }}
                                                    ({{ $pantry['weight'] }}kg)
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('pantry')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-lg"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="bi bi-save"></i> Save Fuel Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@script
    <script>
        $wire.on('close-modal', () => {
            bootstrap.Modal.getInstance(document.getElementById('fuelModal')).hide();
        });
    </script>
@endscript
</div>
