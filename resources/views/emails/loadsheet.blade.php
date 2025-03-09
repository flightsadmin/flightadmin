<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Loadsheet for {{ $flight->flight_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
        }

        .header {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #0d6efd;
        }

        .flight-info {
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
        }

        .flight-info table {
            width: 100%;
            border-collapse: collapse;
        }

        .flight-info td {
            padding: 5px;
        }

        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #6c757d;
            text-align: center;
        }

        .note {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 10px;
            margin: 20px 0;
            border-radius: 0 5px 5px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h2>Flight Loadsheet</h2>
            <p>Please find attached the loadsheet for flight {{ $flight->flight_number }}.</p>
        </div>

        <div class="flight-info">
            <table>
                <tr>
                    <td><strong>Airline:</strong></td>
                    <td>{{ $flight->airline->name }}</td>
                    <td><strong>Flight:</strong></td>
                    <td>{{ $flight->flight_number }}</td>
                </tr>
                <tr>
                    <td><strong>Route:</strong></td>
                    <td>{{ $flight->departure_airport }} - {{ $flight->arrival_airport }}</td>
                    <td><strong>Date:</strong></td>
                    <td>{{ $flight->scheduled_departure_time->format('d M Y') }}</td>
                </tr>
                <tr>
                    <td><strong>STD:</strong></td>
                    <td>{{ $flight->scheduled_departure_time->format('H:i') }}</td>
                    <td><strong>Aircraft:</strong></td>
                    <td>{{ $flight->aircraft->registration_number ?? 'Not assigned' }}</td>
                </tr>
            </table>
        </div>

        <div class="note">
            <p><strong>Note:</strong> This loadsheet is attached as a PDF document. Please review it carefully before flight departure.</p>
        </div>

        <p>The loadsheet has been finalized and is ready for use. If you have any questions or need any changes, please contact the
            operations team immediately.</p>

        <div class="footer">
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>&copy; {{ date('Y') }} Flight Admin System. All rights reserved.</p>
        </div>
    </div>
</body>

</html>
