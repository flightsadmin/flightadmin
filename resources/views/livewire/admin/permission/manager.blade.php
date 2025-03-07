<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="card-title m-0">Permissions</h2>
            <div class="d-flex justify-content-end align-items-center gap-2">
                <div>
                    <input type="text" wire:model.live.debounce.300ms="search"
                        class="form-control form-control-sm"
                        placeholder="Search permissions...">
                </div>
                <button class="btn btn-primary btn-sm" wire:click="$toggle('showModal')"
                    data-bs-toggle="modal" data-bs-target="#permissionFormModal">
                    <i class="bi bi-plus-lg"></i> Add Permission
                </button>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($permissions as $permission)
                            <tr>
                                <td>{{ $permission->name }}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary"
                                        wire:click="edit({{ $permission->id }})"
                                        data-bs-toggle="modal" data-bs-target="#permissionFormModal">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger"
                                        wire:click="delete({{ $permission->id }})"
                                        wire:confirm="Are you sure you want to delete this permission?">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">No permissions found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $permissions->links() }}
        </div>
    </div>

    <!-- Permission Form Modal -->
    <div class="modal fade" id="permissionFormModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <form wire:submit="save">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ $editingPermission ? 'Edit' : 'Add' }} Permission
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
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary">
                            {{ $editingPermission ? 'Update' : 'Create' }} Permission
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @script
        <script>
            $wire.on('permission-saved', () => {
                bootstrap.Modal.getInstance(document.getElementById('permissionFormModal')).hide();
            });
        </script>
    @endscript
</div>
