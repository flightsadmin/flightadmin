<?php

namespace App\Livewire\AircraftType;

use App\Models\AircraftType;
use App\Models\Envelope;
use Livewire\Component;

class EnvelopeManager extends Component
{
    public AircraftType $aircraftType;

    public $showEnvelopeModal = false;

    public $editingEnvelope = null;

    public $envelopeForm = [
        'name' => '',
        'points' => [],
        'is_active' => true,
    ];

    public $showViewModal = false;

    public $viewingEnvelope = null;

    protected $rules = [
        'envelopeForm.name' => 'required|string|max:255',
        'envelopeForm.points' => 'required|array|min:3',
        'envelopeForm.points.*.weight' => 'required|numeric|min:0',
        'envelopeForm.points.*.index' => 'required|numeric',
        'envelopeForm.is_active' => 'boolean',
    ];

    public function addPoint()
    {
        $this->envelopeForm['points'][] = [
            'weight' => '',
            'index' => '',
        ];
    }

    public function removePoint($index)
    {
        unset($this->envelopeForm['points'][$index]);
        $this->envelopeForm['points'] = array_values($this->envelopeForm['points']);
    }

    public function editEnvelope(Envelope $envelope)
    {
        $this->editingEnvelope = $envelope;
        $this->envelopeForm = [
            'name' => $envelope->name,
            'points' => $envelope->points,
            'is_active' => $envelope->is_active,
        ];
        $this->showEnvelopeModal = true;
    }

    public function saveEnvelope()
    {
        $this->validate();

        if ($this->editingEnvelope) {
            $this->editingEnvelope->update($this->envelopeForm);
            $message = 'Envelope updated successfully.';
        } else {
            $this->aircraftType->envelopes()->create($this->envelopeForm);
            $message = 'Envelope created successfully.';
        }

        $this->dispatch('alert', icon: 'success', message: $message);
        $this->dispatch('envelope-saved');
        $this->reset('envelopeForm', 'editingEnvelope', 'showEnvelopeModal');
    }

    public function toggleStatus(Envelope $envelope)
    {
        $envelope->update(['is_active' => ! $envelope->is_active]);
        $this->dispatch('alert', icon: 'success', message: 'Envelope status updated.');
    }

    public function deleteEnvelope(Envelope $envelope)
    {
        $envelope->delete();
        $this->dispatch('alert', icon: 'success', message: 'Envelope deleted successfully.');
    }

    public function viewEnvelope(Envelope $envelope)
    {
        $this->viewingEnvelope = $envelope;
        $this->showViewModal = true;
    }

    public function render()
    {
        return view('livewire.aircraft_type.envelope-manager', [
            'envelopes' => $this->aircraftType->envelopes()->get(),
        ]);
    }
}
