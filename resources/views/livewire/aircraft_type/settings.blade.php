<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">MAC Calculation Settings</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Setting</th>
                            <th>Value</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($macSettings as $key => $value)
                            <tr>
                                <td>{{ ucwords(str_replace('_', ' ', $key)) }}</td>
                                @if ($editingSetting === $key)
                                    <td>
                                        <input type="number" step="any" class="form-control form-control-sm"
                                            wire:model="settingForm.value">
                                        @error('settingForm.value')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-success" wire:click="saveSetting">
                                            <i class="bi bi-check"></i> Save
                                        </button>
                                        <button class="btn btn-sm btn-secondary" wire:click="$set('editingSetting', null)">
                                            <i class="bi bi-x"></i> Cancel
                                        </button>
                                    </td>
                                @else
                                    <td>{{ $value }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" wire:click="editSetting('{{ $key }}')">
                                            <i class="bi bi-pencil"></i> Edit
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
</div>
