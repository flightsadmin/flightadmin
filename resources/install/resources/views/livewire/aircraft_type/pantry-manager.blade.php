<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Pantry Configuration</h3>
        <button class="btn btn-sm btn-primary" wire:click="$toggle('showPantryModal')" data-bs-toggle="modal" data-bs-target="#pantryModal">
            <i class="bi bi-plus-lg"></i> Add Pantry
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Weight</th>
                        <th>Index</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pantries as $pantry)
                        <tr>
                            <td>{{ $pantry['name'] }}</td>
                            <td>{{ strtoupper($pantry['code']) }}</td>
                            <td>{{ number_format($pantry['weight']) }} kg</td>
                            <td>{{ number_format($pantry['index'], 4) }}</td>
                            <td>
                                <button class="btn btn-sm btn-primary"
                                    wire:click="editPantry('{{ $pantry['code'] }}')"
                                    data-bs-toggle="modal" data-bs-target="#pantryModal">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-danger"

                                    wire:click="deletePantry('{{ $pantry['code'] }}')"
                                    wire:confirm="Are you sure you want to delete this pantry?">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No pantries configured</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pantry Modal -->
    <div class="modal fade" id="pantryModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <form wire:submit="savePantry">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editingPantry ? 'Edit' : 'Add' }} Pantry</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control form-control-sm"
                                wire:model="pantryForm.name" required>
                            @error('pantryForm.name')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Code</label>
                            <input type="text" class="form-control form-control-sm"
                                wire:model="pantryForm.code" required
                                {{ $editingPantry ? 'readonly' : '' }}>
                            @error('pantryForm.code')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Weight (kg)</label>
                            <input type="number" step="0.01" class="form-control form-control-sm"
                                wire:model="pantryForm.weight" required>
                            @error('pantryForm.weight')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Index</label>
                            <input type="number" step="0.0001" class="form-control form-control-sm"
                                wire:model="pantryForm.index" required>
                            @error('pantryForm.index')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary">Save Pantry</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@script
    <script>
        $wire.on('pantry-saved', () => {
            const modal = bootstrap.Modal.getInstance(document.getElementById('pantryModal'));
            modal.hide();
        });
    </script>
@endscript
