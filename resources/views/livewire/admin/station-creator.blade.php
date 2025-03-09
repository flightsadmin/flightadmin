<div>
    <button wire:click="createStation" class="btn btn-sm btn-primary d-inline-flex align-items-center nowrap">
        <i class="bi bi-plus-circle"></i>
        <span class="text-nowrap"> Create Station</span>
    </button>

    <!-- Station Modal -->
    @if ($showModal)
        <div class="modal show d-block" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content shadow">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title">
                            <i class="bi bi-{{ $editMode ? 'pencil-square' : 'plus-circle' }} me-2"></i>
                            {{ $editMode ? 'Edit Station' : 'Create New Station' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit="save">
                            <div class="mb-3">
                                <label for="code" class="form-label fw-medium">Station Code <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-tag"></i></span>
                                    <input type="text" class="form-control" id="code" wire:model="code" maxlength="3"
                                        placeholder="e.g. LHR" style="text-transform: uppercase;">
                                </div>
                                <div class="form-text text-muted"><small>3-letter IATA airport code</small></div>
                                @error('code') 
                                    <div class="text-danger small mt-1">{{ $message ?? 'Invalid code format' }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="name" class="form-label fw-medium">Station Name <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-building"></i></span>
                                    <input type="text" class="form-control" id="name" wire:model="name"
                                        placeholder="e.g. London Heathrow">
                                </div>
                                @error('name') 
                                    <div class="text-danger small mt-1">{{ $message ?? 'Station name is required' }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="country" class="form-label fw-medium">Country</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-globe"></i></span>
                                    <input type="text" class="form-control" id="country" wire:model="country"
                                        placeholder="e.g. United Kingdom">
                                </div>
                                @error('country') 
                                    <div class="text-danger small mt-1">{{ $message ?? 'Invalid country format' }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="timezone" class="form-label fw-medium">Timezone</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-clock"></i></span>
                                    <input type="text" class="form-control" id="timezone" wire:model="timezone"
                                        placeholder="e.g. Europe/London">
                                </div>
                                <div class="form-text text-muted"><small>PHP timezone identifier (e.g. Europe/London,
                                        America/New_York)</small></div>
                                @error('timezone') 
                                    <div class="text-danger small mt-1">{{ $message ?? 'Invalid timezone format' }}</div>
                                @enderror
                            </div>

                            <div class="mb-4 form-check">
                                <input type="checkbox" class="form-check-input" id="is_active" wire:model="is_active">
                                <label class="form-check-label" for="is_active">Active</label>
                                <div class="form-text text-muted"><small>Inactive stations won't appear in dropdown
                                        lists</small></div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-outline-secondary"
                                    wire:click="$set('showModal', false)">
                                    <i class="bi bi-x-circle me-1"></i> Cancel
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-{{ $editMode ? 'check-circle' : 'plus-circle' }} me-1"></i>
                                    {{ $editMode ? 'Update' : 'Create' }} Station
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop show"></div>
    @endif
</div>