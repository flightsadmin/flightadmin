<?php

namespace App\Livewire\Airline;

use App\Models\Airline;
use App\Models\EmailNotification;
use App\Models\Route;
use App\Models\Station;
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

    // Form fields
    public $notificationId;
    public $station_id;
    public $route_id;
    public $document_type;
    public $email_addresses = [];
    public $cc_addresses = [];
    public $bcc_addresses = [];
    public $is_active = true;
    public $notes;

    // Temporary fields for email input
    public $newEmail = '';
    public $newCc = '';
    public $newBcc = '';

    // Available document types
    public $documentTypes = [
        'loadsheet' => 'Load Sheet',
        'lirf' => 'Loading Instruction Report',
        'notoc' => 'Notification to Captain',
        'manifest' => 'Passenger Manifest',
        'general' => 'General Notifications',
    ];

    protected $rules = [
        'station_id' => 'nullable|exists:stations,id',
        'route_id' => 'nullable|exists:routes,id',
        'document_type' => 'required|string',
        'email_addresses' => 'required|array|min:1',
        'email_addresses.*' => 'required|email',
        'cc_addresses' => 'nullable|array',
        'cc_addresses.*' => 'nullable|email',
        'bcc_addresses' => 'nullable|array',
        'bcc_addresses.*' => 'nullable|email',
        'is_active' => 'boolean',
        'notes' => 'nullable|string',
    ];

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
        $this->editMode = false;
        $this->showModal = true;
    }

    public function editNotification($id)
    {
        $this->resetForm();
        $this->notificationId = $id;
        $this->editMode = true;

        $notification = EmailNotification::findOrFail($id);
        $this->station_id = $notification->station_id;
        $this->route_id = $notification->route_id;
        $this->document_type = $notification->document_type;
        $this->email_addresses = $notification->email_addresses;
        $this->cc_addresses = $notification->cc_addresses ?: [];
        $this->bcc_addresses = $notification->bcc_addresses ?: [];
        $this->is_active = $notification->is_active;
        $this->notes = $notification->notes;

        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        // Check for duplicate configuration
        $query = EmailNotification::where('airline_id', $this->airline->id)
            ->where('document_type', $this->document_type);

        if ($this->station_id) {
            $query->where('station_id', $this->station_id);
        } else {
            $query->whereNull('station_id');
        }

        if ($this->route_id) {
            $query->where('route_id', $this->route_id);
        } else {
            $query->whereNull('route_id');
        }

        if ($this->editMode) {
            $query->where('id', '!=', $this->notificationId);
        }

        $exists = $query->exists();

        if ($exists) {
            $this->addError('document_type', 'A notification configuration already exists for this combination.');
            return;
        }

        $notificationData = [
            'airline_id' => $this->airline->id,
            'station_id' => $this->station_id,
            'route_id' => $this->route_id,
            'document_type' => $this->document_type,
            'email_addresses' => $this->email_addresses,
            'cc_addresses' => !empty($this->cc_addresses) ? $this->cc_addresses : null,
            'bcc_addresses' => !empty($this->bcc_addresses) ? $this->bcc_addresses : null,
            'is_active' => $this->is_active,
            'notes' => $this->notes,
        ];

        if ($this->editMode) {
            EmailNotification::findOrFail($this->notificationId)->update($notificationData);
            $message = 'Email notification updated successfully';
        } else {
            EmailNotification::create($notificationData);
            $message = 'Email notification created successfully';
        }

        $this->dispatch('alert', icon: 'success', message: $message);
        $this->showModal = false;
        $this->resetForm();
    }

    public function toggleActive($id)
    {
        $notification = EmailNotification::findOrFail($id);
        $notification->update(['is_active' => !$notification->is_active]);

        $status = $notification->is_active ? 'activated' : 'deactivated';
        $this->dispatch('alert', icon: 'success', message: "Email notification {$status} successfully");
    }

    public function deleteNotification($id)
    {
        EmailNotification::findOrFail($id)->delete();
        $this->dispatch('alert', icon: 'success', message: 'Email notification deleted successfully');
    }

    public function addEmail()
    {
        if (!empty($this->newEmail)) {
            $this->validate(['newEmail' => 'required|email']);
            $this->email_addresses[] = $this->newEmail;
            $this->newEmail = '';
        }
    }

    public function removeEmail($index)
    {
        unset($this->email_addresses[$index]);
        $this->email_addresses = array_values($this->email_addresses);
    }

    public function addCc()
    {
        if (!empty($this->newCc)) {
            $this->validate(['newCc' => 'required|email']);
            $this->cc_addresses[] = $this->newCc;
            $this->newCc = '';
        }
    }

    public function removeCc($index)
    {
        unset($this->cc_addresses[$index]);
        $this->cc_addresses = array_values($this->cc_addresses);
    }

    public function addBcc()
    {
        if (!empty($this->newBcc)) {
            $this->validate(['newBcc' => 'required|email']);
            $this->bcc_addresses[] = $this->newBcc;
            $this->newBcc = '';
        }
    }

    public function removeBcc($index)
    {
        unset($this->bcc_addresses[$index]);
        $this->bcc_addresses = array_values($this->bcc_addresses);
    }

    public function resetForm()
    {
        $this->reset([
            'notificationId',
            'station_id',
            'route_id',
            'document_type',
            'email_addresses',
            'cc_addresses',
            'bcc_addresses',
            'is_active',
            'notes',
            'newEmail',
            'newCc',
            'newBcc',
            'editMode'
        ]);
        $this->resetValidation();
    }

    public function render()
    {
        // Get all notifications for this airline
        $notifications = $this->airline->emailNotifications()
            ->with(['station', 'route.departureStation', 'route.arrivalStation'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('station', function ($sq) {
                        $sq->where('code', 'like', '%' . $this->search . '%')
                            ->orWhere('name', 'like', '%' . $this->search . '%');
                    })
                        ->orWhere('document_type', 'like', '%' . $this->search . '%')
                        ->orWhere('notes', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->documentTypeFilter, function ($query) {
                $query->where('document_type', $this->documentTypeFilter);
            })
            ->when($this->stationFilter, function ($query) {
                $query->where('station_id', $this->stationFilter);
            })
            ->orderBy('document_type')
            ->orderBy('station_id')
            ->paginate(10);

        // Get all stations assigned to this airline
        $stations = $this->airline->stations()->orderBy('code')->get();

        // Get routes for the selected station
        $routes = collect();
        if ($this->station_id) {
            $routes = Route::where('airline_id', $this->airline->id)
                ->where(function ($query) {
                    $query->where('departure_station_id', $this->station_id)
                        ->orWhere('arrival_station_id', $this->station_id);
                })
                ->where('is_active', true)
                ->with(['departureStation', 'arrivalStation'])
                ->get();
        }

        return view('livewire.airline.email-notification-manager', [
            'notifications' => $notifications,
            'stations' => $stations,
            'routes' => $routes,
        ]);
    }
}