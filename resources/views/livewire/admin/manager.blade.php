<div>
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <button class="nav-link {{ $tab === 'users' ? 'active' : '' }}" wire:click="setTab('users')">
                        <i class="bi bi-people"></i> Users
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link {{ $tab === 'roles' ? 'active' : '' }}" wire:click="setTab('roles')">
                        <i class="bi bi-shield-shaded"></i> Roles
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link {{ $tab === 'permissions' ? 'active' : '' }}"
                        wire:click="setTab('permissions')">
                        <i class="bi bi-house-lock-fill"></i> Permissions
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link {{ $tab === 'templates' ? 'active' : '' }}"
                        wire:click="setTab('templates')">
                        <i class="bi bi-house-lock-fill"></i> Email Templates
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content">
                <div class="tab-pane fade {{ $tab === 'users' ? 'show active' : '' }}">
                    <livewire:admin.user.manager />
                </div>
                <div class="tab-pane fade {{ $tab === 'roles' ? 'show active' : '' }}">
                    <livewire:admin.role.manager />
                </div>
                <div class="tab-pane fade {{ $tab === 'permissions' ? 'show active' : '' }}">
                    <livewire:admin.permission.manager />
                </div>
                <div class="tab-pane fade {{ $tab === 'templates' ? 'show active' : '' }}">
                    <livewire:email-templates />
                </div>
            </div>
        </div>
    </div>
</div>