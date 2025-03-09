<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Loadsheet</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #000;
            padding-bottom: 10px;
        }

        .title {
            font-size: 18pt;
            font-weight: bold;
        }

        .subtitle {
            font-size: 12pt;
        }

        .flight-info {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .flight-info th,
        .flight-info td {
            border: 1px solid #000;
            padding: 5px;
        }

        .flight-info th {
            background-color: #f0f0f0;
            text-align: left;
        }

        .section {
            margin-top: 15px;
            margin-bottom: 15px;
        }

        .section-title {
            font-weight: bold;
            border-bottom: 1px solid #000;
            margin-bottom: 5px;
        }

        .weight-table {
            width: 100%;
            border-collapse: collapse;
        }

        .weight-table th,
        .weight-table td {
            border: 1px solid #000;
            padding: 5px;
        }

        .weight-table th {
            background-color: #f0f0f0;
        }

        .footer {
            margin-top: 30px;
            font-size: 8pt;
            text-align: center;
            border-top: 1px solid #000;
            padding-top: 10px;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="title">LOADSHEET</div>
        <div class="subtitle">{{ $flight->airline->name }} ({{ $flight->airline->iata }})</div>
    </div>

    <table class="flight-info">
        <tr>
            <th>Flight Number:</th>
            <td>{{ $flight->airline->iata }}{{ $flight->flight_number }}</td>
            <th>Date:</th>
            <td>{{ $flight->scheduled_departure_time->format('d M Y') }}</td>
        </tr>
        <tr>
            <th>From:</th>
            <td>{{ $flight->departure_airport }}</td>
            <th>To:</th>
            <td>{{ $flight->arrival_airport }}</td>
        </tr>
        <tr>
            <th>STD:</th>
            <td>{{ $flight->scheduled_departure_time->format('H:i') }}</td>
            <th>STA:</th>
            <td>{{ $flight->scheduled_arrival_time->format('H:i') }}</td>
        </tr>
        <tr>
            <th>Aircraft Type:</th>
            <td>{{ $flight->aircraft->type->code }}</td>
            <th>Registration:</th>
            <td>{{ $flight->aircraft->registration_number }}</td>
        </tr>
    </table>

    <div class="section">
        <div class="section-title">PASSENGER INFORMATION</div>
        <table class="weight-table">
            <tr>
                <th>Type</th>
                <th>Count</th>
                <th>Weight (kg)</th>
            </tr>
            @foreach ($distribution['load_data']['pax_by_type'] as $type => $data)
                <tr>
                    <td>{{ ucfirst($type) }}</td>
                    <td>{{ $data['count'] }}</td>
                    <td>{{ $data['weight'] }}</td>
                </tr>
            @endforeach
            <tr>
                <th>Total</th>
                <td>{{ array_sum(array_column($distribution['load_data']['pax_by_type'], 'count')) }}</td>
                <td>{{ array_sum(array_column($distribution['load_data']['pax_by_type'], 'weight')) }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">WEIGHT INFORMATION (KG)</div>
        <table class="weight-table">
            <tr>
                <th>Description</th>
                <th>Weight</th>
            </tr>
            <tr>
                <td>Dry Operating Weight (DOW)</td>
                <td>{{ $distribution['weights']['dry_operating_weight'] }}</td>
            </tr>
            <tr>
                <td>Zero Fuel Weight (ZFW)</td>
                <td>{{ $distribution['weights']['zero_fuel_weight'] }}</td>
            </tr>
            <tr>
                <td>Take-off Weight (TOW)</td>
                <td>{{ $distribution['weights']['takeoff_weight'] }}</td>
            </tr>
            <tr>
                <td>Landing Weight (LDW)</td>
                <td>{{ $distribution['weights']['landing_weight'] }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">FUEL INFORMATION (KG)</div>
        <table class="weight-table">
            <tr>
                <th>Description</th>
                <th>Weight</th>
            </tr>
            <tr>
                <td>Block Fuel</td>
                <td>{{ $distribution['fuel']['block'] }}</td>
            </tr>
            <tr>
                <td>Taxi Fuel</td>
                <td>{{ $distribution['fuel']['taxi'] }}</td>
            </tr>
            <tr>
                <td>Trip Fuel</td>
                <td>{{ $distribution['fuel']['trip'] }}</td>
            </tr>
            <tr>
                <td>Take-off Fuel</td>
                <td>{{ $distribution['fuel']['takeoff'] }}</td>
            </tr>
        </table>
    </div>

    @if (!empty($distribution['load_data']['hold_breakdown']))
        <div class="section">
            <div class="section-title">HOLD BREAKDOWN</div>
            <table class="weight-table">
                <tr>
                    <th>Hold</th>
                    <th>Weight (kg)</th>
                    <th>Index</th>
                </tr>
                @foreach ($distribution['load_data']['hold_breakdown'] as $hold)
                    <tr>
                        <td>{{ $hold['hold_no'] }}</td>
                        <td>{{ $hold['weight'] }}</td>
                        <td>{{ $hold['index'] }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

    <div class="footer">
        <p>Loadsheet generated on {{ $loadsheet->released_at ? $loadsheet->released_at->format('d M Y H:i') : now()->format('d M Y H:i') }}
        </p>
        <p>Released by: {{ $loadsheet->released_by ? \App\Models\User::find($loadsheet->released_by)->name : 'System' }}</p>
        <p>This document is computer generated and valid without signature.</p>
    </div>
</body>

</html>
