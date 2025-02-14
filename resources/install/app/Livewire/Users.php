<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class Users extends Component
{
    use WithPagination, WithFileUploads;
    protected $paginationTheme = 'bootstrap';
    public $userId, $name, $email, $photo, $phone, $title, $password, $password_confirmation, $changePassword, $selectedRoles = [];

    public function render()
    {
        return view('livewire.admin.users.view', [
            'users' => User::latest()->paginate(),
            'roles' => Role::with('permissions')->get()
        ])->extends('components.layouts.admin');
    }

    public function submit()
    {
        $validatedData = $this->validate([
            'name' => 'required|min:6',
            'email' => 'required|email',
            'selectedRoles' => 'required',
            'password' => $this->userId ? 'nullable' : 'required|confirmed',
        ]);

        if (!empty($this->password)) {
            $validatedData['password'] = Hash::make($this->password);
        } else {
            unset($validatedData['password']);
        }

        $user = User::updateOrCreate(['id' => $this->userId], $validatedData);
        $user->syncRoles(collect($this->selectedRoles)->map(function ($role) {
            return (int) $role;
        }));

        if ($user->wasRecentlyCreated) {
            $emailData = [
                'name' => $this->name,
                'email' => $this->email,
                'roles' => $user->roles->pluck('name')->toArray(),
                'password' => $this->password
            ];

            Mail::send('mails.email', $emailData, function ($message) use ($emailData) {
                $message->to($emailData['email'], $emailData['name'])
                    ->subject('New Account for ' . $emailData['name']);
            });
        }
        $this->dispatch(
            'closeModal',
            icon: 'success',
            message: $this->userId ? 'User Updated Successfully.' : 'User Created Successfully.',
        );
        $this->reset();
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->userId = $id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->selectedRoles = $user->roles->pluck('id')->toArray();
    }

    public function details()
    {
        $this->edit(auth()->user()->id);
        return view('livewire.admin.users.details');
    }

    public function destroy($userId)
    {
        $user = User::findOrFail($userId);

        $user->delete();
        $this->dispatch(
            'closeModal',
            icon: 'warning',
            message: 'User Deleted Successfully.',
        );
    }
}