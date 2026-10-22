<?php

use Livewire\Component;
use App\Models\LoanDetail;

new class extends Component
{
    public $search = '';
    

    public function render()
    {
        return $this->view([
            "loan_details" => LoanDetail::query()->
            where(function ($query){
                $query->where('id', 'like', '%'.$this->search.'%')
                ->orwhere('loan_id', 'like', '%'.$this->search.'%')
                ->orwhere('qty', 'like', '%'.$this->search.'%')
                ->orWhereHas('tool', function ($q){
                    $q->where('name', 'like', '%'.$this->search.'%');
                });
            })->latest()->paginate(5)
        ])->layout('layouts.app')
        ->title("Loans");
    }
};
?>

<div>
    <div class="mx-auto max-w-6xl px-3 my-5">
        <div class="flex justify-between my-3">
            <x-button icon="plus" x-on:click="$openModal('tool')" />
            <div class="max-w-2xl">
                <x-input icon="magnifying-glass" placeholder="Search..." wire:model.live="search"></x-input>
            </div>
        </div>

        <x-card>
            <x-slot name="title"><h1>Loans</h1></x-slot>

            <div class="overflow-x-auto w-full">
                <table class="w-full text-left">
                    <thead>
                        <tr>
                            <th class="p-3">ID</th>
                            <th class="p-3">Loan ID</th>
                            <th class="p-3">Tool</th>
                            <th class="p-3">QTY</th>
                            <th class="p-3">Created At</th>
                         
                            
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($loan_details as $loan)
                        <tr>
                            <td class="p-3">{{ $loan->id }}</td>
                            <td class="p-3">{{ $loan->loan_id }}</td>
                            <td class="p-3">{{ $loan->tool?->name }}</td>
                            <td class="p-3">{{ $loan->qty }}</td>
                            <td class="p-3">{{ $loan->created_at }}</td>
                          
                            
                        </tr>
                            
                        @empty
                            <x-alert warning title="Null" />
                        @endforelse
                    
                    </tbody>
                </table>

                <div class="my-3 flex justify-end">
                    {{ $loan_details->links()   }}
                </div>
            </div>
        </x-card>
    </div>

    {{-- Because you are alive, everything is possible. - Thich Nhat Hanh --}}
</div>