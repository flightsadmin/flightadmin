<?php

namespace App\Livewire\Flight;

use App\Models\Flight;
use Livewire\Component;

class Settings extends Component
{
    public Flight $flight;

    public $settings;

    public $notocOptions = [
        'No' => false,
        'Yes' => true,
    ];

    public $weightVariations = [
        'Standard (88/70/35/0)',
        'Alternate (84/84/35/0)',
    ];

    public $trimOptions = [
        'Trim by Zone',
        'Trim by Seat Row',
    ];

    public $fuelDensityOptions = [
        '0.785',
        '0.793',
        '0.800',
    ];

    public function mount(Flight $flight)
    {
        $this->flight = $flight;
        $this->settings = $flight->getSettings();
    }

    public function updateNotoc($value)
    {
        $this->settings['notoc_required'] = $value;
        $this->saveSettings();
    }

    public function updateFlightVariation($value)
    {
        $this->settings['passenger_weights'] = $value;
        $this->saveSettings();
    }

    public function updateTrimType($value)
    {
        $this->settings['trim_settings']['type'] = $value;
        $this->saveSettings();
    }

    public function updateFuelDensity($value)
    {
        $this->settings['fuel_density'] = (float) $value;
        $this->saveSettings();
    }

    protected function saveSettings()
    {
        $this->flight->updateSettings($this->settings);
        $this->dispatch('alert', icon: 'success', message: 'Flight settings updated successfully.');
    }

    public function render()
    {
        return view('livewire.flights.settings');
    }
}
