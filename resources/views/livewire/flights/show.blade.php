<div>
    @if (!$flight->aircraft_id)
        <div class="alert alert-warning mb-2">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill fs-1 me-2"></i>
                <div>
                    <strong class="mb-0">Aircraft Not Assigned!</strong>
                    <p class="mb-0">Please assign an aircraft to this flight before accessing other features.</p>
                </div>
            </div>
        </div>
    @endif

    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a wire:click.prevent="setTab('overview')" href=""
                class="nav-link {{ $activeTab === 'overview' ? 'active' : '' }}">
                <i class="bi bi-airplane"></i> Overview
            </a>
        </li>
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle {{ in_array($activeTab, ['passengers', 'boarding']) ? 'active' : '' }}"
                data-bs-toggle="dropdown" role="button" aria-expanded="false" href="#">
                <i class="bi bi-people"></i> PAX Control
            </a>
            <ul class="dropdown-menu">
                <li>
                    <a wire:click.prevent="setTab('passengers')" href=""
                        class="dropdown-item {{ $activeTab === 'passengers' ? 'active' : '' }}">
                        <i class="bi bi-people"></i> Check-in
                        <span class="badge bg-secondary">{{ $flight->passengers_count }}</span>
                    </a>
                </li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li>
                    <a wire:click.prevent="setTab('boarding')" href=""
                        class="dropdown-item {{ $activeTab === 'boarding' ? 'active' : '' }}">
                        <i class="bi bi-door-open-fill"></i> Boarding
                        <span
                            class="badge bg-secondary">{{ $flight->boarded_count }}/{{ $flight->accepted_count }}</span>
                    </a>
                </li>
            </ul>
        </li>
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle {{ in_array($activeTab, ['baggage', 'cargo', 'containers']) ? 'active' : '' }}"
                data-bs-toggle="dropdown" role="button" aria-expanded="false" href="#">
                <i class="bi bi-archive"></i> Deadload
            </a>
            <ul class="dropdown-menu">
                <li>
                    <a wire:click.prevent="setTab('baggage')" href=""
                        class="dropdown-item {{ $activeTab === 'baggage' ? 'active' : '' }}">
                        <i class="bi bi-bag"></i> Baggage
                        <span class="badge bg-secondary">{{ $flight->baggage_count }}</span>
                    </a>
                </li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li>
                    <a wire:click.prevent="setTab('cargo')" href=""
                        class="dropdown-item {{ $activeTab === 'cargo' ? 'active' : '' }}">
                        <i class="bi bi-box"></i> Cargo
                        <span class="badge bg-secondary">{{ $flight->cargo_count }}</span>
                    </a>
                </li>
            </ul>
        </li>
        <li class="nav-item">
            <a wire:click.prevent="setTab('crew')" href="" class="nav-link {{ $activeTab === 'crew' ? 'active' : '' }}">
                <i class="bi bi-person-badge"></i> Crew
                <span class="badge bg-secondary">{{ $flight->crew_count }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a wire:click.prevent="setTab('fuel')" href="" class="nav-link {{ $activeTab === 'fuel' ? 'active' : '' }}">
                <i class="bi bi-fuel-pump"></i> Fuel
            </a>
        </li>
        <li class="nav-item">
            <a wire:click.prevent="setTab('loading')" href=""
                class="nav-link {{ $activeTab === 'loading' ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Loading
            </a>
        </li>
        <li class="nav-item">
            <a wire:click.prevent="setTab('loadsheet')" href=""
                class="nav-link {{ $activeTab === 'loadsheet' ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Loadsheet
            </a>
        </li>
        <li class="nav-item">
            <a wire:click.prevent="setTab('documents')" href=""
                class="nav-link {{ $activeTab === 'documents' ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text"></i> Documents
            </a>
        </li>
    </ul>

    <!-- Tab Content -->
    <div>
        @if ($activeTab === 'overview')
            <livewire:flight.overview :flight="$flight" />
        @elseif ($activeTab === 'fuel')
            <livewire:fuel.manager :flight="$flight" />
        @elseif ($activeTab === 'baggage')
            <livewire:baggage.manager :flight="$flight" />
        @elseif ($activeTab === 'cargo')
            <livewire:cargo.manager :flight="$flight" />
        @elseif ($activeTab === 'passengers')
            <livewire:passenger.manager :flight="$flight" />
        @elseif ($activeTab === 'boarding')
            <livewire:flight.boarding-control :flight="$flight" />
        @elseif ($activeTab === 'crew')
            <livewire:crew.manager :flight="$flight" />
        @elseif ($activeTab === 'loading')
            <livewire:flight.loading-manager :flight="$flight" />
        @elseif ($activeTab === 'loadsheet')
            <livewire:flight.loadsheet-manager :flight="$flight" />
        @elseif ($activeTab === 'documents')
            <livewire:flight.documents :flight="$flight" />
        @endif
    </div>
</div>
