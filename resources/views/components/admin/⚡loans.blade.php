<?php

use Livewire\Component;
use App\Models\Loan;


new class extends Component
{

    public $search = '';
    

    public function render()
    {
        return $this->view([
            "loans" => Loan::query()->
            where(function ($query){
                $query->where('id', 'like', '%'.$this->search.'%')
                ->orwhere('user_id', 'like', '%'.$this->search.'%')
                ->orwhere('status', 'like', '%'.$this->search.'%')
                ->orWhereHas('user', function ($q){
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
                            <th class="p-3">User</th>
                            <th class="p-3">Approved By</th>
                            <th class="p-3">Loan Date</th>
                            <th class="p-3">Due Date</th>
                            <th class="p-3">Return At</th>
                            <th class="p-3">Status</th>
                            <th class="p-3">Fine Amount</th>
                            <th class="p-3">Action</th>
                            
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($loans as $loan)
                        <tr>
                            <td class="p-3">{{ $loan->id }}</td>
                            <td class="p-3">{{ $loan->user?->name }}</td>
                            <td class="p-3">{{ $loan->approvedLoan?->name }}</td>
                            <td class="p-3">{{ $loan->loan_date }}</td>
                            <td class="p-3">{{ $loan->due_date }}</td>
                            <td class="p-3">{{ $loan->return_at}}</td>
                            <td class="p-3">{{ $loan->status}}</td>
                            <td class="p-3">{{ $loan->fine_amount}}</td>
                            <td class="p-3">
                                <div class="flex justify-center space-x-2">
                                    <x-button warning icon="pencil" wire:click="edit('{{$loan->id}}')" />
                                    <x-button negative icon="trash" x-on:confirm="{
                                    icon: 'warning',
                                    title: 'Yakin?',
                                    description: 'Yakin ingin hapus data ini?',
                                    method: 'delete',
                                    params: '{{$loan->id}}'
                                    }" />
                                </div>
                            </td>    
                            
                        </tr>
                            
                        @empty
                            <x-alert warning title="Null" />
                        @endforelse
                    
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>

    {{-- Walk as if you are kissing the Earth with your feet. - Thich Nhat Hanh --}}
</div>