<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Cabin Zones</h3>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#cabinZoneModal">
            <i class="bi bi-plus-lg"></i> Add Zone
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Max Capacity</th>
                        <th>Index</th>
                        <th>Arm</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($aircraftType->cabinZones as $zone)
                        <tr>
                            <td>{{ $zone->name }}</td>
                            <td>{{ number_format($zone->max_capacity) }} pax</td>
                            <td>{{ number_format($zone->index, 4) }}</td>
                            <td>{{ number_format($zone->arm, 4) }}</td>
                            <td>
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#cabinZoneModal" wire:click="editZone({{ $zone->id }})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-danger"
                                    wire:click="deleteZone({{ $zone->id }})"
                                    wire:confirm="Are you sure you want to delete this zone?">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No cabin zones defined</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Cabin Zone Modal -->
    <div class="modal fade" tabindex="-1" id="cabinZoneModal" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $editingZone ? 'Edit' : 'Add' }} Cabin Zone</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="saveZone">
                        <div class="mb-3">
                            <label class="form-label">Zone Name</label>
                            <input type="text" class="form-control form-control-sm" wire:model="zoneForm.name" required>
                            @error('zoneForm.name')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Max Capacity (kg)</label>
                            <input type="number" class="form-control form-control-sm" wire:model="zoneForm.max_capacity" required>
                            @error('zoneForm.max_capacity')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Index</label>
                            <input type="number" step="0.0001" class="form-control form-control-sm" wire:model="zoneForm.index" required>
                            @error('zoneForm.index')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Arm</label>
                            <input type="number" step="0.0001" class="form-control form-control-sm" wire:model="zoneForm.arm" required>
                            @error('zoneForm.arm')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-primary">Save Zone</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@script
    <script>
        $wire.on('zone-saved', () => {
            bootstrap.Modal.getInstance(document.getElementById('cabinZoneModal')).hide();
        });
    </script>
@endscript
