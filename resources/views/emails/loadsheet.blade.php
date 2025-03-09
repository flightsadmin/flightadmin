<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Loadsheet - {{ $flight->flight_number }}</title>

    <style>
        body {
            font-size: 12px;
            font-family: 'Courier New', Courier, monospace;
        }

        .card {
            border-radius: 0;
            margin: 2;
            padding: 0;
        }

        .card-body {
            padding: 0;
        }

        table {
            width: 60%;
            border-collapse: collapse;
        }

        hr {
            border: 0;
            border-top: 1px dashed #333;
            width: 60%;
            margin-left: 0;
        }
    </style>
</head>

<body>
    @if ($loadsheet)
        @include('livewire.flights.partials.loadsheet')
    @endif
</body>

</html>