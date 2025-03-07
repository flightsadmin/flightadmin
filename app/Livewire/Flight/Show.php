<?php

namespace App\Livewire\Flight;

use App\Models\Flight;
use Livewire\Attributes\On;
use Livewire\Component;

class Show extends Component
{
    public Flight $flight;

    public $activeTab;

    protected function loadFlightWithCounts()
    {
        $this->flight = $this->flight->fresh()->load([
            'airline',
            'aircraft',
            'crew',
            'passengers',
            'baggage',
            'cargo',
            'fuel',
        ])->loadCount([
            'baggage',
            'cargo',
            'passengers',
            'crew',
            'passengers as boarded_count' => function ($query) {
                $query->where('boarding_status', 'boarded');
            },
            'passengers as unboarded_count' => function ($query) {
                $query->where('boarding_status', 'unboarded');
            },
            'passengers as accepted_count' => function ($query) {
                $query->where('acceptance_status', 'accepted');
            },
            'passengers as standby_count' => function ($query) {
                $query->where('acceptance_status', 'standby');
            },
            'passengers as offloaded_count' => function ($query) {
                $query->where('acceptance_status', 'offloaded');
            },
        ]);
    }

    public function mount(Flight $flight)
    {
        $this->flight = $flight;
        $this->loadFlightWithCounts();

        // If no aircraft and not on overview tab, force overview tab
        $requestedTab = session('flight_tab', 'overview');
        if (! $this->flight->aircraft_id && $requestedTab !== 'overview') {
            $this->activeTab = 'overview';
            session(['flight_tab' => 'overview']);

            // Show warning if redirected by middleware
            if (session()->has('aircraft_required')) {
                $this->dispatch('alert', icon: 'warning', message: 'Aircraft must be assigned before accessing other tabs.');
                session()->forget('aircraft_required');
            }
        } else {
            $this->activeTab = $requestedTab;
        }
    }

    public function setTab($tab)
    {
        // If no aircraft and not overview tab, show warning
        if (! $this->flight->aircraft_id && $tab !== 'overview') {
            $this->dispatch('alert', icon: 'warning', message: 'Aircraft must be assigned before accessing this tab.');

            return;
        }

        $this->activeTab = $tab;
        session(['flight_tab' => $tab]);
        $this->loadFlightWithCounts();
    }

    #[On('aircraft-updated')]
    public function render()
    {
        return view('livewire.flights.show');
    }

    // Listen for events from child components
    protected $listeners = [
        'passenger-saved' => 'refreshCounts',
        'baggage-saved' => 'refreshCounts',
        'cargo-saved' => 'refreshCounts',
        'crew-saved' => 'refreshCounts',
        'boarding-updated' => 'refreshCounts',
    ];

    public function refreshCounts()
    {
        $this->loadFlightWithCounts();
    }
}
