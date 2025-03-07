<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title m-0">Boarding Control</h4>
        <div class="d-flex justify-content-between align-items-center gap-2 m-0">
            <button class="btn btn-sm btn-{{ $boardedCount === $totalCount ? 'success' : 'primary' }}">
                <i class="bi bi-person-check"></i> Boarded: {{ $boardedCount }}/{{ $totalCount }}
            </button>
            <div class="d-flex gap-2">
                <span class="badge bg-primary">{{ $passengers->count() }} Passengers</span>
                <span class="badge bg-success">{{ $passengers->where('boarding_status', 'boarded')->count() }} Boarded</span>
                <span class="badge bg-danger">{{ $passengers->where('boarding_status', 'unboarded')->count() }} Unboarded</span>
            </div>
        </div>
    </div>

    <div class="card-body">
        <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
                <button class="nav-link {{ $tab === 'seat' ? 'active' : '' }}" wire:click="setTab('seat')">
                    <i class="bi bi-airplane-seats"></i> Board by Seat
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link {{ $tab === 'list' ? 'active' : '' }}" wire:click="setTab('list')">
                    <i class="bi bi-list-check"></i> Passenger List
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link {{ $tab === 'boarded' ? 'active' : '' }}" wire:click="setTab('boarded')">
                    <i class="bi bi-person-check"></i> Boarded Passengers
                </button>
            </li>
        </ul>

        @if ($tab === 'seat')
            <div class="row">
                <div class="col-md-6">
                    <form wire:submit="boardBySeat">
                        <div class="d-flex justify-content-start align-items-center gap-2">
                            <div>
                                <input type="text" wire:model="seatNumber"
                                    class="form-control form-control-sm"
                                    placeholder="Enter seat number..."
                                    autofocus>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-person-check"></i> Board
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @elseif($tab === 'list')
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center gap-2">
                    <div>
                        <input type="text" wire:model.live.debounce.300ms="search"
                            class="form-control form-control-sm"
                            placeholder="Search passengers...">
                    </div>
                    <button class="btn btn-primary btn-sm"
                        wire:click="boardSelected"
                        @if (empty($selectedPassengers)) disabled @endif>
                        <i class="bi bi-person-check"></i> Board Selected ({{ count($selectedPassengers) }})
                    </button>
                </div>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle">
                <thead>
                    <tr>
                        @if ($tab === 'list')
                            <th width="40">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input"
                                        wire:model.live="selectAll"
                                        id="selectAll">
                                </div>
                            </th>
                        @endif
                        <th width="40">#</th>
                        <th width="80">Seat</th>
                        <th>Name</th>
                        <th>Ticket Number</th>
                        <th width="100">Type</th>
                        @if ($tab === 'boarded')
                            <th width="100">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($passengers as $index => $passenger)
                        <tr>
                            @if ($tab === 'list')
                                <td>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input"
                                            wire:model.live="selectedPassengers"
                                            value="{{ $passenger->id }}">
                                    </div>
                                </td>
                            @endif
                            <td>{{ $passengers->firstItem() + $index }}</td>
                            <td>{{ $passenger->seat?->designation ?? 'No Seat' }}</td>
                            <td>{{ $passenger->name }}</td>
                            <td>{{ $passenger->ticket_number }}</td>
                            <td>
                                <span class="badge bg-{{ $passenger->type === 'infant' ? 'warning' : 'info' }}">
                                    {{ ucfirst($passenger->type) }}
                                </span>
                            </td>
                            @if ($tab === 'boarded')
                                <td>
                                    <button class="btn btn-sm btn-danger"
                                        wire:click="unboardPassenger({{ $passenger->id }})"
                                        wire:confirm="Are you sure you want to unboard this passenger?">
                                        <i class="bi bi-person-x"></i> Unboard
                                    </button>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $tab === 'list' ? 6 : ($tab === 'boarded' ? 6 : 5) }}" class="text-center">
                                No passengers found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $passengers->links() }}
        </div>
    </div>
</div>
