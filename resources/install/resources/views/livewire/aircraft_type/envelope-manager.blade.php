<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Balance Envelopes</h3>
        <button class="btn btn-sm btn-primary" wire:click="$toggle('showEnvelopeModal')" data-bs-toggle="modal" data-bs-target="#envelopeModal">
            <i class="bi bi-plus-lg"></i> Add Envelope
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Points</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($envelopes as $envelope)
                        <tr>
                            <td>{{ $envelope->name }}</td>
                            <td>{{ count($envelope->points) }} points</td>
                            <td>
                                <button wire:click="toggleStatus({{ $envelope->id }})"
                                    class="btn btn-sm btn-{{ $envelope->is_active ? 'success' : 'warning' }}">
                                    {{ $envelope->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info"
                                    wire:click="viewEnvelope({{ $envelope->id }})"
                                    data-bs-toggle="modal" data-bs-target="#viewEnvelopeModal">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-primary"
                                    wire:click="editEnvelope({{ $envelope->id }})"
                                    data-bs-toggle="modal" data-bs-target="#envelopeModal">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-danger"
                                    wire:click="deleteEnvelope({{ $envelope->id }})"
                                    wire:confirm="Are you sure you want to delete this envelope?">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No envelopes configured</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- View Envelope Modal -->
    <div class="modal fade" id="viewEnvelopeModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">View Envelope</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if ($viewingEnvelope)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="fw-bold">Name:</label>
                                    <p>{{ $viewingEnvelope->name }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="fw-bold">Status:</label>
                                    <span class="badge bg-{{ $viewingEnvelope->is_active ? 'success' : 'warning' }}">
                                        {{ $viewingEnvelope->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="fw-bold">Points:</label>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Weight</th>
                                            <th>Index</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($viewingEnvelope->points as $point)
                                            <tr>
                                                <td>{{ number_format($point['weight'], 2) }}</td>
                                                <td>{{ number_format($point['index'], 4) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Envelope Modal -->
    <div class="modal fade" id="envelopeModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <form wire:submit="saveEnvelope">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editingEnvelope ? 'Edit' : 'Add' }} Envelope</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Name</label>
                                    <input type="text" class="form-control form-control-sm"
                                        wire:model="envelopeForm.name" required>
                                    @error('envelopeForm.name')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input"
                                            wire:model="envelopeForm.is_active" id="is_active">
                                        <label class="form-check-label" for="is_active">Active</label>
                                    </div>
                                </div>
                            </div>
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
                                        @foreach ($envelopeForm['points'] as $index => $point)
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
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary">Save Envelope</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@script
    <script>
        $wire.on('envelope-saved', () => {
            const modal = bootstrap.Modal.getInstance(document.getElementById('envelopeModal'));
            modal.hide();
        });
    </script>
@endscript
