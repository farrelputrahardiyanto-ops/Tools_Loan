<?php

use Livewire\Component;
use WireUi\Traits\WireUiActions;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    use WireUiActions;
    
    public function logout()
    {
        Auth::logout();

        return redirect()->route('login');
    }
};
?>

<div>
    <nav class="w-full dark:bg-gray-800 py-3 dark:text-gray-400" x-data="{ open: false }">
        <div class="flex justify-between items-center w-full">

            <div class="flex justify-between items-center w-full px-3">
                <a href="">
                    <h1 class="font-semibold text-lg">Power Tools</h1>

                    
                </a>
                <button class="md:hidden" x-on:click="open != open" >
                        <x-icon name="bars-3" />
                    </button>
            </div>

            <div class="md:block md:w-full " :class="{ 'hidden': !open}">
                <div class="flex flex-col md:flex-row md:justify-end md:mx-3 md:space-x-3 ">
                    <a href="{{route('users')}}">Users</a>
                    <a href="{{route('tools')}}">Tools</a>
                    <a href="{{route('categories')}}">Categories</a>
                    <a href="{{route('loans')}}">Loans</a>
                    <a href="">Loan Details</a>
                    <a href="{{route('max_loan')}}">Max Loan</a>
                    <x-button
                        negative
                        x-on:confirm="{
                            icon: 'warning',
                            title: 'Yakin?',
                            description: 'Yakin ingin Logout?',
                            method: 'logout'
                        }"
                        label="Logout"
                    />
                </div>
            </div>
        </div>
    </nav>
    {{-- I have not failed. I've just found 10,000 ways that won't work. - Thomas Edison --}}
</div>