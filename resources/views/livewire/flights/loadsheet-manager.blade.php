<div class="mb-4">
    <div class="row g-2">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Load Sheet</h4>
                    <div class="d-flex gap-2 mb-0">
                        @if ($loadsheet)
                            @if (!$loadsheet->final && $loadsheet->status !== 'revoked')
                                <button class="btn btn-success btn-sm mb-0" wire:click="finalizeLoadsheet">
                                    <i class="bi bi-check2-circle"></i> Finalize Loadsheet
                                </button>
                            @elseif($loadsheet->status === 'released')
                                <button class="btn btn-secondary btn-sm mb-0" wire:click="revokeLoadsheet"
                                    wire:confirm="Are you sure you want to revoke this loadsheet?">
                                    <i class="bi bi-trash-fill"></i> Revoke Loadsheet
                                </button>
                            @endif
                        @endif

                        @if (!$loadsheet || $loadsheet->status === 'revoked')
                            <button class="btn btn-primary btn-sm mb-0" wire:click="generateLoadsheet"
                                {{ !$flight->fuel || !$loadplan || $loadplan->status !== 'released' ? 'disabled' : '' }}>
                                <i class="bi bi-plus-circle"></i> Generate Loadsheet
                            </button>
                        @endif
                    </div>
                </div>
                @if ($loadsheet)
                    <div class="card-body p-2">
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
                        <div style="font-family: monospace;">
                            <p class="mb-0 ms-1">
                                @if (!$loadsheet->final)
                                    <span>PRELIMINARY LOADSHEET</span>
                                @endif
                            </p>
                            <table class="table table-sm table-borderless m-0">
                                <tbody>
                                    <tr>
                                        <td>LOADSHEET</td>
                                        <td>CHECKED</td>
                                        <td>APPROVED</td>
                                        <td>EDNO</td>
                                    </tr>
                                    <tr>
                                        <td>ALL WEIGHTS IN KILOS</td>
                                        <td class="text-uppercase">{{ $loadsheet->creator->name ?? 'N/A' }}</td>
                                        <td></td>
                                        <td>{{ $loadsheet->edition }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            <table class="table table-sm table-borderless m-0">
                                <tr>
                                    <td>FROM/TO</td>
                                    <td>FLIGHT</td>
                                    <td>A/C REG</td>
                                    <td>VERSION</td>
                                    <td>CREW</td>
                                    <td>DATE</td>
                                    <td>TIME</td>
                                </tr>
                                <tr>
                                    <td>{{ $distribution['flight']['sector'] ?? 'N/A' }}</td>
                                    <td>{{ $distribution['flight']['flight_number'] ?? 'N/A' }}</td>
                                    <td>{{ $distribution['flight']['registration'] ?? 'N/A' }}</td>
                                    <td>{{ $distribution['flight']['version'] ?? 'N/A' }}</td>
                                    <td>{{ $distribution['fuel']['crew'] ?? 'N/A' }}</td>
                                    <td>{{ $distribution['flight']['flight_date'] ?? 'N/A' }}</td>
                                    <td>{{ $distribution['flight']['release_time'] ?? now('Asia/Qatar')->format('Hi') }}</td>
                                </tr>
                            </table>
                            <table class="table table-sm table-borderless m-0">
                                <tr>
                                    <td style="width: 50%;">WEIGHT</td>
                                    <td style="width: 50%;">DISTRIBUTION</td>
                                </tr>
                                <tr>
                                    <td>LOAD IN COMPARTMENTS</td>
                                    <td>
                                        @forelse ($pax['hold_breakdown'] as $hold)
                                            {{ $hold['hold_no'] }}/{{ $hold['weight'] }}
                                        @empty
                                            NIL
                                        @endforelse
                                    </td>
                                </tr>
                                <tr>
                                    <td>PASSENGER/CABIN BAG</td>
                                    <td>
                                        @forelse ($pax['pax_by_type'] as $type => $count)
                                            {{ $count['count'] . '/' }}
                                        @empty
                                            NIL
                                        @endforelse
                                        <span class="ms-3">TTL {{ $totalPax }} CAB 0</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>Y {{ $totalPax }} SOC 0/0</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>BLKD 0</td>
                                </tr>
                            </table>
                            <hr class="my-0">
                            <table class="table table-sm table-borderless m-0">
                                <tr>
                                    <td>TOTAL TRAFFIC LOAD</td>
                                    <td>{{ $distribution['flight']['total_traffic_load'] ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td>DRY OPERATING WEIGHT</td>
                                    <td>{{ $distribution['weights']['dry_operating_weight'] ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td>ZERO FUEL WEIGHT ACTUAL</td>
                                    <td>{{ $distribution['weights']['zero_fuel_weight'] ?? 'N/A' }} MAX
                                        {{ $flight->aircraft->type->max_zero_fuel_weight }} ADJ
                                    </td>
                                </tr>
                                <tr>
                                    <td>TAKE OFF FUEL</td>
                                    <td>{{ $distribution['fuel']['takeoff'] }}</td>
                                </tr>
                                <tr>
                                    <td>TAKE OFF WEIGHT ACTUAL</td>
                                    <td>{{ $distribution['weights']['takeoff_weight'] ?? 'N/A' }} MAX
                                        {{ $flight->aircraft->type->max_takeoff_weight }} ADJ
                                    </td>
                                </tr>
                                <tr>
                                    <td>TRIP FUEL</td>
                                    <td>{{ $distribution['fuel']['trip'] }}</td>
                                </tr>
                                <tr>
                                    <td>LANDING WEIGHT ACTUAL</td>
                                    <td>{{ $distribution['weights']['landing_weight'] ?? 'N/A' }} MAX
                                        {{ $flight->aircraft->type->max_landing_weight }} ADJ
                                    </td>
                                </tr>
                            </table>
                            <hr class="my-0">
                            <div>BALANCE / SEATING CONDITIONS</div>
                            <table class="table table-sm table-borderless m-0">
                                <tr>
                                    <td>DOI: {{ number_format($distribution['indices']['doi'], 2) ?? 'N/A' }}</td>
                                    <td>DLI: {{ number_format($distribution['indices']['dli'], 2) ?? 'N/A' }}</td>
                                    <td>LAST MINUTE CHANGES</td>
                                </tr>
                                <tr>
                                    <td>LIZFW: {{ number_format($distribution['indices']['lizfw'], 2) ?? 'N/A' }}</td>
                                    <td>LITOW: {{ number_format($distribution['indices']['litow'], 2) ?? 'N/A' }}</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>MACZFW: {{ number_format($distribution['indices']['maczfw'], 2) ?? 'N/A' }}</td>
                                    <td>MACTOW: {{ number_format($distribution['indices']['mactow'], 2) ?? 'N/A' }}</td>
                                    <td></td>
                                </tr>
                            </table>
                            <div>STAB TRIM SETTING</div>
                            <div>STAB TO 1.9 NOSE UP</div>
                            <div>TRIM BY SEAT ROW</div>
                            <table class="table table-sm table-borderless m-0">
                                <tr>
                                    <td style="width: 35%">UNDERLOAD BEFORE LMC</td>
                                    <td>{{ $distribution['flight']['underload'] ?? 'N/A' }}</td>
                                    <td>LMC TOTAL</td>
                                </tr>
                            </table>
                            <hr class="my-0">
                            <div>LOADMESSAGE AND CAPTAIN'S INFORMATION BEFORE LMC</div>
                            <div>TAXI FUEL: {{ $distribution['fuel']['taxi'] ?? 'N/A' }}</div>
                            {{-- LDM --}}
                            <div style="font-family: monospace;">
                                <div class="mt-3">LDM</div>
                                <div>
                                    {{ $distribution['flight']['flight_number'] }}/{{ $distribution['flight']['short_flight_date'] }}.
                                    {{ $distribution['flight']['registration'] }}.
                                    {{ $distribution['flight']['version'] }}.
                                    {{ $distribution['fuel']['crew'] ?? 'N/A' }}
                                </div>
                                <div>
                                    -{{ $distribution['flight']['destination'] }}.
                                    @forelse ($pax['pax_by_type'] as $count)
                                        {{ $count['count'] . '/' }}
                                    @empty
                                        NIL
                                    @endforelse
                                    T{{ $distribution['flight']['total_deadload'] }}.PAX/{{ $totalPax }}.PAD/0
                                </div>
                                <div>
                                    SI PAX WEIGHTS USED
                                    @foreach ($pax['passenger_weights_used'] as $type => $weight)
                                        {{ strtoupper($type[0]) }}{{ $weight }}
                                    @endforeach
                                    &nbsp; BAG WGT: ACTUAL
                                </div>
                                <div>
                                    {{ $distribution['flight']['destination'] }}
                                    @forelse ($pax['deadload_by_type'] as $type => $weight)
                                        {{ $type }} {{ $weight['weight'] }}
                                    @empty
                                        C 0 M 0 B 0/0
                                    @endforelse
                                    O 0 &nbsp; T {{ $distribution['flight']['total_deadload'] }}
                                </div>
                                <div>PANTRY CODE {{ $distribution['indices']['pantry']['code'] }}</div>
                                <div>ACTUAL LOADING OF AIRCRAFT</div>
                                <div>
                                    @forelse ($pax['hold_breakdown'] as $hold)
                                        <div>CPT{{ $hold['hold_no'] }}/{{ $hold['weight'] }}</div>
                                    @empty
                                        NIL
                                    @endforelse
                                </div>
                                <br>
                                <div>AIRCRAFT TYPE: {{ $flight->aircraft->type->name }}</div>
                                <div>NOTOC: {{ $distribution['flight']['notoc_required'] ? 'YES' : 'NO' }}</div>
                                <br>
                                <div>
                                    {{ $distribution['flight']['destination'] }} &nbsp;&nbsp;
                                    @forelse ($pax['deadload_by_type'] as $type => $weight)
                                        {{ $type }} {{ $weight['weight'] }} &nbsp;&nbsp;
                                    @empty
                                        C 0 M 0 B 0/0
                                    @endforelse
                                    TRA 0
                                </div>
                            </div>
                            <div>END LOADSHEET EDNO {{ $loadsheet->edition }} -
                                {{ $flight->flight_number }}/{{ $flight->scheduled_departure_time->format('d') }}
                                {{ $flight->scheduled_departure_time->format('Hi') }}
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card-body">
                        <div class="text-center py-4">
                            @if (!$flight->fuel)
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i> Fuel data must be added before generating loadsheet.
                                </div>
                            @endif
                            @if (!$loadplan || $loadplan->status !== 'released')
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i> Load plan must be released before generating loadsheet.
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
                            <button class="btn btn-sm btn-primary" wire:click="refreshTrimSheet"
                                @disabled(!$loadsheet)>
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
                                                        <button class="btn btn-sm btn-success" wire:click="saveZoneDistribution">
                                                            <i class="bi bi-check"></i> Save
                                                        </button>
                                                        <button class="btn btn-sm btn-secondary" wire:click="$set('editingZone', null)">
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
