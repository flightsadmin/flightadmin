<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Overview</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-sm">
                    <tbody>
                        <tr>
                            <th>Code:</th>
                            <td>{{ $aircraftType->code }}</td>
                        </tr>
                        <tr>
                            <th>Name:</th>
                            <td>{{ $aircraftType->name }}</td>
                        </tr>
                        <tr>
                            <th>Manufacturer:</th>
                            <td>{{ $aircraftType->manufacturer }}</td>
                        </tr>
                        <tr>
                            <th>Category:</th>
                            <td>{{ $aircraftType->category }}</td>
                        </tr>
                        <tr>
                            <th>Max Deck Crew:</th>
                            <td>{{ $aircraftType->max_deck_crew }}</td>
                        </tr>
                        <tr>
                            <th>Max Cabin Crew:</th>
                            <td>{{ $aircraftType->max_cabin_crew }}</td>
                        </tr>
                        <tr>
                            <th>Max Passengers:</th>
                            <td>{{ number_format($aircraftType->max_passengers) }} pax</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm">
                    <tbody>
                        <tr>
                            <th>Empty Weight:</th>
                            <td>{{ number_format($aircraftType->empty_weight) }} kg</td>
                        </tr>
                        <tr>
                            <th>Max Zero Fuel Weight:</th>
                            <td>{{ number_format($aircraftType->max_zero_fuel_weight) }} kg</td>
                        </tr>
                        <tr>
                            <th>Max Takeoff Weight:</th>
                            <td>{{ number_format($aircraftType->max_takeoff_weight) }} kg</td>
                        </tr>
                        <tr>
                            <th>Max Landing Weight:</th>
                            <td>{{ number_format($aircraftType->max_landing_weight) }} kg</td>
                        </tr>
                        <tr>
                            <th>Cargo Capacity:</th>
                            <td>{{ number_format($aircraftType->cargo_capacity) }} kg</td>
                        </tr>
                        <tr>
                            <th>Max Fuel Capacity:</th>
                            <td>{{ number_format($aircraftType->max_fuel_capacity) }} L</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
