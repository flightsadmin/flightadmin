<?php

namespace App\Livewire\Airline;

use App\Models\Airline;
use App\Models\Setting;
use Livewire\Component;

class SettingsManager extends Component
{
    public $airline;
    public $categories = ['general', 'operations', 'cargo', 'notifications'];
    public $selectedCategory = 'general';
    public $defaultSettings;
    public $airlineSettings;
    public $newSettingForm = [
        'key' => '',
        'value' => '',
        'type' => 'string',
        'description' => '',
        'category' => ''
    ];
    public $editingForm = [];
    public $showNewSettingModal = false;

    public function mount(Airline $airline)
    {
        $this->airline = $airline;
        $this->loadSettings();
        $this->selectedCategory = 'general';
    }

    public function loadSettings()
    {
        // Using a more direct approach to avoid JSON issues
        $templateRecord = Setting::where('key', 'airline_settings_template')->first();
        $airlineSettings = Setting::where('key', 'airline_settings')
            ->where('airline_id', $this->airline->id)
            ->first();

        // Set defaultSettings
        if ($templateRecord) {
            $decoded = json_decode($templateRecord->value, true);
            if (is_array($decoded)) {
                $this->defaultSettings = $decoded;
            } else {
                $this->defaultSettings = $this->getDefaultSettingsStructure();
                $this->logJsonError('Template settings JSON invalid');
            }
        } else {
            $this->defaultSettings = $this->getDefaultSettingsStructure();
        }

        // Set airlineSettings
        if ($airlineSettings) {
            $decoded = json_decode($airlineSettings->value, true);
            if (is_array($decoded)) {
                $this->airlineSettings = $decoded;
            } else {
                $this->airlineSettings = $this->defaultSettings;
                $this->logJsonError('Airline settings JSON invalid for airline ' . $this->airline->id);

                // Fix the invalid data in the database
                Setting::updateOrCreate(
                    [
                        'key' => 'airline_settings',
                        'airline_id' => $this->airline->id
                    ],
                    [
                        'value' => json_encode($this->defaultSettings),
                        'type' => 'json',
                        'description' => 'Fixed settings for ' . $this->airline->name
                    ]
                );
            }
        } else {
            // Create new settings for this airline
            $this->airlineSettings = $this->defaultSettings;

            Setting::create([
                'key' => 'airline_settings',
                'value' => json_encode($this->defaultSettings),
                'type' => 'json',
                'description' => 'Settings for ' . $this->airline->name,
                'airline_id' => $this->airline->id
            ]);
        }
    }

    private function logJsonError($message)
    {
        logger($message);
        logger('JSON error: ' . json_last_error_msg());
    }

    private function getDefaultSettingsStructure()
    {
        return [
            'general' => [
                'standard_passenger_weight' => [
                    'type' => 'integer',
                    'description' => 'Standard passenger weight (kg)',
                    'default' => 84
                ],
                'standard_male_passenger_weight' => [
                    'type' => 'integer',
                    'description' => 'Standard male passenger weight (kg)',
                    'default' => 88
                ],
                'standard_female_passenger_weight' => [
                    'type' => 'integer',
                    'description' => 'Standard female passenger weight (kg)',
                    'default' => 70
                ],
                'standard_child_passenger_weight' => [
                    'type' => 'integer',
                    'description' => 'Standard child passenger weight (kg)',
                    'default' => 35
                ],
                'standard_infant_passenger_weight' => [
                    'type' => 'integer',
                    'description' => 'Standard infant passenger weight (kg)',
                    'default' => 10
                ],
                'standard_cockpit_crew_weight' => [
                    'type' => 'integer',
                    'description' => 'Standard cockpit crew weight (kg)',
                    'default' => 85
                ],
                'standard_cabin_crew_weight' => [
                    'type' => 'integer',
                    'description' => 'Standard cabin crew weight (kg)',
                    'default' => 70
                ],
                'standard_baggage_weight' => [
                    'type' => 'integer',
                    'description' => 'Standard baggage weight (kg)',
                    'default' => 23
                ],
                'standard_fuel_density' => [
                    'type' => 'float',
                    'description' => 'Standard fuel density (kg/L)',
                    'default' => 0.8
                ],
            ],
            'operations' => [
                'checkin_open_time' => [
                    'type' => 'integer',
                    'description' => 'Check-in opens before departure (minutes)',
                    'default' => 120
                ],
                'checkin_close_time' => [
                    'type' => 'integer',
                    'description' => 'Check-in closes before departure (minutes)',
                    'default' => 45
                ],
                'boarding_open_time' => [
                    'type' => 'integer',
                    'description' => 'Boarding opens before departure (minutes)',
                    'default' => 45
                ],
                'boarding_close_time' => [
                    'type' => 'integer',
                    'description' => 'Boarding closes before departure (minutes)',
                    'default' => 15
                ],
            ],
            'cargo' => [
                'dangerous_goods_allowed' => [
                    'type' => 'boolean',
                    'description' => 'Allow dangerous goods',
                    'default' => false
                ],
                'live_animals_allowed' => [
                    'type' => 'boolean',
                    'description' => 'Allow live animals',
                    'default' => false
                ],
                'max_cargo_piece_weight' => [
                    'type' => 'integer',
                    'description' => 'Maximum cargo piece weight (kg)',
                    'default' => 150
                ],
                'max_baggage_piece_weight' => [
                    'type' => 'integer',
                    'description' => 'Maximum baggage piece weight (kg)',
                    'default' => 32
                ],
            ],
            'notifications' => [
                'enable_email_notifications' => [
                    'type' => 'boolean',
                    'description' => 'Enable email notifications',
                    'default' => true
                ],
                'enable_sms_notifications' => [
                    'type' => 'boolean',
                    'description' => 'Enable SMS notifications',
                    'default' => false
                ],
                'notification_email' => [
                    'type' => 'string',
                    'description' => 'Notification email address',
                    'default' => ''
                ],
                'notification_phone' => [
                    'type' => 'string',
                    'description' => 'Notification phone number',
                    'default' => ''
                ],
            ],
        ];
    }

