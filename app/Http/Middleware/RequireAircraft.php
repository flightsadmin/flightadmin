<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireAircraft
{
    public function handle(Request $request, Closure $next): Response
    {
        // Get the flight from the route parameter
        $flight = $request->route('flight');

        // If there's no flight parameter, proceed
        if (!$flight) {
            return $next($request);
        }

        // If the flight has no aircraft assigned, set a session flag
        if (!$flight->aircraft_id) {
            session()->flash('aircraft_required', true);
        }

        return $next($request);
    }
}