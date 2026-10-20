<?php

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component
{

    public $email;
    public $password;

    public $rememberme = false;

    public function login()
    {
        $rememberme = $this->rememberme;

        $rules = [
            'email' => 'required|email',
            'password' => 'required|min:8'
        ];

        $this->validate($rules);

        $credentials = [
            'email' => $this->email,
            'password' => $this->password
        ];

        if(Auth::attempt($credentials, $rememberme)){
            session()->regenerate();

            return redirect()->route('loan');
        }


    }
    

    public function render()
    {
        return $this->view()
        ->layout('layouts.login')
        ->title('Login');
    }
};
?>

<div>

    <div class="max-w-3xl h-100vh p-4 mx-auto flex justify-center item-center">
        <x-card class="p-3 w-full">
            <h1 class="text-3xl font-semibold text-center py-6">Sign In Power Tools</h1>
            <x-input  label="Email" wire:model="email"  />
            <x-password label="Password" wire:model="password" />
            <div class="flex justify-end w-full my-3">
                <x-checkbox left-label="Remember Me" wire:model="rememberme" />
            </div>
            <div class="w-full my-5">
                <x-button  class="w-full" label="Sign In" wire:click="login" />
            </div>
        </x-card>
    </div>
    {{-- Happiness is not something readymade. It comes from your own actions. - Dalai Lama --}}
</div>