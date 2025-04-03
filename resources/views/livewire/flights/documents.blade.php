<div>
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#documents">
                        <i class="bi bi-file-earmark-text me-2"></i>Documents
                    </a>
                </li>
                <!-- Add other tabs here -->
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content">
                <div class="tab-pane fade show active" id="documents">
                    <div class="row g-3">
                        <!-- Document Type Selector -->
                        <div class="col-md-4">
                            <label class="form-label">Document Type</label>
                            <select class="form-select form-select-sm" wire:model.live="selectedDocType">
                                <option value="">Select Document</option>
                                <option value="loadsheet">Loadsheet</option>
                                <option value="loadplan">Loading Instructions</option>
                            </select>
                        </div>

                        <!-- Version Selector -->
                        <div class="col-md-4">
                            <label class="form-label">Version</label>
                            <select class="form-select form-select-sm" wire:model.live="selectedVersion"
                                @if (!$selectedDocType) disabled @endif>
                                <option value="">Select Version</option>
                                @if ($selectedDocType === 'loadsheet')
                                    @if ($loadsheets->count())
                                        @foreach ($loadsheets as $loadsheet)
                                            <option value="{{ $loadsheet->id }}">
                                                Version {{ $loadsheet->edition }}
                                                @if ($loop->first)
                                                    (Current)
                                                @endif
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="" disabled>NIL</option>
                                    @endif
                                @elseif($selectedDocType === 'loadplan')
                                    @if ($loadplans->count())
                                        @foreach ($loadplans as $instruction)
                                            <option value="{{ $instruction->id }}">
                                                Version {{ $instruction->version }}
                                                @if ($loop->first)
                                                    (Current)
                                                @endif
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="" disabled>NIL</option>
                                    @endif
                                @endif
                            </select>
                        </div>

                        <!-- Action Buttons -->
                        <div class="col-md-4">
                            <label class="form-label">Actions</label>
                            <div class="btn-group-sm w-100">
                                <button class="btn btn-sm btn-primary" @if (!$selectedVersion) disabled @endif
                                    wire:click="viewDocument">
                                    <i class="bi bi-eye me-2"></i>View
                                </button>
                                <button class="btn btn-sm btn-secondary" @if (!$selectedVersion) disabled @endif
                                    wire:click="downloadDocument">
                                    <i class="bi bi-download me-2"></i>Download
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Document Info -->
                    @if ($selectedVersion)
                        <div class="mt-4">
                            <div class="card border">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1">
                                                <strong>Generated:</strong>
                                                {{ $selectedDocType === 'loadsheet'
                                                    ? $loadsheets->find($selectedVersion)->created_at->format('d M Y H:i')
                                                    : $loadplans->find($selectedVersion)->created_at->format('d M Y H:i') }}
                                            </p>
                                            <p class="mb-1">
                                                <strong>Released by:</strong>
                                                {{ $selectedDocType === 'loadsheet'
                                                    ? $loadsheets->find($selectedVersion)->releaser->name
                                                    : $loadplans->find($selectedVersion)->releaser->name }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Document Preview Modal -->
    <div class="modal fade" id="documentPreviewModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $previewTitle }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="ratio ratio-16x9">
                        <iframe src="{{ $previewUrl }}" class="w-100 h-100"></iframe>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                        Close
                    </button>
                    <button type="button" class="btn btn-sm btn-primary" wire:click="downloadDocument">
                        <i class="bi bi-download me-2"></i>Download
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@script
    <script>
        $wire.on('show-document-preview', () => {
            new bootstrap.Modal(document.getElementById('documentPreviewModal')) modal.show();
        });
    </script>
@endscript
