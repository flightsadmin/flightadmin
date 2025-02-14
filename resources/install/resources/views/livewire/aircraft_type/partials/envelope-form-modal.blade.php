<div class="modal fade" id="envelopeModal" tabindex="-1" wire:ignore.self>
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form wire:submit="saveEnvelope">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $editingEnvelope ? 'Edit' : 'Add' }} Envelope</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control form-control-sm"
                            wire:model="envelopeForm.name" required>
                        @error('envelopeForm.name')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control form-control-sm"
                            wire:model="envelopeForm.description" rows="2"></textarea>
                        @error('envelopeForm.description')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Points</label>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Weight</th>
                                        <th>Index</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($envelopeForm['points'] as $index => $point)
                                        <tr>
                                            <td>
                                                <input type="number" step="0.01"
                                                    class="form-control form-control-sm"
                                                    wire:model="envelopeForm.points.{{ $index }}.weight">
                                            </td>
                                            <td>
                                                <input type="number" step="0.01"
                                                    class="form-control form-control-sm"
                                                    wire:model="envelopeForm.points.{{ $index }}.index">
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-danger"
                                                    wire:click="removePoint({{ $index }})">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-sm btn-secondary" wire:click="addPoint">
                            <i class="bi bi-plus-lg"></i> Add Point
                        </button>
                        @error('envelopeForm.points')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input"
                                wire:model="envelopeForm.is_active" id="is_active">
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">Save Envelope</button>
                </div>
            </form>
        </div>
    </div>
</div> 