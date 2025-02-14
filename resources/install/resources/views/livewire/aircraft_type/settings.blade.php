<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Aircraft Type Settings</h3>
    </div>
    <div class="card-body">
        @foreach ($defaultSettings as $category => $settings)
            <div class="mb-4">
                <h4>{{ str_replace('_', ' ', ucfirst($category)) }}</h4>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Setting</th>
                                <th>Value</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($settings as $key => $config)
                                @php
                                    $existingSetting = $this->aircraftType->settings->where('key', $key)->first();
                                @endphp
                                <tr>
                                    <td>{{ str_replace('_', ' ', ucfirst($key)) }}</td>
                                    <td>
                                        @if ($existingSetting)
                                            {{ $existingSetting->value }}
                                        @else
                                            <span class="text-muted">Not set</span>
                                        @endif
                                    </td>
                                    <td>{{ $config['type'] }}</td>
                                    <td>{{ $config['description'] }}</td>
                                    <td>
                                        @if ($existingSetting)
                                            <button class="btn btn-sm btn-link"
                                                wire:click="editSetting({{ $existingSetting->id }})"
                                                data-bs-toggle="modal" data-bs-target="#settingModal">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-link"
                                                wire:click="deleteSetting({{ $existingSetting->id }})"
                                                wire:confirm="Are you sure you want to delete this setting?">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @else
                                            <button class="btn btn-sm btn-success"
                                                wire:click="createSetting('{{ $key }}', {{ json_encode($config) }})"
                                                data-bs-toggle="modal" data-bs-target="#settingModal">
                                                <i class="bi bi-plus-lg"></i> Set Value
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Setting Modal -->
    <div class="modal fade" id="settingModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <form wire:submit="saveSetting">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ $editingSetting ? 'Edit' : 'Create' }} Setting
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Key</label>
                            <input type="text" class="form-control" wire:model="settingForm.key" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Value</label>
                            @if ($settingForm['type'] === 'boolean')
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox"
                                        wire:model="settingForm.value">
                                </div>
                            @else
                                <input type="{{ $settingForm['type'] === 'float' ? 'number' : 'text' }}"
                                    class="form-control"
                                    step="{{ $settingForm['type'] === 'float' ? '0.0001' : '1' }}"
                                    wire:model="settingForm.value">
                            @endif
                            @error('settingForm.value')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" wire:model="settingForm.description" rows="2"></textarea>
                            @error('settingForm.description')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-sm btn-primary">
                            Save Setting
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @script
        <script>
            $wire.on('setting-saved', () => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('settingModal'));
                modal.hide();
            });
        </script>
    @endscript
</div>
