<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title m-0">Cargo</h4>
            <div class="d-flex align-items-center gap-3">
                <input type="text" wire:model.live.debounce.300ms="search"
                    class="form-control form-control-sm"
                    placeholder="Search cargo...">

                <select wire:model.live="type" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    <option value="general">General</option>
                    <option value="perishable">Perishable</option>
                    <option value="dangerous_goods">Dangerous Goods</option>
                    <option value="live_animals">Live Animals</option>
                    <option value="valuable">Valuable</option>
                    <option value="mail">Mail</option>
                </select>

                <select wire:model.live="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="accepted">Accepted</option>
                    <option value="loaded">Loaded</option>
                    <option value="offloaded">Offloaded</option>
                </select>

                <select wire:model.live="container_id" class="form-select form-select-sm">
                    <option value="">All Containers</option>
                    @foreach ($containers as $container)
                        <option value="{{ $container->id }}">{{ $container->container_number }}</option>
                    @endforeach
                </select>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary btn-sm" wire:click="$toggle('showForm')" data-bs-toggle="modal"
                    data-bs-target="#cargoFormModal">
                    <i class="bi bi-plus-circle"></i> Add Cargo
                </button>
            </div>
        </div>

        <div class="card-body">
            @if ($cargo->count() > 0)
                <div class="mb-3 d-flex gap-2 align-items-center">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" wire:model.live="selectAll" id="selectAll">
                        <label class="form-check-label" for="selectAll">Select All</label>
                    </div>

                    @if (!empty($selected))
                        <select class="form-select form-select-sm w-auto" wire:model="bulkContainer">
                            <option value="">Select Container</option>
                            @foreach ($containers as $container)
                                <option value="{{ $container->id }}">{{ $container->container_number }}</option>
                            @endforeach
                        </select>

                        <button class="btn btn-primary btn-sm" wire:click="loadSelectedToContainer">
                            Load Selected ({{ count($selected) }})
                        </button>
                    @endif
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th><input type="checkbox" wire:model.live="selectAll"></th>
                            <th>AWB Number</th>
                            <th>Type</th>
                            <th>Pieces</th>
                            <th>Weight</th>
                            <th>Container</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($cargo as $item)
                            <tr wire:key="cargo-{{ $item->id }}">
                                <td>
                                    <input type="checkbox" wire:model.live="selected" value="{{ $item->id }}">
                                </td>
                                <td>
                                    {{ $item->awb_number }}
                                </td>
                                <td>{{ ucwords(str_replace('_', ' ', $item->type)) }}</td>
                                <td>{{ $item->pieces }}</td>
                                <td>{{ number_format($item->weight) }} kg</td>
                                <td>
                                    <div class="dropdown d-inline">
                                        <button
                                            class="btn btn-sm btn-{{ $item->container_id ? 'success' : 'danger' }} dropdown-toggle"
                                            type="button" data-bs-toggle="dropdown">
                                            {{ $item->container_id ? $item->container->container_number : 'Not loaded' }}
                                        </button>
                                        <ul class="dropdown-menu">
                                            @foreach ($containers as $container)
                                                <li>
                                                    <button class="dropdown-item"
                                                        wire:click="updateContainer({{ $item->id }}, {{ $container->id }})">
                                                        <i class="bi bi-check-circle text-success"></i>
                                                        {{ $container->container_number }}
                                                    </button>
                                                </li>
                                            @endforeach
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li>
                                                <button class="dropdown-item"
                                                    wire:click="updateContainer({{ $item->id }}, null)">
                                                    <i class="bi bi-x-circle text-danger"></i> Offloaded
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                                <td>
                                    <span
                                        class="badge bg-{{ $item->status === 'loaded' ? 'success' : ($item->status === 'offloaded' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-link" wire:click="edit({{ $item->id }})" data-bs-toggle="modal"
                                        data-bs-target="#cargoFormModal">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-link text-danger" wire:click="delete({{ $item->id }})"
                                        wire:confirm="Are you sure you want to remove this cargo?">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="100%" class="text-center">No cargo found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $cargo->links() }}
            </div>
        </div>
    </div>

    <div class="modal fade" id="cargoFormModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form wire:submit="save">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editingCargo ? 'Edit' : 'Add' }} Cargo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">AWB Number</label>
                                <input type="text" class="form-control form-control-sm" wire:model="form.awb_number">
                                @error('form.awb_number')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Type</label>
                                <select class="form-select form-select-sm" wire:model="form.type">
                                    <option value="">Select Type</option>
                                    <option value="general">General</option>
                                    <option value="perishable">Perishable</option>
                                    <option value="dangerous_goods">Dangerous Goods</option>
                                    <option value="live_animals">Live Animals</option>
                                    <option value="valuable">Valuable</option>
                                    <option value="mail">Mail</option>
                                </select>
                                @error('form.type')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Pieces</label>
                                <input type="number" class="form-control form-control-sm" wire:model="form.pieces">
                                @error('form.pieces')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Weight (kg)</label>
                                <input type="number" class="form-control form-control-sm" wire:model="form.weight">
                                @error('form.weight')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Container</label>
                                <select class="form-select form-select-sm" wire:model="form.container_id">
                                    <option value="">No Container</option>
                                    @foreach ($containers as $container)
                                        <option value="{{ $container->id }}">
                                            {{ $container->container_number }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('form.container_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select class="form-select form-select-sm" wire:model="form.status">
                                    <option value="accepted">Accepted</option>
                                    <option value="loaded">Loaded</option>
                                    <option value="offloaded">Offloaded</option>
                                </select>
                                @error('form.status')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control form-control-sm" wire:model="form.notes" rows="2"></textarea>
                                @error('form.notes')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary">Save Cargo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@script
    <script>
        $wire.on('cargo-saved', () => {
            bootstrap.Modal.getInstance(document.getElementById('cargoFormModal')).hide();
        });
    </script>
@endscript
