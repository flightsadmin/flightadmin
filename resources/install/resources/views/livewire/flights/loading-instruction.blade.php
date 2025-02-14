<div class="container-fluid">
    <!-- Table format for the pre content -->
    <table class="table table-borderless mb-0" style="font-family: monospace; font-size: small; border-collapse: collapse;">
        <tbody>
            <tr>
                <td style="padding: 0; line-height: 1.5;">LOADING INSTRUCTION/REPORT</td>
                <td style="padding: 0; line-height: 1.5;"></td>
                <td style="padding: 0; line-height: 1.5;">PREPARED</td>
                <td style="padding: 0; line-height: 1.5;"></td>
                <td style="padding: 0; line-height: 1.5;"></td>
                <td style="padding: 0; line-height: 1.5;">VERSION</td>
            </tr>
            <tr>
                <td style="padding: 0; line-height: 1.5;">ALL WEIGHTS IN KILOS</td>
                <td style="padding: 0; line-height: 1.5;"></td>
                <td style="padding: 0; line-height: 1.5;">N/A</td>
                <td style="padding: 0; line-height: 1.5;"></td>
                <td style="padding: 0; line-height: 1.5;"></td>
                <td style="padding: 0; line-height: 1.5;">{{ $loadplan->version }}</td>
            </tr>
            <tr>
                <td style="padding: 0; line-height: 1.5;">FLIGHT</td>
                <td style="padding: 0; line-height: 1.5;">DATE</td>
                <td style="padding: 0; line-height: 1.5;">FROM-TO</td>
                <td style="padding: 0; line-height: 1.5;">ETD</td>
                <td style="padding: 0; line-height: 1.5;">A-C/REG</td>
                <td style="padding: 0; line-height: 1.5;">PRINTED</td>
            </tr>
            <tr>
                <td style="padding: 0; line-height: 1.5;">{{ $flight->flight_number }}</td>
                <td style="padding: 0; line-height: 1.5;"> {{ strtoupper($flight->scheduled_departure_time->format('dMy')) }}</td>
                <td style="padding: 0; line-height: 1.5;"> {{ $flight->departure_airport . '-' . $flight->arrival_airport }}</td>
                <td style="padding: 0; line-height: 1.5;">{{ $flight->scheduled_departure_time->format('Hi') }}</td>
                <td style="padding: 0; line-height: 1.5;">{{ $flight->aircraft->registration_number }}</td>
                <td style="padding: 0; line-height: 1.5;">{{ now()->format('Hi') }}</td>
            </tr>
            <tr>
                <td colspan="7" style="padding: 0; line-height: 1.5;">PLANNED LOAD</td>
            </tr>
            <tr>
                <td colspan="7" style="padding: 0; line-height: 1.5;">{{ $flight->departure_airport }} OF OC OY / OC OM OB</td>
            </tr>
            <tr>
                <td colspan="7" style="padding: 0; line-height: 1.5;">LOADING SPECS: NIL</td>
            </tr>
            <tr>
                <td colspan="7" style="padding: 0; line-height: 1.5;">TRANSIT SPECS: NIL</td>
            </tr>
            <tr>
                <td colspan="7" style="padding: 0; line-height: 1.5;">RELOADS:</td>
            </tr>
        </tbody>
    </table>

    <!-- Rest of your document structure -->
    <dl class="row my-2"
        style="font-family: monospace; font-size: small; border-bottom: 1px dotted rgb(48, 46, 46); max-width: calc(100% - 30px); margin: 0 auto;">
        <dd class="col-6">LOADING INSTRUCTION</dd>
        <dd class="col-6">ACTUAL WEIGHTS</dd>
    </dl>

    @foreach (['FH' => 'FWD', 'AH' => 'AFT', 'BH' => 'BULK'] as $holdCode => $position)
        <dl class="row mb-0" style="font-family: monospace; font-size: small;">
            <dt class="col-6">
                CPT {{ $flight->aircraft->type->holds->where('code', $holdCode)->first()->code }} {{ $position }} MAX
                {{ $flight->aircraft->type->holds->where('code', $holdCode)->first()->max_weight }}
            </dt>
            <dt class="col-6">* CPT {{ $flight->aircraft->type->holds->where('code', $holdCode)->first()->code }} TOTAL:</dt>
        </dl>

        <dl class="row mb-0" style="font-family: monospace; font-size: small;">
            @foreach ($loadingInstructions->where('hold', $holdCode == 'FH' ? 'Forward Hold' : ($holdCode == 'AH' ? 'Aft Hold' : 'Bulk Hold'))->sortBy('position')->chunk(2) as $pair)
                @foreach ($pair as $container)
                    <div class="col-6">
                        <dt>:{{ $container['position'] }} {{ $container['container_number'] }}</dt>
                        <dd>:ONLOAD
                            {{ $container['is_empty'] ? '' : $container['destination'] . ' ' . ($container['content_type'] === 'baggage' ? 'BAG' : 'CGO') . '/' . $container['weight'] }}<br>
                            :REPORT
                        </dd>
                    </div>
                @endforeach
                <div class="col-12" style="border-bottom: 1px dotted rgb(48, 46, 46); max-width: calc(100% - 30px); margin: 0 auto;"></div>
            @endforeach
        </dl>
    @endforeach
    <div class="small my-2" style="font-family: monospace; line-height: 1.2;">
        THIS AIRCRAFT HAS BEEN LOADED IN ACCORDANCE WITH THESE INSTRUCTIONS AND THE DEVIATIONS SHOWN ON THIS REPORT. THE CONTAINER/PALLETS
        AND BULK LOAD HAVE BEEN SECURED IN ACCORDANCE WITH COMPANY INSTRUCTIONS.<br><br>
        NAME: ___________________________________________________________________<br><br>
        SIGNATURE: ______________________________________________________________
    </div>
</div>
