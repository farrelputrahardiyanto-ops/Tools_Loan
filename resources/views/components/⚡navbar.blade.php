<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div>
    <nav class="w-full dark:bg-gray-800 py-3 dark:text-gray-400" x-data="{ open: false }">
        <div class="flex justify-between items-center">
            <div class="flex justify-between items-center w-full px-3">
                <a href="">
                    <h1 class="font-semibold text-lg">Power Tools</h1>

                    
                </a>
                <button class="md:hidden" x-on:click="open != open" >
                        <x-icon name="bars-3" />
                    </button>
            </div>

            <div class="md:block" :class="{ 'hidden': !open}">
                <div class="flex flex-col md:flex-row md:space-x-3">
                    <a href="{{route('users')}}">Users</a>
                    <a href="{{route('tools')}}">Tools</a>
                    <a href="{{route('categories')}}">Categories</a>
                    <a href="">Loans</a>
                    <a href="">Loan Details</a>
                </div>
            </div>
        </div>
    </nav>
    {{-- I have not failed. I've just found 10,000 ways that won't work. - Thomas Edison --}}
</div>