<div>
    <button wire:click="createStation" class="btn btn-sm btn-primary">
        <i class="bi bi-plus-lg"></i> Create New Station
    </button>

    <!-- Station Modal -->
    @if ($showModal)
        <div class="modal show d-block" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editMode ? 'Edit Station' : 'Create New Station' }}</h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit="save">
                            <div class="mb-3">
                                <label for="code" class="form-label">Station Code</label>
                                <input type="text" class="form-control" id="code" wire:model="code" maxlength="3"
                                    placeholder="e.g. LHR">
                                <div class="form-text">3-letter IATA airport code</div>
                                @error('code')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="name" class="form-label">Station Name</label>
                                <input type="text" class="form-control" id="name" wire:model="name"
                                    placeholder="e.g. London Heathrow">
                                @error('name')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="country" class="form-label">Country</label>
                                <input type="text" class="form-control" id="country" wire:model="country"
                                    placeholder="e.g. United Kingdom">
                                @error('country')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="timezone" class="form-label">Timezone</label>
                                <input type="text" class="form-control" id="timezone" wire:model="timezone"
                                    placeholder="e.g. Europe/London">
                                <div class="form-text">PHP timezone identifier (e.g. Europe/London, America/New_York)</div>
                                @error('timezone')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="is_active" wire:model="is_active">
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-secondary"
                                    wire:click="$set('showModal', false)">Cancel</button>
                                <button type="submit" class="btn btn-primary">
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