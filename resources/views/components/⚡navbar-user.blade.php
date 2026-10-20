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
                    <a href="">Loans</a>
                </div>
            </div>
        </div>
    </nav>
    {{-- Walk as if you are kissing the Earth with your feet. - Thich Nhat Hanh --}}
</div>