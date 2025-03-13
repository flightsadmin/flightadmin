<?php

namespace App\Livewire\Airline;

use App\Models\Airline;
use App\Models\EmailNotification;
use App\Models\Route;
use App\Models\Station;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Livewire\WithPagination;

class EmailNotificationManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public Airline $airline;
    public $search = '';
    public $documentTypeFilter = '';
    public $stationFilter = '';
    public $showModal = false;
    public $editMode = false;
    public $notification_id = null;

    public $document_type = '';
    public $station_id = null;
    public $route_id = null;
    public $email_addresses = [];
    public $sita_addresses = [];
    public $notes = '';
    public $is_active = true;

    public $newEmail = '';
    public $newSita = '';

    public $documentTypes = [
        'loadsheet' => 'Load Sheet',
        'loadinginstruction' => 'Loading Instructions',
        'flightplan' => 'Flight Plan',
        'notoc' => 'NOTOC',
        'gendec' => 'General Declaration',
        'fueling' => 'Fueling Order',
        'delay' => 'Delay Report',
        'incident' => 'Incident Report',
        'weather' => 'Weather Report',
    ];

    protected $rules = [
        'document_type' => 'required|string',
        'station_id' => 'nullable|exists:stations,id',
        'route_id' => 'nullable|exists:routes,id',
        'email_addresses' => 'required_without:sita_addresses|array',
        'sita_addresses' => 'required_without:email_addresses|array',
        'notes' => 'nullable|string',
        'is_active' => 'boolean',
    ];

    protected $listeners = ['refreshNotifications' => '$refresh'];

    public function mount(Airline $airline)
    {
        $this->airline = $airline;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedDocumentTypeFilter()
    {
        $this->resetPage();
    }

    public function updatedStationFilter()
    {
        $this->resetPage();
    }

    public function updatedStationId()
    {
        // Reset route_id if station changes
        $this->reset(['route_id']);
    }

    public function createNotification()
    {
        $this->resetForm();
        $this->showModal = true;
        $this->editMode = false;
    }

    public function editNotification($id)
    {
        $notification = EmailNotification::findOrFail($id);
        $this->notification_id = $notification->id;
        $this->document_type = $notification->document_type;
        $this->station_id = $notification->station_id;
        $this->route_id = $notification->route_id;
        $this->email_addresses = $notification->email_addresses;
        $this->sita_addresses = $notification->sita_addresses ?? [];
        $this->is_active = $notification->is_active;

        $this->showModal = true;
        $this->editMode = true;
    }

    public function save()
    {
        // Validate that at least one recipient is provided
        if (empty($this->email_addresses) && empty($this->sita_addresses)) {
            $this->addError('email_addresses', 'At least one email or SITA address is required');
            return;
        }

        $this->validate([
            'document_type' => 'required|string',
            'station_id' => 'nullable|exists:stations,id',
            'route_id' => 'nullable|exists:routes,id',
            'email_addresses' => 'required_without:sita_addresses|array',
            'sita_addresses' => 'required_without:email_addresses|array',
            'is_active' => 'boolean',
        ]);

        $data = [
            'airline_id' => $this->airline->id,
            'document_type' => $this->document_type,
            'station_id' => $this->station_id,
            'route_id' => $this->route_id,
            'email_addresses' => $this->email_addresses,
            'sita_addresses' => $this->sita_addresses,
            'is_active' => $this->is_active,
        ];

        if ($this->notification_id) {
            EmailNotification::findOrFail($this->notification_id)->update($data);
            $message = 'Notification updated successfully';
        } else {
            EmailNotification::create($data);
            $message = 'Notification created successfully';
        }

        $this->dispatch('notify', ['message' => $message, 'type' => 'success']);
        $this->resetForm();
        $this->showModal = false;
    }

    public function deleteNotification($id)
    {
        EmailNotification::findOrFail($id)->delete();
        $this->dispatch('notify', ['message' => 'Notification deleted successfully', 'type' => 'success']);
    }

    public function toggleActive($id)
    {
        $notification = EmailNotification::findOrFail($id);
        $notification->update(['is_active' => !$notification->is_active]);

        $status = $notification->is_active ? 'activated' : 'deactivated';
        $this->dispatch('notify', ['message' => "Notification {$status} successfully", 'type' => 'success']);
    }

    public function addEmail()
    {
        if (empty($this->newEmail)) {
            return;
        }

        $validator = Validator::make(
            ['email' => $this->newEmail],
            ['email' => 'required|email']
        );

        if ($validator->fails()) {
            $this->addError('newEmail', 'Please enter a valid email address');
            return;
        }

        if (!in_array($this->newEmail, $this->email_addresses)) {
            $this->email_addresses[] = $this->newEmail;
        }

        $this->newEmail = '';
    }

    public function removeEmail($index)
    {
        if (isset($this->email_addresses[$index])) {
            unset($this->email_addresses[$index]);
            $this->email_addresses = array_values($this->email_addresses);
        }
    }

    public function addSita()
    {
        if (empty($this->newSita)) {
            return;
        }

        $validator = Validator::make(
            ['sita' => $this->newSita],
            ['sita' => 'required|regex:/^[A-Z0-9]{7}$/']
        );

        if ($validator->fails()) {
            $this->addError('newSita', 'Please enter a valid 7-character SITA address');
            return;
        }

        if (!in_array($this->newSita, $this->sita_addresses)) {
            $this->sita_addresses[] = strtoupper($this->newSita);
        }

        $this->newSita = '';
    }

    public function removeSita($index)
    {
        if (isset($this->sita_addresses[$index])) {
            unset($this->sita_addresses[$index]);
            $this->sita_addresses = array_values($this->sita_addresses);
        }
    }

    public function resetForm()
    {
        $this->notification_id = null;
        $this->document_type = '';
        $this->station_id = null;
        $this->route_id = null;
        $this->email_addresses = [];
        $this->sita_addresses = [];
        $this->newEmail = '';
        $this->newSita = '';
        $this->is_active = true;
        $this->resetErrorBag();
    }

    public function render()
    {
        $stations = Station::whereHas('airlines', function ($query) {
            $query->where('airlines.id', $this->airline->id);
        })->orderBy('code')->get();

        $routes = collect([]);
        if ($this->station_id) {
            $routes = Route::where('airline_id', $this->airline->id)
                ->where(function ($query) {
                    $query->where('departure_station_id', $this->station_id)
                        ->orWhere('arrival_station_id', $this->station_id);
                })
                ->with(['departureStation', 'arrivalStation'])
                ->orderBy('departure_station_id')
                ->orderBy('arrival_station_id')
                ->get();
        }

        $notifications = EmailNotification::where('airline_id', $this->airline->id)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->whereJsonContains('email_addresses', $this->search)
                        ->orWhereJsonContains('sita_addresses', $this->search)
                        ->orWhereHas('station', function ($sq) {
                            $sq->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('code', 'like', '%' . $this->search . '%');
                        })
                        ->orWhere('document_type', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->documentTypeFilter, function ($query) {
                $query->where('document_type', $this->documentTypeFilter);
            })
            ->when($this->stationFilter, function ($query) {
                $query->where('station_id', $this->stationFilter);
            })
            ->with(['station', 'route.departureStation', 'route.arrivalStation'])
            ->orderBy('document_type')
            ->orderBy('station_id')
            ->orderBy('route_id')
            ->paginate(10);

        return view('livewire.airline.email-notification-manager', [
            'notifications' => $notifications,
            'stations' => $stations,
            'routes' => $routes,
            'documentTypes' => $this->documentTypes,
        ]);
    }
}