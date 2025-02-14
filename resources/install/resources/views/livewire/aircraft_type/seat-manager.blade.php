<div class="mb-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Seat Configuration</h3>
            <button class="btn btn-sm btn-primary" wire:click="$toggle('showBulkCreateModal')" data-bs-toggle="modal"

                data-bs-target="#bulkCreateModal">
                <i class="bi bi-plus-lg"></i> Add Seats
            </button>
        </div>

        <div class="card-body">
            <div class="row">
                @foreach ($cabinZones as $zone)
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">

                                <h5 class="card-title m-0">{{ $zone->name }}</h5>
                                <div>
                                    <button class="btn btn-sm btn-danger"
                                        wire:click="deleteZoneSeats({{ $zone->id }})"
                                        wire:confirm="Are you sure you want to delete all seats in this zone?">
                                        <i class="bi bi-trash"></i> Delete Zone Seats
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="seat-grid">
                                    @if (isset($seatsByZone[$zone->id]))
                                        @php
                                            $seats = $seatsByZone[$zone->id];
                                            $rows = $seats->groupBy('row');
                                        @endphp
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead>
                                                    <tr>
                                                        @foreach ($seats->pluck('column')->unique()->sort() as $column)
                                                            <th class="text-center">{{ $column }}</th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($rows as $row => $rowSeats)
                                                        <tr>
                                                            @foreach ($seats->pluck('column')->unique()->sort() as $column)
                                                                @php
                                                                    $seat = $rowSeats->firstWhere('column', $column);
                                                                @endphp
                                                                <td class="text-center">
                                                                    @if ($seat)
                                                                        <div class="seat-cell 
                                                                    {{ $seat->is_blocked ? 'bg-danger' : '' }} 
                                                                    {{ $seat->is_exit ? 'bg-success' : '' }}
                                                                    {{ $seat->type === 'business' ? 'bg-info' : '' }}
                                                                    {{ $seat->type === 'first' ? 'bg-warning' : '' }}"
                                                                            wire:click="editSeat({{ $seat->id }})"
                                                                            data-bs-toggle="modal" data-bs-target="#editSeatModal">
                                                                            {{ $seat->designation }}
                                                                        </div>
                                                                    @else
                                                                        <div class="seat-cell empty">-</div>
                                                                    @endif
                                                                </td>
                                                            @endforeach
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center py-3">
                                            <p class="text-muted">No seats configured for this zone</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Bulk Create Modal -->
            <div class="modal fade" id="bulkCreateModal" tabindex="-1" wire:ignore.self>
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form wire:submit="createSeats">
                            <div class="modal-header">
                                <h5 class="modal-title">Add Seats</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Start Row</label>
                                            <input type="number" class="form-control form-control-sm"
                                                wire:model="bulkForm.start_row" min="1" required>
                                            @error('bulkForm.start_row')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">

                                        <div class="mb-3">
                                            <label class="form-label">End Row</label>
                                            <input type="number" class="form-control form-control-sm"
                                                wire:model="bulkForm.end_row" min="1" required>
                                            @error('bulkForm.end_row')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Columns</label>
                                    <div class="row">
                                        @foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K'] as $letter)
                                            <div class="col-auto">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input"
                                                        wire:model="bulkForm.columns"
                                                        value="{{ $letter }}"
                                                        id="col_{{ $letter }}">
                                                    <label class="form-check-label"
                                                        for="col_{{ $letter }}">{{ $letter }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                        @error('bulkForm.columns')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Cabin Zone</label>
                                    <select class="form-select form-select-sm"
                                        wire:model="bulkForm.cabin_zone_id" required>
                                        <option value="">Select Zone</option>
                                        @foreach ($cabinZones as $zone)
                                            <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('bulkForm.cabin_zone_id')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Seat Type</label>
                                    <select class="form-select form-select-sm"
                                        wire:model="bulkForm.type" required>
                                        <option value="economy">Economy</option>
                                        <option value="business">Business</option>
                                        <option value="first">First</option>
                                    </select>
                                    @error('bulkForm.type')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-sm btn-secondary"
                                    data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-sm btn-primary">Create Seats</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit Seat Modal -->
            <div class="modal fade" id="editSeatModal" tabindex="-1" wire:ignore.self>
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form wire:submit="updateSeat">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Seat {{ $editingSeat?->designation }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Cabin Zone</label>
                                    <select class="form-select form-select-sm"
                                        wire:model="seatForm.cabin_zone_id" required>
                                        <option value="">Select Zone</option>
                                        @foreach ($cabinZones as $zone)
                                            <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Seat Type</label>
                                    <select class="form-select form-select-sm"
                                        wire:model="seatForm.type" required>
                                        <option value="economy">Economy</option>
                                        <option value="business">Business</option>
                                        <option value="first">First</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input"
                                            wire:model="seatForm.is_exit" id="is_exit">
                                        <label class="form-check-label" for="is_exit">Exit Row</label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input"
                                            wire:model="seatForm.is_blocked" id="is_blocked">
                                        <label class="form-check-label" for="is_blocked">Blocked</label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Notes</label>
                                    <textarea class="form-control form-control-sm"
                                        wire:model="seatForm.notes" rows="2"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-sm btn-danger"
                                    wire:click="deleteSeat({{ $editingSeat?->id }})"
                                    wire:confirm="Are you sure you want to delete this seat?"
                                    data-bs-dismiss="modal">
                                    Delete Seat
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary"
                                    data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-sm btn-primary">Update Seat</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .seat-cell {
                cursor: pointer;
                padding: 5px;
                border-radius: 4px;
                transition: background-color 0.2s;
            }

            .seat-cell:hover {
                background-color: #e9ecef;
            }

            .seat-cell.empty {
                cursor: default;
            }
        </style>

        @script
            <script>
                $wire.on('seat-saved', () => {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editSeatModal'));
                    modal.hide();
                });
            </script>
        @endscript
    </div>
