<?php

namespace App\Livewire\Flight;

use Livewire\Component;
use App\Models\Flight;
use App\Models\Loadsheet;
use App\Models\Loadplan;

class Documents extends Component
{
    public Flight $flight;
    public $loadsheets;
    public $loadplans;
    public $previewUrl = '';
    public $previewTitle = '';

    public $selectedDocType = '';
    public $selectedVersion = '';

    public function mount(Flight $flight)
    {
        $this->flight = $flight;
        $this->loadDocuments();
    }

    public function loadDocuments()
    {
        $this->loadsheets = $this->flight->loadsheets()
            ->with('releaser')
            ->orderBy('edition', 'DESC')
            ->get();

        $this->loadplans = $this->flight->loadplans()
            ->with('releaser')
            ->orderBy('version', 'DESC')
            ->get();
    }

    public function viewDocument()
    {
        if ($this->selectedDocType === 'loadsheet') {
            $document = $this->flight->loadsheets()->findOrFail($this->selectedVersion);
            $this->previewUrl = route('loadsheets.preview', $document);
            $this->previewTitle = "Loadsheet - Version {$document->version}";
        } else {
            $document = $this->flight->loadplans()->findOrFail($this->selectedVersion);
            $this->previewUrl = route('loadplans.preview', $document);
            $this->previewTitle = "Loading Instructions - Version {$document->version}";
        }

        $this->dispatch('show-document-preview');
    }

    public function downloadDocument()
    {
        if ($this->selectedDocType === 'loadsheet') {
            $document = $this->flight->loadsheets()->findOrFail($this->selectedVersion);
            return response()->download($document->file_path);
        } else {
            $document = $this->flight->loadplans()->findOrFail($this->selectedVersion);
            return response()->download($document->file_path);
        }
    }

    public function render()
    {
        return view('livewire.flights.documents');
    }
}