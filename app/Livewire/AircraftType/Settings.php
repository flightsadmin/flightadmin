<?php

namespace App\Livewire\AircraftType;

use App\Models\AircraftType;
use App\Models\Setting;
use Livewire\Component;

class Settings extends Component
{
    public AircraftType $aircraftType;

    public $showSettingModal = false;

    public $editingSetting = null;

    // Setting form
    public $settingForm = [
        'key' => '',
        'value' => '',
        'type' => '',
        'description' => '',
    ];

    public function mount(AircraftType $aircraftType)
    {
        $this->aircraftType = $aircraftType;
    }

    public function editSetting($key)
    {
        $macSettings = $this->aircraftType->getMacSettings();
        $this->editingSetting = $key;
        $this->settingForm = [
            'key' => $key,
            'value' => $macSettings[$key],
        ];
    }

    public function saveSetting()
    {
        $this->validate([
            'settingForm.value' => 'required|numeric',
        ]);

        $macSettings = $this->aircraftType->getMacSettings();
        $macSettings[$this->settingForm['key']] = (float) $this->settingForm['value'];

        $this->aircraftType->settings()->updateOrCreate(
            [
                'key' => 'mac_settings',
                'airline_id' => $this->aircraftType->airline_id,
            ],
            [
                'value' => json_encode($macSettings),
                'type' => 'json',
                'description' => 'MAC Calculation Settings',
            ]
        );

        $this->editingSetting = null;
        $this->dispatch('alert', icon: 'success', message: 'Setting updated successfully.');
    }

    public function render()
    {
        return view('livewire.aircraft_type.settings', [
            'macSettings' => $this->aircraftType->getMacSettings(),
        ]);
    }
}
