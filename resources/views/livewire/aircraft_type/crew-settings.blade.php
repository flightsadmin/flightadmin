<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title m-0">Crew Seating Positions</h5>
            <div>
                <button class="btn btn-sm btn-info me-2" wire:click="showDistributionModal" data-bs-toggle="modal"
                    data-bs-target="#distributionModal">
                    <i class="bi bi-people"></i> Manage Distributions
                </button>
                <button class="btn btn-sm btn-primary" wire:click="createSeating" data-bs-toggle="modal"
                    data-bs-target="#seatingModal">
                    <i class="bi bi-plus"></i> Add Position
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Location</th>
                            <th>Index/kg</th>
                            <th>Arm Length</th>
                            <th>Max Crew</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($settings['seating'] as $position => $config)
                            <tr>
                                <td class="bg-{{ $config['is_deck_crew'] ? 'success' : 'primary-subtle' }}">
                                    {{ $config['location'] }}
                                </td>
                                <td>{{ number_format($config['index_per_kg'], 5) }}</td>
                                <td>{{ number_format($config['arm_length'], 2) }}</td>
                                <td>{{ $config['max_crew'] }}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary"
                                        wire:click="editSeating('{{ $position }}')"
                                        data-bs-toggle="modal"
                                        data-bs-target="#seatingModal">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger"
                                        wire:click="deleteSeating('{{ $position }}')"
                                        wire:confirm="Are you sure you want to delete this crew position?">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Seating Modal -->
    <div class="modal fade" id="seatingModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <form wire:submit="saveSeating">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $isEditing ? 'Edit' : 'Add' }} Crew Position</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" class="form-control" wire:model="seatingForm.location"
                                placeholder="e.g. Forward Cabin">
                            @error('seatingForm.location')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Index per kg</label>
                            <input type="number" step="0.00001" class="form-control"
                                wire:model="seatingForm.index_per_kg">
                            @error('seatingForm.index_per_kg')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Arm Length</label>
                            <input type="number" step="0.01" class="form-control"
                                wire:model="seatingForm.arm_length">
                            @error('seatingForm.arm_length')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Max Crew</label>
                            <input type="number" class="form-control" wire:model="seatingForm.max_crew">
                            @error('seatingForm.max_crew')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                    wire:model="seatingForm.is_deck_crew">
                                <label class="form-check-label">
                                    This is a deck crew position
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary">Save Position</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Distribution Modal -->
    <div class="modal fade" id="distributionModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form wire:submit="saveDistributions">
                    <div class="modal-header">
                        <h5 class="modal-title">Cabin Crew Distribution Settings</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Number of Cabin Crew</th>
                                        @foreach ($cabinCrewPositions as $position => $config)
                                            <th>{{ $config['location'] }}</th>
                                        @endforeach
                                        <th>Total</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($distributions as $count => $distribution)
                                        <tr>
                                            <td>{{ $count }}</td>
                                            @foreach ($cabinCrewPositions as $index => $config)
                                                <td>
                                                    <input type="number" class="form-control form-control-sm"
                                                        wire:model="distributions.{{ $count }}.{{ $index }}"
                                                        min="0" max="{{ $config['max_crew'] }}">
                                                </td>
                                            @endforeach
                                            <td>
                                                <span
                                                    class="badge {{ collect($distribution)->sum() === $count ? 'bg-success' : 'bg-warning' }}">
                                                    {{ collect($distribution)->sum() }}/{{ $count }}
                                                </span>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-danger"
                                                    wire:click="removeDistributionRow({{ $count }})">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="{{ count($cabinCrewPositions) + 3 }}">
                                            <button type="button" class="btn btn-sm btn-secondary"
                                                wire:click="addDistributionRow">
                                                <i class="bi bi-plus"></i> Add Row
                                            </button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-save"></i> Save Distributions
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @script
        <script>
            $wire.on('seating-saved', () => {
                bootstrap.Modal.getInstance(document.getElementById('seatingModal')).hide();
            });
            $wire.on('distributions-saved', () => {
                bootstrap.Modal.getInstance(document.getElementById('distributionModal')).hide();
            });
        </script>
    @endscript
</div>
