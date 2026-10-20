<?php

use Livewire\Component;
use App\Models\MaxLoan;
use Livewire\WithFileUploads;
use WireUi\Traits\WireUiActions;
use Illuminate\Support\Facades\Storage;


new class extends Component
{
    use withFileUploads;
    use WireUiActions;

    public $search = '';

  
    public $max_loan;
    public $id;
    
    

    public function notif($message)
    {
        $this->notification()->send([
            'title' => 'Berhasil',
            'icon' => 'success',
            'message' => $message
        ]);
    }


    public function resetForm()
    {
        $this->reset(
            'id',
            'max_loan'
        );
    }

    public function edit($id)
    {
        $max_loan = MaxLoan::findOrFail($id);

        $this->id = $max_loan->id;
        $this->max_loan = $max_loan->max_loan;

        $this->dispatch('edit');
    }

    public function save()
    {
        $rules = [
            'max_loan' => 'required'
        ];


        $this->validate($rules);

        $data = [
            'max_loan' => $this->max_loan,
        ];

        if($this->id)
        {
            $max_loan = MaxLoan::findOrFail($this->id);

           
            $max_loan->update($data);
            $message = "Data Berhasdil DI Update";
        }else {
         

            MaxLoan::create($data);

            $message = "Data Berhasil DI Buat";
        }

        $this->dispatch('created');

        $this->resetForm();

        $this->notif($message);


    }

    public function delete($id)
    {
        $max_loan = MaxLoan::findOrFail($id);

        $max_loan->delete($id);

        $message = "Data Berhasil DI Hapus";

        $this->notif($message);
    }

    

    public function render()
    {
        return $this->view(
            [
                "max_loans" => MaxLoan::query()->
                where(function ($query){
                $query->where('id', 'like', '%'.$this->search.'%')
                ->orwhere('max_loan', 'like', '%'.$this->search.'%');
                })->latest()->paginate(5)
            ]
        )
        ->layout('layouts.app')
        ->title("Categories");
    }
};
?>

<div
x-on:edit.window="$openModal('category')"
x-on:created.window="$closeModal('category')">
    <div class="mx-auto max-w-6xl px-3 my-5">
        <div class="flex justify-between my-3">
            <x-button icon="plus" x-on:click="$openModal('category')" />
            <div class="max-w-2xl">
                <x-input icon="magnifying-glass" placeholder="Search..." wire:model.live="search"></x-input>
            </div>
        </div>

        <x-card>
            <x-slot name="title"><h1>Max Loan</h1></x-slot>

            <div class="overflow-x-auto w-full">
                <table class="w-full text-left">
                    <thead>
                        <tr>
                            <th class="p-3">ID</th>
                            <th class="p-3">Max Loan</th>
                            <th class="p-3">Action</th>
                            
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($max_loans as $max_loan)
                        <tr>
                            <td class="p-3">{{ $max_loan->id }}</td>
                            <td class="p-3">{{ $max_loan->max_loan }}</td>
                            <td class="p-3">
                                <div class="flex justify-center space-x-2">
                                    <x-button warning icon="pencil" wire:click="edit('{{$max_loan->id}}')" />
                                    <x-button negative icon="trash" x-on:confirm="{
                                    icon: 'warning',
                                    title: 'Yakin?',
                                    description: 'Yakin ingin hapus data ini?',
                                    method: 'delete',
                                    params: '{{$max_loan->id}}'
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

    <x-modal-card name="category">
        <x-slot name="title">@if ($this->id)
            <h1>Edit Max Loan</h1>
        @else
            <h1>Add Max Loan</h1>
        @endif</x-slot>

        <div class="flex flex-col space-y-2">
         
            <x-number label="Max Loan" wire:model="max_loan" />
            <x-slot name="footer">
                <div class="flex justify-end space-x-2">
                    <x-button flat label="Close" x-on:click="close" />
                    <x-button :label="$this->id? 'Update':'Save'" wire:click="save" />
                </div>
            </x-slot>
        </div>
    </x-modal-card>
 
    {{-- Very little is needed to make a happy life. - Marcus Aurelius --}}
</div>