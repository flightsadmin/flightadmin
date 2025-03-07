<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="card-title m-0">Roles</h2>
            <div class="d-flex justify-content-end align-items-center gap-2">
                <div>
                    <input type="text" wire:model.live.debounce.300ms="search"
                        class="form-control form-control-sm"
                        placeholder="Search roles...">
                </div>
                <button class="btn btn-primary btn-sm" wire:click="$toggle('showModal')"
                    data-bs-toggle="modal" data-bs-target="#roleFormModal">
                    <i class="bi bi-plus-lg"></i> Add Role
                </button>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered table-sm">
                    <thead>
                        <tr>
                            <th style="width: 150px;">Name</th>
                            <th>Permissions</th>
                            <th style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                            <tr>
                                <td>{{ $role->name }}</td>
                                <td>
                                    @foreach ($role->permissions as $permission)
                                        <span class="badge bg-info">{{ $permission->name }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary"
                                        wire:click="edit({{ $role->id }})"
                                        data-bs-toggle="modal" data-bs-target="#roleFormModal">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger"
                                        wire:click="delete({{ $role->id }})"
                                        wire:confirm="Are you sure you want to delete this role?">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No roles found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $roles->links() }}
        </div>
    </div>

    <!-- Role Form Modal -->
    <div class="modal fade" id="roleFormModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <form wire:submit="save">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ $editingRole ? 'Edit' : 'Add' }} Role
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
                            <label class="form-label">Permissions</label>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <tbody>
                                        @foreach ($permissions->chunk(3) as $chunk)
                                            <tr>
                                                @foreach ($chunk as $permission)
                                                    <td class="p-1">
                                                        <div class="form-check">
                                                            <input type="checkbox" class="form-check-input"
                                                                wire:model="selectedPermissions"
                                                                value="{{ $permission['name'] }}"
                                                                id="permission_{{ $permission['id'] }}">
                                                            <label class="form-check-label" for="permission_{{ $permission['id'] }}">
                                                                {{ $permission['name'] }}
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
                            @error('selectedPermissions')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary">
                            {{ $editingRole ? 'Update' : 'Create' }} Role
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @script
        <script>
            $wire.on('role-saved', () => {
                bootstrap.Modal.getInstance(document.getElementById('roleFormModal')).hide();
            });
        </script>
    @endscript
</div>
