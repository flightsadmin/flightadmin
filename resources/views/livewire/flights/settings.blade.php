<div class="card">
    <div class="card-header">
        <h4 class="mb-0">Flight Options</h4>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <tbody>
                    <tr>
                        <td>NOTOC needed</td>
                        <td>
                            <select class="form-select form-select-sm"
                                wire:model.live="settings.notoc_required"
                                wire:change="updateNotoc($event.target.value)">
                                @foreach ($notocOptions as $label => $value)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            {{ $settings['notoc_required'] ? 'Yes' : 'No' }}
                        </td>
                    </tr>
                    <tr>
                        <td>Weight Variations</td>
                        <td>
                            <select class="form-select form-select-sm"
                                wire:model.live="settings.passenger_weights"
                                wire:change="updateFlightVariation($event.target.value)">
                                @foreach ($weightVariations as $variation)
                                    <option value="{{ $variation }}">{{ $variation }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            {{ $settings['passenger_weights'] }}
                        </td>
                    </tr>
                    <tr>
                        <td>Trim by</td>
                        <td>
                            <select class="form-select form-select-sm"
                                wire:model.live="settings.trim_settings.type"
                                wire:change="updateTrimType($event.target.value)">
                                @foreach ($trimOptions as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            {{ $settings['trim_settings']['type'] }}
                        </td>
                    </tr>
                    <tr>
                        <td>Fuel density</td>
                        <td>
                            <select class="form-select form-select-sm"
                                wire:model.live="settings.fuel_density"
                                wire:change="updateFuelDensity($event.target.value)">
                                @foreach ($fuelDensityOptions as $density)
                                    <option value="{{ $density }}">{{ $density }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            {{ $settings['fuel_density'] }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
