<?php

namespace App\Livewire\Components;

use App\Models\Flight;
use Livewire\Component;
use Livewire\WithPagination;

class Sidebar extends Component
{
    use WithPagination;

    public $selectedDate;

    public $selectedFlight;

    public function mount()
    {
        $this->selectedDate = session('selectedDate', now()->toDateString());
        $this->selectedFlight = request()->route('flight');
    }

    public function updatedSelectedDate()
    {
        session(['selectedDate' => $this->selectedDate]);
        $this->resetPage();
    }

    public function setActiveFlight($id)
    {
        $this->selectedFlight = Flight::findOrFail($id);
    }

    public function render()
    {
        $flights = Flight::with('airline', 'baggage')
            ->whereDate('scheduled_departure_time', $this->selectedDate)
            ->orderBy('scheduled_departure_time')
            ->simplePaginate(15);

        return view('livewire.components.sidebar', compact('flights'));
    }
}
