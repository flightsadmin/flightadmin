<div>
    <div class="row g-2">
        <div class="col-md-3">
            <div class="list-group mb-3">
                @foreach ($categories as $category)
                    <button class="list-group-item list-group-item-action {{ $selectedCategory === $category ? 'active' : '' }}"
                        wire:click="selectCategory('{{ $category }}')">
                        <i
                            class="bi bi-{{ match ($category) {
                                'general' => 'gear',
                                'operations' => 'clock',
                                'cargo' => 'box',
                                'notifications' => 'bell',
                                default => 'circle',
                            } }}"></i>
                        {{ str_replace('_', ' ', ucfirst($category)) }}
                    </button>
                @endforeach
            </div>

            <button class="btn btn-sm btn-success w-100" wire:click="showNewSettingForm"
                data-bs-toggle="modal" data-bs-target="#newSettingModal">
                <i class="bi bi-plus-lg"></i> Add New Setting
            </button>
        </div>

        <div class="col-md-9">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title m-0">{{ str_replace('_', ' ', ucfirst($selectedCategory)) }} Settings</h5>
                    <button class="btn btn-sm btn-warning" wire:click="repairSettings"
                        wire:confirm="Are you sure you want to repair the settings? This will overwrite all existing settings with the default values.">
                        <i class="bi bi-wrench"></i> Repair Settings
                    </button>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Setting Key</th>
                                <th>Default Value</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($airlineSettings[$selectedCategory]) && is_array($airlineSettings[$selectedCategory]))
                                @foreach ($airlineSettings[$selectedCategory] as $key => $setting)
                                    @if (is_array($setting))
                                        <tr>
                                            <td>{{ str_replace('_', ' ', ucfirst($key)) }}</td>
                                            <td>
                                                @if (isset($setting['type']) && $setting['type'] === 'boolean')
                                                    <span
                                                        class="badge bg-{{ isset($setting['default']) && $setting['default'] ? 'success' : 'danger' }}">
                                                        {{ isset($setting['default']) && $setting['default'] ? 'Yes' : 'No' }}
                                                    </span>
                                                @else
                                                    {{ $setting['default'] ?? 'Not set' }}
                                                @endif
                                            </td>
                                            <td>{{ $setting['type'] ?? 'string' }}</td>
                                            <td>{{ $setting['description'] ?? '' }}</td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-primary" wire:click="editSetting('{{ $key }}')"
                                                        data-bs-toggle="modal" data-bs-target="#editSettingModal">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-danger" wire:click="removeSetting('{{ $key }}')"
                                                        wire:confirm="Are you sure you want to delete this setting?">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="text-center">No settings found for this category</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Setting Modal -->
    <div class="modal fade" id="editSettingModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <form wire:submit.prevent="saveSetting">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Setting</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Setting Key</label>
                            <input type="text" class="form-control form-control-sm" wire:model="editingForm.key" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <select class="form-select form-select-sm" wire:model="editingForm.type">
                                <option value="string">String</option>
                                <option value="integer">Integer</option>
                                <option value="float">Float</option>
                                <option value="boolean">Boolean</option>
                                <option value="json">JSON</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <input type="text" class="form-control form-control-sm" wire:model="editingForm.description">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Default Value</label>
                            @if (isset($editingForm['type']) && $editingForm['type'] === 'boolean')
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="editingForm.value">
                                </div>
                            @elseif(isset($editingForm['type']) && $editingForm['type'] === 'json')
                                <textarea class="form-control form-control-sm" rows="5" wire:model="editingForm.value"></textarea>
                            @else
                                <input
                                    type="{{ isset($editingForm['type']) && ($editingForm['type'] === 'float' || $editingForm['type'] === 'integer') ? 'number' : 'text' }}"
                                    class="form-control form-control-sm"
                                    step="{{ isset($editingForm['type']) && $editingForm['type'] === 'float' ? '0.01' : '1' }}"
                                    wire:model="editingForm.value">
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-check-lg"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- New Setting Modal -->
    <div class="modal fade" id="newSettingModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <form wire:submit.prevent="addNewSetting">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Setting</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-select form-select-sm" wire:model="newSettingForm.category">
                                @foreach ($categories as $category)
                                    <option value="{{ $category }}">{{ str_replace('_', ' ', ucfirst($category)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Setting Key</label>
                            <input type="text" class="form-control form-control-sm" wire:model="newSettingForm.key">
                            @error('newSettingForm.key')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <select class="form-select form-select-sm" wire:model="newSettingForm.type">
                                <option value="string">String</option>
                                <option value="integer">Integer</option>
                                <option value="float">Float</option>
                                <option value="boolean">Boolean</option>
                                <option value="json">JSON</option>
                            </select>
                            @error('newSettingForm.type')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <input type="text" class="form-control form-control-sm" wire:model="newSettingForm.description">
                            @error('newSettingForm.description')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Default Value</label>
                            @if ($newSettingForm['type'] === 'boolean')
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="newSettingForm.value">
                                </div>
                            @elseif($newSettingForm['type'] === 'json')
                                <textarea class="form-control form-control-sm" rows="5" wire:model="newSettingForm.value"></textarea>
                            @else
                                <input
                                    type="{{ $newSettingForm['type'] === 'float' || $newSettingForm['type'] === 'integer' ? 'number' : 'text' }}"
                                    class="form-control form-control-sm"
                                    step="{{ $newSettingForm['type'] === 'float' ? '0.01' : '1' }}"
                                    wire:model="newSettingForm.value">
                            @endif
                            @error('newSettingForm.value')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-lg"></i> Add Setting
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@script
    <script>
        $wire.on('close-edit-setting-modal', () => {
            bootstrap.Modal.getInstance(document.getElementById('editSettingModal')).hide();
        });

        $wire.on('close-new-setting-modal', () => {
            bootstrap.Modal.getInstance(document.getElementById('newSettingModal')).hide();
        });
    </script>
@endscript
