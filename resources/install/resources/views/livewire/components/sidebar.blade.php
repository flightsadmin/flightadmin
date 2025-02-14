<div class="d-flex flex-column h-100">
    <div class="position-sticky top-0 border-bottom bg-dark-subtle">
        <div class="d-flex justify-content-between align-items-center m-2">
            <input type="date" wire:model.live="selectedDate" class="form-control form-control-sm me-3" id="date-picker">
            <a wire:navigate href="{{ route('flights.index') }}"
                class="btn-link text-secondary bi-airplane-engines-fill float-end h4 m-0"></a>
        </div>
    </div>

    <div class="flex-grow-1 overflow-auto">
        <ul class="nav flex-column">
            @forelse ($flights as $f)
                <li class="nav-item text-body-dark">
                    <a class="nav-link {{ isset($selectedFlight) && $selectedFlight->id == $f->id ? 'active bg-secondary text-white' : '' }} text-reset"
                        wire:click.prevent="setActiveFlight({{ $f->id }})"
                        wire:navigate href="{{ route('flights.show', ['flight' => $f->id]) }}">
                        {{ $f->flight_number }} - {{ $f->scheduled_departure_time->format('dS, M Y') }}
                    </a>
                </li>
            @empty
                <li class="nav-item text-body-dark mt-3">
                    <h5 class="mx-2 fw-medium">No Flights Available</h5>
                </li>
            @endforelse
        </ul>
    </div>

    <div class="dropdown mt-auto mb-3">
        {{ $flights->links() }}
        {{-- <div class="d-flex flex-column align-items-center"> --}}
        <hr class="mt-2">
        <a href="#" class="d-flex align-items-center text-center text-reset text-decoration-none dropdown-toggle"
            data-bs-toggle="dropdown" aria-expanded="false">
            <strong>Dropdown</strong>
        </a>
        {{-- </div> --}}
        <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
            <li>
                <form action="{{ route('migrate') }}" method="GET" style="margin: 0;">
                    @csrf
                    <button type="submit" class="dropdown-item text-danger bi-database-fill-gear">
                        Seed Database
                    </button>
                </form>
            </li>
            <li>
                <hr class="dropdown-divider">
            </li>
            <li class="dropdown-item">
                <a class="nav-link" wire:navigate href="{{ route('aircraft_types.index') }}">
                    <i class="bi bi-airplane-engines-fill"></i> Aircraft Types
                </a>
            </li>
            <li class="dropdown-item">
                <a class="nav-link" wire:navigate href="{{ route('airlines.index') }}">
                    <i class="bi bi-house-check-fill"></i> Airlines
                </a>
            </li>
            <li class="dropdown-item">
                <a class="nav-link" wire:navigate href="{{ route('flights.index') }}">
                    <i class="bi bi-calendar3"></i> Flights
                </a>
            </li>
            <li class="dropdown-item">
                <a class="nav-link" wire:navigate href="{{ route('crews.index') }}">
                    <i class="bi bi-people-fill"></i> Crew
                </a>
            </li>
        </ul>
    </div>
</div>
