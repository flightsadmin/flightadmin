<?php

namespace App\Livewire\Admin\Role;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Manager extends Component
{
    use WithPagination;

    public $paginationTheme = 'bootstrap';

    public $search = '';

    public $showModal = false;

    public $editingRole = null;

    public $permissions = [];

    public $selectedPermissions = [];

    public $form = [
        'name' => '',
    ];

    protected $rules = [
        'form.name' => 'required|string|max:255',
        'selectedPermissions' => 'array',
    ];

    public function mount()
    {
        $this->permissions = Permission::all()->map(function ($permission) {
            return [
                'id' => $permission->id,
                'name' => $permission->name,
            ];
        });
    }

    public function edit(Role $role)
    {
        $this->editingRole = $role;
        $this->form = [
            'name' => $role->name,
        ];
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $roleData = [
            'name' => $this->form['name'],
            'guard_name' => 'web', // Set default guard
        ];

        if ($this->editingRole) {
            $this->editingRole->update($roleData);
            $this->editingRole->syncPermissions($this->selectedPermissions);
            $message = 'Role updated successfully.';
        } else {
            $role = Role::create($roleData);
            $role->syncPermissions($this->selectedPermissions);
            $message = 'Role created successfully.';
        }

        $this->dispatch('alert', icon: 'success', message: $message);
        $this->dispatch('role-saved');
        $this->reset(['form', 'editingRole', 'showModal', 'selectedPermissions']);
    }

    public function delete(Role $role)
    {
        $role->delete();
        $this->dispatch('alert', icon: 'success', message: 'Role deleted successfully.');
    }

    public function render()
    {
        return view('livewire.admin.role.manager', [
            'roles' => Role::query()
                ->with('permissions')
                ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
                ->orderBy('name')
                ->paginate(10),
        ]);
    }
}
