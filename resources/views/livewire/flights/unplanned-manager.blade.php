<div class="card h-100">
    <div class="card-header py-2">
        <h6 class="card-title m-0">Bulk Items</h6>
    </div>
    <div class="card-body p-2">
        <div class="unplanned-items-area"
            x-data="{
                selectedType: null,
                selectItems(type) {
                    this.selectedType = type === this.selectedType ? null : type;
                    $dispatch(type === this.selectedType ? 'unplanned-items-deselected' : 'unplanned-items-selected', { type });
                }
            }">

            <div class="row">
                @forelse ($unplannedItems as $type => $item)
                    <div class="col-6">
                        <div class="holding-area p-2 mb-2 border-2"
                            :class="{ 'selected': selectedType === '{{ $type }}' }"
                            @click="selectItems('{{ $type }}')">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-{{ $type === 'baggage' ? 'luggage' : 'box-seam' }} fs-3"></i>
                                <div>
                                    <div>Pieces: {{ $item['total_pieces'] }}</div>
                                    <div>Weight: {{ $item['total_weight'] }}kg</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info">No unplanned items found</div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Pieces Modal -->
    <div class="modal fade" id="piecesModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add {{ ucfirst($selectedType ?? '') }} to Position</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label">Number of Pieces</label>
                            <i class="bi bi-info-circle text-info"> Leave empty to add all {{ $maxPieces }} pieces</i>
                        </div>
                        <input type="number" class="form-control" wire:model="inputPieces"
                            min="1" max="{{ $maxPieces }}" placeholder="Leave empty to add all pieces" autofocus>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel</button>
                    <button type="button" class="btn btn-sm btn-primary" wire:click="confirmAdd">
                        <i class="bi bi-plus-circle"></i> Add to Position</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .unplanned-items-area {
            height: 100%;
        }

        .holding-area {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            background-color: #f8f9fa;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .holding-area:hover {
            transform: translateY(-2px);
            transition: all 0.2s ease;
            background-color: #e9ecef;
            border-color: #157ce2;
        }

        .holding-area.selected {
            background-color: #cfe2ff;
            border-color: #0d6efd;
        }
    </style>

    <script>
        document.addEventListener('livewire:initialized', () => {
            let modal = new bootstrap.Modal(document.getElementById('piecesModal'));

            @this.on('showModal', () => {
                modal.show();
            });

            @this.on('hideModal', () => {
                modal.hide();
            });

            // Handle modal hidden event
            document.getElementById('piecesModal').addEventListener('hidden.bs.modal', () => {
                @this.resetModal();
            });
        });
    </script>
</div>
