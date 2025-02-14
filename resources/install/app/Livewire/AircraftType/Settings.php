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
        'type' => 'string',
        'description' => '',
    ];

    // Default settings structure
    protected $defaultSettings = [
        'weight_and_balance' => [
            'ref_sta_at' => ['type' => 'float', 'description' => 'Reference station (meters)'],
            'k_constant' => ['type' => 'float', 'description' => 'K constant for index calculation'],
            'c_constant' => ['type' => 'float', 'description' => 'C constant for index calculation'],
            'length_of_mac' => ['type' => 'float', 'description' => 'Length of MAC (meters)'],
            'lemac_at' => ['type' => 'float', 'description' => 'Leading Edge MAC (meters)'],
            'fuel_density' => ['type' => 'float', 'description' => 'Fuel density (kg/L)'],
        ],
    ];

    public function mount(AircraftType $aircraftType)
    {
        $this->aircraftType = $aircraftType;
    }

    public function createSetting($key, $config)
    {
        $this->settingForm = [
            'key' => $key,
            'value' => '',
            'type' => $config['type'],
            'description' => $config['description'],
        ];
        $this->showSettingModal = true;
    }

    public function editSetting(Setting $setting)
    {
        $this->editingSetting = $setting;
        $this->settingForm = [
            'key' => $setting->key,
            'value' => $setting->value,
            'type' => $setting->type,
            'description' => $setting->description,
        ];
        $this->showSettingModal = true;
    }

    public function saveSetting()
    {
        $this->validate([
            'settingForm.key' => 'required|string',
            'settingForm.value' => 'required',
            'settingForm.type' => 'required|in:string,float,integer,boolean',
            'settingForm.description' => 'nullable|string',
        ]);

        $this->aircraftType->settings()->updateOrCreate(
            [
                'key' => $this->settingForm['key'],
                'airline_id' => $this->aircraftType->airline_id,
            ],
            [
                'value' => $this->settingForm['value'],
                'type' => $this->settingForm['type'],
                'description' => $this->settingForm['description'],
            ]
        );

        $this->dispatch('alert', icon: 'success', message: 'Setting saved successfully.');
        $this->dispatch('setting-saved');
        $this->reset('settingForm', 'editingSetting', 'showSettingModal');
    }

    public function deleteSetting(Setting $setting)
    {
        $setting->delete();
        $this->dispatch('alert', icon: 'success', message: 'Setting deleted successfully.');
    }

    public function render()
    {
        return view('livewire.aircraft_type.settings', [
            'settings' => $this->aircraftType->settings,
            'defaultSettings' => $this->defaultSettings,
        ]);
    }
}
