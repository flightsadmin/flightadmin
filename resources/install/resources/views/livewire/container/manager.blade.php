<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <select wire:model.live="type" class="form-select form-select-sm">
                <option value="">All Types</option>
                <option value="baggage">Baggage</option>
                <option value="cargo">Cargo</option>

            </select>
        </div>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#assignContainerModal">
            <i class="bi bi-plus-lg"></i> Assign Containers
        </button>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th>ULD Number</th>
                        <th>Type</th>
                        <th>Position</th>
                        <th>Status</th>
                        <th>Weight</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignedContainers as $container)
                        <tr class="{{ $container->pivot->type }}">
                            <td>{{ $container->container_number }}</td>
                            <td>
                                <span class="badge bg-{{ $container->pivot->type === 'baggage' ? 'primary' : 'warning' }}">
                                    {{ ucfirst($container->pivot->type) }}
                                    <i class="bi bi-{{ $container->pivot->type === 'baggage' ? 'luggage' : 'box' }}"></i>
                                </span>
                            </td>
                            <td>
                                {{ $container->pivot->position_id ? optional($container->position)->getFullCode() : '-' }}
                            </td>
                            <td>
                                <span class="badge bg-{{ $container->pivot->status === 'loaded' ? 'success' : 'warning' }}">
                                    {{ ucfirst($container->pivot->status) }}
                                </span>
                            </td>
                            <td>{{ number_format($container->weight) }} kg</td>
                            <td>
                                <button class="btn btn-sm btn-danger"
                                    wire:click="unassignContainer({{ $container->id }})"
                                    wire:confirm="Are you sure you want to unassign this container?">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No containers assigned</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Assignment Modal -->
    <div class="modal fade" id="assignContainerModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Containers</h5>
                    <div class="d-flex align-items-center gap-3">
                        <input type="text" wire:model.live.debounce.300ms="search"
                            class="form-control form-control-sm"
                            placeholder="Search containers...">

                        <select wire:model="assignmentType" class="form-select form-select-sm">
                            <option value="baggage">Assign as Baggage</option>
                            <option value="cargo">Assign as Cargo</option>
                        </select>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th> </th>
                                    <th>ULD Number</th>
                                    <th>Tare Weight</th>
                                    <th>Max Weight</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($availableContainers as $container)
                                    <tr>
                                        <td>
                                            <input type="checkbox"
                                                wire:model.live="selected"
                                                value="{{ $container->id }}">
                                        </td>
                                        <td>{{ $container->container_number }}</td>
                                        <td>{{ number_format($container->tare_weight) }} kg</td>
                                        <td>{{ number_format($container->max_weight) }} kg</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $availableContainers->links() }}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-sm btn-primary"
                        wire:click="assignContainers"
                        @if (empty($selected)) disabled @endif>
                        Assign Selected ({{ count($selected) }})
                    </button>
                </div>
            </div>
        </div>
    </div>

    @script
        <script>
            $wire.on('containerSaved', () => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('assignContainerModal'));
                modal.hide();
            });
        </script>
    @endscript
</div>
