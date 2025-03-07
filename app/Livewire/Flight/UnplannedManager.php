<?php

namespace App\Livewire\Flight;

use App\Models\Flight;
use Livewire\Attributes\On;
use Livewire\Component;

class UnplannedManager extends Component
{
    public Flight $flight;

    public $unplannedItems = [];

    public $targetPositionId = null;

    public $selectedType = null;

    public $maxPieces = 0;

    public $inputPieces = null;

    protected $listeners = ['container_position_updated' => '$refresh'];

    public function mount(Flight $flight)
    {
        $this->flight = $flight;
        $this->initializeUnplannedItems();
    }

    protected function initializeUnplannedItems()
    {
        $unplannedBaggage = $this->flight->baggage()->whereNull('container_id')->get();

        $this->unplannedItems['baggage'] = [
            'total_pieces' => $unplannedBaggage->count(),
            'total_weight' => $unplannedBaggage->sum('weight'),
        ];

        $unplannedCargo = $this->flight->cargo()->whereNull('container_id')->get();

        $this->unplannedItems['cargo'] = [
            'total_pieces' => $unplannedCargo->sum('pieces'),
            'total_weight' => $unplannedCargo->sum('weight'),
        ];
    }

    #[On('open-pieces-modal')]
    public function openPiecesModal($data)
    {
        $this->targetPositionId = $data['positionId'] ?? null;
        $this->selectedType = $data['type'] ?? null;

        if ($this->selectedType === 'baggage') {
            $this->maxPieces = $this->unplannedItems['baggage']['pieces'];
        } else {
            $this->maxPieces = $this->unplannedItems['cargo']['pieces'];
        }

        $this->dispatch('showModal');
    }

    public function confirmAdd()
    {
        if (! $this->targetPositionId || ! $this->selectedType) {
            return;
        }

        $piecesToAdd = $this->inputPieces ?? $this->unplannedItems[$this->selectedType]['total_pieces'];

        if ($piecesToAdd > $this->unplannedItems[$this->selectedType]['total_pieces']) {
            $this->dispatch('alert', icon: 'error', message: 'Cannot add more pieces than available');

            return;
        }

        try {
            // Calculate weight based on proportion of pieces
            $totalPieces = $this->unplannedItems[$this->selectedType]['total_pieces'];
            $totalWeight = $this->unplannedItems[$this->selectedType]['total_weight'];
            $weightToAdd = ($piecesToAdd / $totalPieces) * $totalWeight;

            // Update the unplanned items totals
            $this->unplannedItems[$this->selectedType]['total_pieces'] -= $piecesToAdd;
            $this->unplannedItems[$this->selectedType]['total_weight'] -= $weightToAdd;

            // Dispatch event to parent to update the bulk position
            $this->dispatch('unplanned-items-added', [
                'positionId' => $this->targetPositionId,
                'type' => $this->selectedType,
                'pieces' => $piecesToAdd,
                'weight' => round($weightToAdd, 1),
            ]);

            $this->dispatch('hideModal');
            $this->resetModal();
            $this->dispatch('alert', icon: 'success', message: ucfirst($this->selectedType).' added to position successfully');

        } catch (\Exception $e) {
            $this->dispatch('alert', icon: 'error', message: 'Failed to add '.$this->selectedType.' to position');
        }
    }

    public function resetModal()
    {
        $this->targetPositionId = null;
        $this->selectedType = null;
        $this->inputPieces = null;
    }

    public function render()
    {
        return view('livewire.flights.unplanned-manager');
    }
}
