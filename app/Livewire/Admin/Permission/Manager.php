<?php

namespace App\Livewire\Admin\Permission;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;

class Manager extends Component
{
    use WithPagination;

    public $paginationTheme = 'bootstrap';

    public $search = '';

    public $showModal = false;

    public $editingPermission = null;

    public $form = [
        'name' => '',
    ];

    protected $rules = [
        'form.name' => 'required|string|max:255',
    ];

    public function edit(Permission $permission)
    {
        $this->editingPermission = $permission;
        $this->form = [
            'name' => $permission->name,
        ];
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $permissionData = [
            'name' => $this->form['name'],
            'guard_name' => 'web', // Set default guard
        ];

        if ($this->editingPermission) {
            $this->editingPermission->update($permissionData);
            $message = 'Permission updated successfully.';
        } else {
            Permission::create($permissionData);
            $message = 'Permission created successfully.';
        }

        $this->dispatch('alert', icon: 'success', message: $message);
        $this->dispatch('permission-saved');
        $this->reset(['form', 'editingPermission', 'showModal']);
    }

    public function delete(Permission $permission)
    {
        $permission->delete();
        $this->dispatch('alert', icon: 'success', message: 'Permission deleted successfully.');
    }

    public function render()
    {
        return view('livewire.admin.permission.manager', [
            'permissions' => Permission::query()
                ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
                ->orderBy('name')
                ->paginate(10),
        ]);
    }
}
