<?php

namespace App\Livewire\AircraftType;

use App\Models\AircraftType;
use App\Models\Seat;
use Livewire\Component;

class SeatManager extends Component
{
    public AircraftType $aircraftType;

    public $showBulkCreateModal = false;

    public $bulkForm = [
        'start_row' => 1,
        'end_row' => 10,
        'columns' => ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'],
        'cabin_zone_id' => '',
        'type' => 'economy',
    ];

    public $editingSeat = null;

    public $seatForm = [
        'cabin_zone_id' => '',
        'type' => 'economy',
        'is_exit' => false,
        'is_blocked' => false,
        'notes' => '',
    ];

    public function createSeats()
    {
        $this->validate([
            'bulkForm.start_row' => 'required|integer|min:1',
            'bulkForm.end_row' => 'required|integer|min:1|gte:bulkForm.start_row',
            'bulkForm.columns' => 'required|array|min:1',
            'bulkForm.columns.*' => 'required|string|size:1',
            'bulkForm.cabin_zone_id' => 'required|exists:cabin_zones,id',
            'bulkForm.type' => 'required|in:economy,business,first',
        ]);

        for ($row = $this->bulkForm['start_row']; $row <= $this->bulkForm['end_row']; $row++) {
            foreach ($this->bulkForm['columns'] as $column) {
                $this->aircraftType->seats()->create([
                    'cabin_zone_id' => $this->bulkForm['cabin_zone_id'],
                    'row' => $row,
                    'column' => $column,
                    'designation' => $row.$column,
                    'type' => $this->bulkForm['type'],
                ]);
            }
        }

        $this->dispatch('alert', icon: 'success', message: 'Seats created successfully.');
        $this->reset('bulkForm', 'showBulkCreateModal');
    }

    public function editSeat(Seat $seat)
    {
        $this->editingSeat = $seat;
        $this->seatForm = $seat->only([
            'cabin_zone_id',
            'type',
            'is_exit',
            'is_blocked',
            'notes',
        ]);
    }

    public function updateSeat()
    {
        $this->validate([
            'seatForm.cabin_zone_id' => 'required|exists:cabin_zones,id',
            'seatForm.type' => 'required|in:economy,business,first',
            'seatForm.is_exit' => 'boolean',
            'seatForm.is_blocked' => 'boolean',
            'seatForm.notes' => 'nullable|string',
        ]);

        $this->editingSeat->update($this->seatForm);
        $this->dispatch('alert', icon: 'success', message: 'Seat updated successfully.');
        $this->reset('editingSeat', 'seatForm');
    }

    public function deleteSeat(Seat $seat)
    {
        $seat->delete();
        $this->dispatch('alert', icon: 'success', message: 'Seat deleted successfully.');
    }

    public function deleteZoneSeats($zoneId)
    {
        $this->aircraftType->seats()
            ->where('cabin_zone_id', $zoneId)
            ->delete();

        $this->dispatch('alert', icon: 'success', message: 'Zone seats deleted successfully.');
    }

    public function render()
    {
        $seats = $this->aircraftType->seats()
            ->with('cabinZone')
            ->orderBy('row')
            ->orderBy('column')
            ->get()
            ->groupBy('cabin_zone_id');

        return view('livewire.aircraft_type.seat-manager', [
            'seatsByZone' => $seats,
            'cabinZones' => $this->aircraftType->cabinZones,
        ]);
    }
}