    public function selectCategory($category)
    {
        $this->selectedCategory = $category;
    }

    public function editSetting($key)
    {
        $category = $this->selectedCategory;

        // Ensure airlineSettings and defaultSettings are arrays
        if (!is_array($this->airlineSettings)) {
            $this->airlineSettings = [];
        }

        if (!is_array($this->defaultSettings)) {
            $this->defaultSettings = $this->getDefaultSettingsStructure();
        }

        // Check if category exists
        if (!isset($this->airlineSettings[$category])) {
            $this->airlineSettings[$category] = [];
        }

        if (isset($this->airlineSettings[$category][$key]) && is_array($this->airlineSettings[$category][$key])) {
            $setting = $this->airlineSettings[$category][$key];
            $this->editingForm = [
                'category' => $category,
                'key' => $key,
                'type' => $setting['type'] ?? 'string',
                'description' => $setting['description'] ?? '',
                'value' => $setting['default'] ?? '',
            ];
        } elseif (isset($this->defaultSettings[$category][$key]) && is_array($this->defaultSettings[$category][$key])) {
            $setting = $this->defaultSettings[$category][$key];
            $this->editingForm = [
                'category' => $category,
                'key' => $key,
                'type' => $setting['type'] ?? 'string',
                'description' => $setting['description'] ?? '',
                'value' => $setting['default'] ?? '',
            ];
        } else {
            // Handle case where setting doesn't exist
            $this->editingForm = [
                'category' => $category,
                'key' => $key,
                'type' => 'string',
                'description' => '',
                'value' => '',
            ];
        }

        $this->dispatch('open-edit-setting-modal');
    }

    public function saveSetting()
    {
        $category = $this->editingForm['category'];
        $key = $this->editingForm['key'];

        // Update the airline settings
        if (!isset($this->airlineSettings[$category])) {
            $this->airlineSettings[$category] = [];
        }

        $this->airlineSettings[$category][$key] = [
            'type' => $this->editingForm['type'],
            'description' => $this->editingForm['description'],
            'default' => $this->editingForm['value']
        ];

        // Save to database
        Setting::updateOrCreate(
            [
                'key' => 'airline_settings',
                'airline_id' => $this->airline->id
            ],
            [
                'value' => json_encode($this->airlineSettings),
                'type' => 'json',
                'description' => 'Custom settings for ' . $this->airline->name
            ]
        );

        $this->dispatch('close-edit-setting-modal');
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Setting saved successfully'
        ]);
    }

    public function showNewSettingForm()
    {
        $this->newSettingForm = [
            'key' => '',
            'value' => '',
            'type' => 'string',
            'description' => '',
            'category' => $this->selectedCategory
        ];
        $this->showNewSettingModal = true;
        $this->dispatch('open-new-setting-modal');
    }

    public function addNewSetting()
    {
        $this->validate([
            'newSettingForm.key' => 'required|string',
            'newSettingForm.value' => 'required',
            'newSettingForm.type' => 'required|string',
            'newSettingForm.description' => 'required|string',
        ]);

        $category = $this->newSettingForm['category'];
        $key = $this->newSettingForm['key'];

        // Add to airline settings
        if (!isset($this->airlineSettings[$category])) {
            $this->airlineSettings[$category] = [];
        }

        $this->airlineSettings[$category][$key] = [
            'type' => $this->newSettingForm['type'],
            'description' => $this->newSettingForm['description'],
            'default' => $this->newSettingForm['value']
        ];

        // Save to database
        Setting::updateOrCreate(
            [
                'key' => 'airline_settings',
                'airline_id' => $this->airline->id
            ],
            [
                'value' => json_encode($this->airlineSettings),
                'type' => 'json',
                'description' => 'Custom settings for ' . $this->airline->name
            ]
        );

        $this->showNewSettingModal = false;
        $this->dispatch('close-new-setting-modal');
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'New setting added successfully'
        ]);
    }

    public function removeSetting($key)
    {
        $category = $this->selectedCategory;

        if (isset($this->airlineSettings[$category][$key])) {
            unset($this->airlineSettings[$category][$key]);

            // Save to database
            Setting::updateOrCreate(
                [
                    'key' => 'airline_settings',
                    'airline_id' => $this->airline->id
                ],
                [
                    'value' => json_encode($this->airlineSettings),
                    'type' => 'json',
                    'description' => 'Custom settings for ' . $this->airline->name
                ]
            );

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Setting removed successfully'
            ]);
        }
    }

    public function repairSettings()
    {
        // Use the default structure to repair
        $defaultStructure = $this->getDefaultSettingsStructure();

        // Update the settings in the database
        Setting::updateOrCreate(
            [
                'key' => 'airline_settings',
                'airline_id' => $this->airline->id
            ],
            [
                'value' => json_encode($defaultStructure),
                'type' => 'json',
                'description' => 'Repaired settings for ' . $this->airline->name
            ]
        );

        // Reload the settings
        $this->loadSettings();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Settings have been repaired'
        ]);
    }

    public function render()
    {
        return view('livewire.airline.settings-manager');
    }
}