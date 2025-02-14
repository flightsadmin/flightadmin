<div>
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a wire:click.prevent="setTab('overview')" href=""
                class="nav-link {{ $activeTab === 'overview' ? 'active' : '' }}">
                <i class="bi bi-airplane"></i> Overview
            </a>
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
                        <span class="badge bg-secondary">{{ $baggage_count }}</span>
                    </a>
                </li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li>
                    <a wire:click.prevent="setTab('cargo')" href=""
                        class="dropdown-item {{ $activeTab === 'cargo' ? 'active' : '' }}">
                        <i class="bi bi-box"></i> Cargo
                        <span class="badge bg-secondary">{{ $cargo_count }}</span>
                    </a>
                </li>
            </ul>
        </li>
        <li class="nav-item">
            <a wire:click.prevent="setTab('passengers')" href=""
                class="nav-link {{ $activeTab === 'passengers' ? 'active' : '' }}">
                <i class="bi bi-people"></i> Passengers
                <span class="badge bg-secondary">{{ $passengers_count }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a wire:click.prevent="setTab('crew')" href=""
                class="nav-link {{ $activeTab === 'crew' ? 'active' : '' }}">
                <i class="bi bi-person-badge"></i> Crew
                <span class="badge bg-secondary">{{ $crew_count }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a wire:click.prevent="setTab('fuel')" href=""
                class="nav-link {{ $activeTab === 'fuel' ? 'active' : '' }}">
                <i class="bi bi-fuel-pump"></i> Fuel
            </a>
        </li>
        <li class="nav-item">
            <a wire:click.prevent="setTab('loadplan')" href=""
                class="nav-link {{ $activeTab === 'loadplan' ? 'active' : '' }}">
                <i class="bi bi-database-fill-add"></i> Loadplan
            </a>
        </li>
        <li class="nav-item">
            <a wire:click.prevent="setTab('loadsheet')" href=""
                class="nav-link {{ $activeTab === 'loadsheet' ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Loadsheet
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
        @elseif ($activeTab === 'crew')
            <livewire:crew.manager :flight="$flight" />
        @elseif ($activeTab === 'loadplan')
            <livewire:flight.loadplan-manager :flight="$flight" />
        @elseif ($activeTab === 'loadsheet')
            <livewire:flight.loadsheet-manager :flight="$flight" />
        @endif
    </div>
</div>
