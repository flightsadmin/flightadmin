<div class="mb-4">
    <div class="row g-2">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Load Sheet</h4>
                    <div wire:loading wire:target="generateLoadsheet, finalizeLoadsheet">
                        <div class="custom-spin-overlay">
                            <div class="position-absolute top-50 start-50 translate-middle d-flex justify-content-center">
                                <div class="spinner-border" style="width: 4rem; height: 4rem; border-width: 0.5rem;" role="status">
                                    <span class="visually-hidden">Loading ...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mb-0">
                        @if ($loadsheet && !$loadsheet->final && $loadsheet->status !== 'revoked')
                            <button class="btn btn-success btn-sm mb-0" wire:click="finalizeLoadsheet"
                                wire:loading.attr="disabled">
                                <i class="bi bi-check2-circle"></i> Finalize Loadsheet
                            </button>
                        @endif

                        @if ($loadsheet && $loadsheet->status === 'released')
                            <button class="btn btn-secondary btn-sm mb-0" wire:click="revokeLoadsheet"
                                wire:confirm="Are you sure you want to revoke this loadsheet?"
                                wire:loading.attr="disabled">
                                <i class="bi bi-trash-fill"></i> Revoke Loadsheet
                            </button>
                        @endif

                        @if ((!$loadsheet || $loadsheet->status === 'revoked') && $flight->fuel && $loadplan && $loadplan->status === 'released')
                            <button class="btn btn-primary btn-sm mb-0" wire:click="generateLoadsheet"
                                wire:loading.attr="disabled">
                                <i class="bi bi-plus-circle"></i> Generate Loadsheet
                            </button>
                        @endif
                    </div>
                </div>
                @if ($loadsheet)
                    @php
                        $distribution = $loadsheet->distribution;
                        $pax = $distribution['load_data'];
                        $totalPax = array_sum(
                            array_column(
                                array_filter($pax['pax_by_type'], fn($data, $type) => $type !== 'infant', ARRAY_FILTER_USE_BOTH),
                                'count',
                            ),
                        );
                    @endphp
                    @include('livewire.flights.partials.loadsheet')
                @else
                    <div class="card-body">
                        <div class="text-center py-4">
                            @if (!$flight->fuel)
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i> Fuel data must be added before generating
                                    loadsheet.
                                </div>
                            @endif
                            @if (!$loadplan || $loadplan->status !== 'released')
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    Load plan must be released before generating loadsheet.
                                </div>
                            @else
                                <div class="alert alert-success">
                                    <p class="text-muted m-0">No loadsheet generated yet.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-md-6">
            <div class="row g-2">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title m-0">Trim Sheet</h4>
                            <button class="btn btn-sm btn-primary" wire:click="refreshTrimSheet" @disabled(!$loadsheet)>
                                <i class="bi bi-arrow-counterclockwise"></i> Refresh Trim Sheet
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="py-4">
                                <canvas id="trimSheetChart" height="200" wire:ignore></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">Passenger Distribution</h4>
                            <button class="btn btn-sm btn-warning mb-0" wire:click="resetDistribution"
                                wire:confirm="This will reset to actual checked-in passenger distribution. Continue?">
                                <i class="bi bi-arrow-counterclockwise"></i> Reset to Actual
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Zone</th>
                                            <th>Male</th>
                                            <th>Female</th>
                                            <th>Child</th>
                                            <th>Infant</th>
                                            <th>Total</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($paxDistribution as $zoneName => $counts)
                                            <tr>
                                                <td>{{ $zoneName }} (Max {{ $counts['max_pax'] }}) Pax</td>
                                                @if ($editingZone === $zoneName)
                                                    <td>
                                                        <input type="number" class="form-control form-control-sm"
                                                            wire:model="zoneForm.male">
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control form-control-sm"
                                                            wire:model="zoneForm.female">
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control form-control-sm"
                                                            wire:model="zoneForm.child">
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control form-control-sm"
                                                            wire:model="zoneForm.infant">
                                                    </td>
                                                    <td>
                                                        {{ array_sum($zoneForm) }}
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-success"
                                                            wire:click="saveZoneDistribution">
                                                            <i class="bi bi-check"></i> Save
                                                        </button>
                                                        <button class="btn btn-sm btn-secondary"
                                                            wire:click="$set('editingZone', null)">
                                                            <i class="bi bi-x"></i> Cancel
                                                        </button>
                                                    </td>
                                                @else
                                                    <td>{{ $counts['male'] }}</td>
                                                    <td>{{ $counts['female'] }}</td>
                                                    <td>{{ $counts['child'] }}</td>
                                                    <td>{{ $counts['infant'] }}</td>
                                                    <td>{{ array_sum(array_intersect_key($counts, array_flip(['male', 'female', 'child', 'infant']))) }}
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary"
                                                            wire:click="editZone('{{ $zoneName }}')">
                                                            <i class="bi bi-pencil"></i> Edit
                                                        </button>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                        <tr class="fw-bold">
                                            <td>Total</td>
                                            <td>{{ array_sum(array_column($paxDistribution, 'male')) }}</td>
                                            <td>{{ array_sum(array_column($paxDistribution, 'female')) }}</td>
                                            <td>{{ array_sum(array_column($paxDistribution, 'child')) }}</td>
                                            <td>{{ array_sum(array_column($paxDistribution, 'infant')) }}</td>
                                            <td colspan="2">
                                                {{ array_sum(
                                                    array_map(fn($zone) => array_sum(array_intersect_key($zone, array_flip(['male', 'female', 'child', 'infant']))), $paxDistribution),
                                                ) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <livewire:flight.settings :flight="$flight" />
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('livewire:initialized', function() {
            const chartValues = @json($distribution['trim_data'] ?? []);
            const ctx = document.getElementById('trimSheetChart').getContext('2d');
            const trimSheetChart = new Chart(ctx, {
                type: 'scatter',
                data: {
                    datasets: [{
                            label: 'ZFW Envelope',
                            data: chartValues.zfwEnvelope || [],
                            borderColor: 'red',
                            showLine: true,
                            pointRadius: 0,
                            fill: false,
                        },
                        {
                            label: 'TOW Envelope',
                            data: chartValues.towEnvelope || [],
                            borderColor: 'blue',
                            showLine: true,
                            pointRadius: 0,
                            fill: false,
                        },
                        {
                            label: 'LDW Envelope',
                            data: chartValues.ldwEnvelope || [],
                            borderColor: 'green',
                            showLine: true,
                            pointRadius: 0,
                            fill: false,
                        },
                        {
                            label: 'ZFW',
                            data: [{
                                x: {{ $distribution['indices']['lizfw'] ?? 0 }},
                                y: {{ $distribution['weights']['zero_fuel_weight'] ?? 0 }}
                            }],
                            backgroundColor: 'red',
                            pointRadius: 4
                        },
                        {
                            label: 'TOW',
                            data: [{
                                x: {{ $distribution['indices']['litow'] ?? 0 }},
                                y: {{ $distribution['weights']['takeoff_weight'] ?? 0 }}
                            }],
                            backgroundColor: 'blue',
                            pointRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            type: 'linear',
                            title: {
                                display: true,
                                text: 'Index'
                            },
                            min: 25,
                            max: 100
                        },
                        y: {
                            type: 'linear',
                            title: {
                                display: true,
                                text: 'Aircraft Weight (kg)'
                            },
                            min: 40600,
                            max: 85000
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += '(Index ' + (context.raw.x).toFixed(2) + ', Weight ' + (context.raw.y)
                                        .toLocaleString() + ' kg)';
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</div>
