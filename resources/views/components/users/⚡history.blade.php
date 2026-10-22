<?php

use Livewire\Component;
use App\Models\Loan;

new class extends Component
{
    public $search = '';
    

    public function render()
    {
        return $this->view([
            "loans" => Loan::query()->where('user_id', auth()->user()->id)->
            where(function ($query){
                $query->where('id', 'like', '%'.$this->search.'%')
                ->orwhere('created_at', 'like', '%'.$this->search.'%')
                ->orwhere('returned_at', 'like', '%'.$this->search.'%')
                ->orwhere('loan_date', 'like', '%'.$this->search.'%')
                ->orwhere('due_date', 'like', '%'.$this->search.'%')
                ->orwhere('status', 'like', '%'.$this->search.'%');
            })->latest()->paginate(5)
        ])->layout('layouts.user')
        ->title("History");
    }
};
?>

<div>

    <div class="max-w-6xl p-3 mx-auto">
        <div class="flex justify-between my-5">
            <h1 class="text-4xl font-semibold dark:text-gray-400">History</h1>
            <x-input icon="magnifying-glass" wire:model.live="search" placeholder="Search..."  class="max-w-2xl"/>
        </div>
        @forelse ($loans as $user_loan)
         <div class="col-span-full rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800 my-2">

            <div class="flex flex-col gap-4">

                {{-- Loan Header --}}
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <h1 class="text-xl font-bold text-gray-800 dark:text-gray-100">
                            Loan Status
                        </h1>

                        <span class="rounded-full px-3 py-1 text-xs font-semibold
                            {{ $user_loan->status == 'borrowed'
                                ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300'
                                : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
                            {{ ucfirst($user_loan->status) }}
                        </span>
                    </div>

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Created At: {{ $user_loan->created_at }}
                    </p>
                </div>

                {{-- Loan Dates --}}
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-700/50">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Loan Date</p>
                        <p class="mt-1 font-medium text-gray-800 dark:text-gray-200">
                            {{ $user_loan->loan_date }}
                        </p>
                    </div>

                    <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-700/50">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Due Date</p>
                        <p class="mt-1 font-medium text-gray-800 dark:text-gray-200">
                            {{ $user_loan->due_date }}
                        </p>
                    </div> 

                     <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-700/50">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Returned Date</p>
                        <p class="mt-1 font-medium text-gray-800 dark:text-gray-200">
                            {{ $user_loan->returned_at }}
                        </p>
                    </div> 

                     <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-700/50">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Fine Amount</p>
                        <p class="mt-1 font-medium text-gray-800 dark:text-gray-200">
                            Rp.{{ number_format($user_loan->fine_amount) }}
                        </p>
                    </div> 


                </div>

                

                {{-- Loan Details --}}
                <div>
                    <p class="mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">
                        Borrowed Tools
                    </p>

                    <div class="flex flex-wrap gap-2">
                        @foreach ($user_loan->loan_details as $detail)
                            <span class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                {{ $detail->tool->name }}
                                <span class="font-semibold">x {{ $detail->qty }}</span>
                            </span>
                        @endforeach
                    </div>
                </div>

                {{-- Return Button --}}
                @if ($user_loan->status == 'borrowed')
                    <div class="flex justify-end border-t border-gray-200 pt-3 dark:border-gray-700">
                        <x-button
                            label="Return Tools"
                            icon="arrow-uturn-left"
                            class="w-full sm:w-auto"
                            x-on:confirm="{
                                title: 'Yakin Mengembalikan data ini?',
                                method: 'return',
                                params: {{ $user_loan->id }}
                            }"
                        />
                    </div>
                @endif

            </div>
        </div>

    @empty
        
    @endforelse
    </div>
    {{-- Do what you can, with what you have, where you are. - Theodore Roosevelt --}}
</div>