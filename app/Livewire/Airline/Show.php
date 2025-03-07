<?php

namespace App\Livewire\Airline;

use App\Models\Airline;
use Livewire\Attributes\Url;
use Livewire\Component;

class Show extends Component
{
    public Airline $airline;

    #[Url]
    public $activeTab = 'overview';

    #[Url]
    public $settingCategory = 'general';

    public $editingSetting = null;

    public $showSettingModal = false;

    // Form fields
    public $form = [
        'category' => '',
        'key' => '',
        'value' => '',
        'type' => '',
        'description' => '',
    ];

    // Grouped settings
    protected $defaultSettings = [
        'general' => [
            'standard_passenger_weight' => ['type' => 'integer', 'description' => 'Standard passenger weight (kg)'],
            'standard_male_passenger_weight' => ['type' => 'integer', 'description' => 'Standard male passenger weight (kg)'],
            'standard_female_passenger_weight' => ['type' => 'integer', 'description' => 'Standard female passenger weight (kg)'],
            'standard_child_passenger_weight' => ['type' => 'integer', 'description' => 'Standard child passenger weight (kg)'],
            'standard_infant_passenger_weight' => ['type' => 'integer', 'description' => 'Standard infant passenger weight (kg)'],
            'standard_cockpit_crew_weight' => ['type' => 'integer', 'description' => 'Standard cockpit crew weight (kg)'],
            'standard_cabin_crew_weight' => ['type' => 'integer', 'description' => 'Standard cabin crew weight (kg)'],
            'standard_baggage_weight' => ['type' => 'integer', 'description' => 'Standard baggage weight (kg)'],
            'standard_fuel_density' => ['type' => 'float', 'description' => 'Standard fuel density (kg/L)'],
        ],
        'operations' => [
            'checkin_open_time' => ['type' => 'integer', 'description' => 'Check-in opens before departure (minutes)'],
            'checkin_close_time' => ['type' => 'integer', 'description' => 'Check-in closes before departure (minutes)'],
            'boarding_open_time' => ['type' => 'integer', 'description' => 'Boarding opens before departure (minutes)'],
            'boarding_close_time' => ['type' => 'integer', 'description' => 'Boarding closes before departure (minutes)'],
        ],
        'cargo' => [
            'dangerous_goods_allowed' => ['type' => 'boolean', 'description' => 'Allow dangerous goods'],
            'live_animals_allowed' => ['type' => 'boolean', 'description' => 'Allow live animals'],
            'max_cargo_piece_weight' => ['type' => 'integer', 'description' => 'Maximum cargo piece weight (kg)'],
            'max_baggage_piece_weight' => ['type' => 'integer', 'description' => 'Maximum baggage piece weight (kg)'],
        ],
        'notifications' => [
            'enable_email_notifications' => ['type' => 'boolean', 'description' => 'Enable email notifications'],
            'enable_sms_notifications' => ['type' => 'boolean', 'description' => 'Enable SMS notifications'],
            'notification_email' => ['type' => 'string', 'description' => 'Notification email address'],
            'notification_phone' => ['type' => 'string', 'description' => 'Notification phone number'],
        ],
    ];

    public function mount(Airline $airline)
    {
        $this->airline = $airline->load([
            'settings',
            'aircraft.type',
            'flights' => fn ($q) => $q->latest('scheduled_departure_time')->take(5),
        ]);
    }

    public function setSettingCategory($category)
    {
        $this->settingCategory = $category;
    }

    public function getCurrentCategorySettings()
    {
        return $this->defaultSettings[$this->settingCategory] ?? [];
    }

    public function editSetting($category, $key, $config)
    {
        $settings = $this->airline->getSettings($category);
        $this->form = [
            'category' => $category,
            'key' => $key,
            'value' => $settings[$key] ?? ($config['type'] === 'json' ? $config['default'] : ''),
            'type' => $config['type'],
            'description' => $config['description'],
        ];

        $this->editingSetting = true;
        $this->showSettingModal = true;
    }

    public function saveSetting()
    {
        $this->validate([
            'form.value' => 'required',
            'form.type' => 'required|in:string,float,integer,boolean,json',
        ]);

        $value = match ($this->form['type']) {
            'float' => (float) $this->form['value'],
            'integer' => (int) $this->form['value'],
            'boolean' => (bool) $this->form['value'],
            'json' => is_array($this->form['value']) ? $this->form['value'] : json_decode($this->form['value'], true),
            default => $this->form['value']
        };

        $this->airline->updateSettings(
            $this->form['category'],
            $this->form['key'],
            $value
        );

        $this->resetForm();
        $this->dispatch('setting-saved');
        $this->dispatch('alert', icon: 'success', message: 'Setting saved successfully.');
    }

    public function resetForm()
    {
        $this->form = [
            'category' => '',
            'key' => '',
            'value' => '',
            'type' => '',
            'description' => '',
        ];
        $this->editingSetting = false;
        $this->showSettingModal = false;
    }

    public function closeModal()
    {
        $this->resetForm();
    }

    public function deleteSetting($key)
    {
        $this->airline->settings()->where('key', $key)->delete();
        $this->dispatch('alert', icon: 'success', message: 'Setting deleted successfully.');
    }

    public function toggleStatus()
    {
        $this->airline->active = ! $this->airline->active;
        $this->airline->save();
        $this->dispatch('alert', icon: 'success', message: 'Airline status updated successfully.');
    }

    public function render()
    {
        return view('livewire.airline.show', [
            'settings' => $this->airline->getSettings(),
            'defaultSettings' => $this->defaultSettings,
            'currentSettings' => $this->getCurrentCategorySettings(),
        ]);
    }
}
