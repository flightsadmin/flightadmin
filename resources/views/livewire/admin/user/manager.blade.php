<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="card-title m-0">Users</h2>
            <div class="d-flex justify-content-end align-items-center gap-2">
                <div>
                    <input type="text" wire:model.live.debounce.300ms="search"
                        class="form-control form-control-sm"
                        placeholder="Search users...">
                </div>
                @can('createUser')
                    <button class="btn btn-primary btn-sm" wire:click="$toggle('showModal')"
                        data-bs-toggle="modal" data-bs-target="#userFormModal">
                        <i class="bi bi-person-plus"></i> Add User
                    </button>
                @endcan
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Roles</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @foreach ($user->roles as $role)
                                        <span class="badge bg-info">{{ $role->name }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    @can('editUser')
                                        <button wire:click="toggleStatus({{ $user->id }})"
                                            @if ($user->hasRole('super-admin') && !auth()->user()->hasRole('super-admin')) disabled @endif
                                            class="btn btn-sm btn-{{ $user->active ? 'success' : 'danger' }}">
                                            {{ $user->active ? 'Active' : 'Inactive' }}
                                        </button>
                                    @endcan
                                </td>
                                <td>
                                    @can('editUser')
                                        <button class="btn btn-sm btn-primary"
                                            wire:click="edit({{ $user->id }})"
                                            data-bs-toggle="modal" data-bs-target="#userFormModal">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    @endcan

                                    @can('deleteUser')
                                        <button class="btn btn-sm btn-danger"
                                            wire:click="delete({{ $user->id }})"
                                            wire:confirm="Are you sure you want to delete this user?">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No users found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $users->links() }}
        </div>
    </div>

    <!-- User Form Modal -->
    <div class="modal fade" id="userFormModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <form wire:submit="save">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ $editingUser ? 'Edit' : 'Add' }} User
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control form-control-sm"
                                wire:model="form.name" required>
                            @error('form.name')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control form-control-sm"
                                wire:model="form.email" required>
                            @error('form.email')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control form-control-sm"
                                wire:model="form.password"
                                {{ !$editingUser ? 'required' : '' }}>
                            @error('form.password')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" class="form-control form-control-sm"
                                wire:model="form.password_confirmation"
                                {{ !$editingUser ? 'required' : '' }}>
                        </div>

                        @can('editUser')
                            <div class="mb-3">
                                <label class="form-label">Roles</label>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <tbody>
                                            @foreach ($roles->chunk(3) as $chunk)
                                                <tr>
                                                    @foreach ($chunk as $role)
                                                        <td class="p-1">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input"
                                                                    wire:model="selectedRoles"
                                                                    value="{{ $role['name'] }}"
                                                                    id="role_{{ $role['id'] }}">
                                                                <label class="form-check-label" for="role_{{ $role['id'] }}">
                                                                    {{ str($role['name'])->title() }}
                                                                </label>
                                                            </div>
                                                        </td>
                                                    @endforeach
                                                    @for ($i = $chunk->count(); $i < 3; $i++)
                                                        <td></td>
                                                    @endfor
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endcan

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input"
                                    wire:model="form.active" id="active">
                                <label class="form-check-label" for="active">Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary">
                            {{ $editingUser ? 'Update' : 'Create' }} User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @script
        <script>
            $wire.on('user-saved', () => {
                bootstrap.Modal.getInstance(document.getElementById('userFormModal')).hide();
            });
        </script>
    @endscript
</div>
