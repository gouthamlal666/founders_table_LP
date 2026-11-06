<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Account')]
class Settings extends Component
{
    public string $name = '';
    public string $email = '';

    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    public function saveProfile(): void
    {
        $user = Auth::user();
        $data = $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:200', Rule::unique('users')->ignore($user->id)],
        ]);

        $user->update($data);
        $this->dispatch('toast', message: 'Profile saved');
    }

    public function savePassword(): void
    {
        $this->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(10)],
        ], [
            'current_password.current_password' => 'That isn’t your current password.',
        ]);

        Auth::user()->update(['password' => Hash::make($this->password)]);
        $this->reset('current_password', 'password', 'password_confirmation');
        $this->dispatch('toast', message: 'Password changed');
    }

    public function render()
    {
        return view('livewire.admin.settings');
    }
}
