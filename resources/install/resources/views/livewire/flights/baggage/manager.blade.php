<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="card-title m-0">Baggage</h2>
        <div class="d-flex align-items-center gap-3">
            <input type="text" wire:model.live.debounce.300ms="search"
                class="form-control form-control-sm"
                placeholder="Search baggage...">

            <select wire:model.live="status" class="form-select form-select-sm">
                <option value="">All Status</option>
                <option value="checked">Checked</option>
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
        <div>
            <a wire:navigate href="{{ route('flights.show', $flight) }}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Flight
            </a>
        </div>
    </div>

    <div class="card-body table-responsive">
        @if ($baggage->count() > 0)
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

            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th><input type="checkbox" wire:model.live="selectAll"></th>
                            <th>Tag Number</th>
                            @unless ($flight)
                                <th>Flight</th>
                            @endunless
                            <th>Passenger</th>
                            <th>Seat</th>
                            <th>Weight</th>
                            <th>Container</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($baggage as $bag)
                            <tr wire:key="{{ $bag->id }}">
                                <td>
                                    <input type="checkbox" wire:model.live="selected" value="{{ $bag->id }}">
                                </td>
                                <td>{{ $bag->tag_number }}</td>
                                @unless ($flight)
                                    <td>
                                        @if ($bag->flight)
                                            {{ $bag->flight->flight_number }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                @endunless
                                <td>{{ $bag->passenger->name }}</td>
                                <td>{{ $bag->passenger->seat->designation }}</td>
                                <td>{{ number_format($bag->weight) }} kg</td>
                                <td>
                                    <div class="dropdown d-inline">
                                        <button
                                            class="btn btn-sm btn-{{ $bag->container_id ? 'success' : 'danger' }} dropdown-toggle"
                                            type="button" data-bs-toggle="dropdown">
                                            {{ $bag->container_id ? $bag->container->container_number : 'Not loaded' }}
                                        </button>
                                        <ul class="dropdown-menu">
                                            @foreach ($containers as $container)
                                                <li>
                                                    <button class="dropdown-item"
                                                        wire:click="updateContainer({{ $bag->id }}, {{ $container->id }})">
                                                        <i class="bi bi-check-circle text-success"></i> {{ $container->container_number }}
                                                    </button>
                                                </li>
                                            @endforeach
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li>
                                                <button class="dropdown-item"
                                                    wire:click="updateContainer({{ $bag->id }}, null)">
                                                    <i class="bi bi-x-circle text-danger"></i> Offloaded
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                                <td>
                                    <span
                                        class="badge bg-{{ $bag->status === 'loaded' ? 'success' : ($bag->status === 'offloaded' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($bag->status) }}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-link" wire:click="edit({{ $bag->id }})" data-bs-toggle="modal"
                                        data-bs-target="#baggageFormModal">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-link text-danger" wire:click="delete({{ $bag->id }})"
                                        wire:confirm="Are you sure you want to remove this baggage?">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $flight ? 6 : 7 }}" class="text-center">No baggage found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="d-flex justify-content-between align-items-center">
        {{ $baggage->links() }}
    </div>

    <!-- Modal -->
    <div class="modal fade" id="baggageFormModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form wire:submit="save">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editingBaggage ? 'Edit' : 'Add' }} Baggage</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Passenger</label>
                                <select class="form-select form-select-sm" wire:model="form.passenger_id">
                                    <option value="">Select Passenger</option>
                                    @foreach ($passengers as $passenger)
                                        <option value="{{ $passenger->id }}">
                                            {{ $passenger->name }} ({{ $passenger->ticket_number }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('form.passenger_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tag Number</label>
                                <input type="text" class="form-control form-control-sm" wire:model="form.tag_number"
                                    wire:model.live="form.tag_number">
                                @error('form.tag_number')
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
                                    <option value="checked">Checked</option>
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
                        <button type="submit" class="btn btn-sm btn-primary">Save Baggage</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @script
        <script>
            $wire.on('baggage-saved', () => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('baggageFormModal'));
                modal.hide();
            });
        </script>
    @endscript
</div>
