<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Loadsheet for {{ $flight->flight_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.2;
            color: #333;
            margin: 0;
            padding: 5px;
        }

        .container {
            max-width: 100%;
            margin: 0 auto;
        }
    </style>
</head>

<body>
    @include('livewire.flights.partials.loadsheet')
</body>

</html>