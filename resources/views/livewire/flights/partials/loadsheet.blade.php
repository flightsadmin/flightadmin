<div class="card">
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
                <div>AIRCRAFT TYPE: {{ strtoupper($flight->aircraft->type->name) }}</div>
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
</div>