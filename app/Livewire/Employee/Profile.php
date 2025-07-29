<?php

namespace App\Livewire\Employee;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class Profile extends Component
{
    public $user;
    
    // Form fields
    public $name = '';
    public $email = '';
    public $current_password = '';
    public $new_password = '';
    public $confirm_password = '';
    public $phone = '';
    public $address = '';
    public $timezone = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email',
        'phone' => 'nullable|string|max:255',
        'address' => 'nullable|string',
        'timezone' => 'required|string',
        'current_password' => 'nullable|required_with:new_password',
        'new_password' => 'nullable|min:6|confirmed',
        'confirm_password' => 'nullable|min:6',
    ];

    protected $messages = [
        'name.required' => 'Name is required.',
        'email.required' => 'Email is required.',
        'email.email' => 'Please enter a valid email address.',
        'current_password.required_with' => 'Current password is required to change password.',
        'new_password.min' => 'New password must be at least 6 characters.',
        'new_password.confirmed' => 'Password confirmation does not match.',
    ];

    public function mount()
    {
        $this->user = auth()->user();
        $this->loadUserData();
    }

    public function loadUserData()
    {
        $this->name = $this->user->name;
        $this->email = $this->user->email;
        $this->phone = $this->user->phone ?? '';
        $this->address = $this->user->address ?? '';
        $this->timezone = $this->user->timezone ?? 'America/New_York';
    }

    public function updateProfile()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($this->user->id)],
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'timezone' => 'required|string',
        ]);

        $this->user->update([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'timezone' => $this->timezone,
        ]);

        session()->flash('message', 'Profile updated successfully!');
    }

    public function updatePassword()
    {
        $this->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
            'confirm_password' => 'required|min:6',
        ]);

        // Check if current password is correct
        if (!Hash::check($this->current_password, $this->user->password)) {
            session()->flash('error', 'Current password is incorrect.');
            return;
        }

        // Update password
        $this->user->update([
            'password' => Hash::make($this->new_password)
        ]);

        // Clear password fields
        $this->current_password = '';
        $this->new_password = '';
        $this->confirm_password = '';

        session()->flash('message', 'Password updated successfully!');
    }

    public function render()
    {
        return view('livewire.employee.profile')
            ->layout('layouts.app');
    }
}
