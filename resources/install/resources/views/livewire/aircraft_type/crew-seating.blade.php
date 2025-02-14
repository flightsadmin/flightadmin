<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title m-0">Crew Configuration</h5>
            <div>
                @if ($isEditable)
                    <button wire:click="save" class="btn btn-success btn-sm me-2">
                        <i class="bi bi-check-lg"></i> Save Changes
                    </button>
                    <button wire:click="toggleEdit" class="btn btn-secondary btn-sm">
                        <i class="bi bi-x-lg"></i> Cancel
                    </button>
                @else
                    <button wire:click="toggleEdit" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil"></i> Edit Configuration
                    </button>
                @endif
            </div>
        </div>
        <div class="card-body">
            <h6 class="mb-2 text-decoration-underline fw-bold">Deck Crew</h6>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Location</th>
                            <th>Max Number</th>
                            <th>Arm</th>
                            <th>Index per kg</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($deck_crew as $index => $crew)
                            <tr>
                                <td>
                                    @if ($isEditable)
                                        <input type="text" class="form-control form-control-sm"
                                            wire:model="deck_crew.{{ $index }}.location">
                                    @else
                                        {{ $crew['location'] }}
                                    @endif
                                </td>
                                <td>
                                    @if ($isEditable)
                                        <input type="number" class="form-control form-control-sm"
                                            wire:model="deck_crew.{{ $index }}.max_number">
                                    @else
                                        {{ $crew['max_number'] }}
                                    @endif
                                </td>
                                <td>
                                    @if ($isEditable)
                                        <input type="number" step="0.01" class="form-control form-control-sm"
                                            wire:model="deck_crew.{{ $index }}.arm">
                                    @else
                                        {{ $crew['arm'] }}
                                    @endif
                                </td>
                                <td>
                                    @if ($isEditable)
                                        <input type="number" step="0.01" class="form-control form-control-sm"
                                            wire:model="deck_crew.{{ $index }}.index_per_kg">
                                    @else
                                        {{ $crew['index_per_kg'] }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-2 text-decoration-underline fw-bold">Cabin Crew</h6>
                @if ($isEditable)
                    <button wire:click="addCrew" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-plus-circle"></i> Add Location
                    </button>
                @endif
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Location</th>
                            <th>Max Number</th>
                            <th>Arm</th>
                            <th>Index per kg</th>
                            @if ($isEditable)
                                <th>Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cabin_crew as $index => $crew)
                            <tr>
                                <td>
                                    @if ($isEditable)
                                        <input type="text" class="form-control form-control-sm"
                                            wire:model="cabin_crew.{{ $index }}.location">
                                    @else
                                        {{ $crew['location'] }}
                                    @endif
                                </td>
                                <td>
                                    @if ($isEditable)
                                        <input type="number" class="form-control form-control-sm"
                                            wire:model="cabin_crew.{{ $index }}.max_number">
                                    @else
                                        {{ $crew['max_number'] }}
                                    @endif
                                </td>
                                <td>
                                    @if ($isEditable)
                                        <input type="number" step="0.01" class="form-control form-control-sm"
                                            wire:model="cabin_crew.{{ $index }}.arm">
                                    @else
                                        {{ $crew['arm'] }}
                                    @endif
                                </td>
                                <td>
                                    @if ($isEditable)
                                        <input type="number" step="0.01" class="form-control form-control-sm"
                                            wire:model="cabin_crew.{{ $index }}.index_per_kg">
                                    @else
                                        {{ $crew['index_per_kg'] }}
                                    @endif
                                </td>
                                @if ($isEditable)
                                    <td>
                                        <button class="btn btn-sm btn-link text-danger" wire:click="removeCrew({{ $index }})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title m-0">Crew Distribution</h5>
            <div>
                @if ($isEditable)
                    <button wire:click="save" class="btn btn-success btn-sm me-2">
                        <i class="bi bi-check-lg"></i> Save Changes
                    </button>
                    <button wire:click="toggleEdit" class="btn btn-secondary btn-sm">
                        <i class="bi bi-x-lg"></i> Cancel
                    </button>
                @else
                    <button wire:click="toggleEdit" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil"></i> Edit Configuration
                    </button>
                @endif
            </div>
        </div>
        <div class="card-body">
            @empty(!$crewSeats)
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Number of Cabin Crew</th>
                                @foreach ($this->crewLocations as $location)
                                    <th>{{ $location['location'] }}</th>
                                @endforeach
                                @if ($isEditable)
                                    <th>Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($crewSeats as $index => $row)
                                <tr>
                                    @if ($isEditable)
                                        <td>
                                            <input type="number" class="form-control form-control-sm"
                                                wire:model="crewSeats.{{ $index }}.number">
                                        </td>
                                        @foreach ($this->crewLocations as $location)
                                            <td>
                                                <input type="number" class="form-control form-control-sm"
                                                    wire:model="crewSeats.{{ $index }}.{{ Str::snake(strtolower($location['location'])) }}">
                                            </td>
                                        @endforeach
                                        <td>
                                            <button class="btn btn-sm btn-link text-danger"
                                                wire:click="removeSeat({{ $index }})">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    @else
                                        <td>{{ $row['number'] }}</td>
                                        @foreach ($this->crewLocations as $location)
                                            <td>
                                                {{ $row[Str::snake(strtolower($location['location']))] ?? 0 }}
                                            </td>
                                        @endforeach
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($isEditable)
                    <div class="mt-3">
                        <button wire:click="addSeat" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-plus-circle"></i> Add Row
                        </button>
                    </div>
                @endif
            @else
                <div class="text-center text-muted py-3">
                    No crew distribution configured
                </div>
            @endempty
        </div>
    </div>
</div>
