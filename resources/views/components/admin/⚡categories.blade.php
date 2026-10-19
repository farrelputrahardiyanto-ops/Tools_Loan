<?php

use Livewire\Component;
use App\Models\Category;
use Livewire\WithFileUploads;
use WireUi\Traits\WireUiActions;
use Illuminate\Support\Facades\Storage;


new class extends Component
{
    use withFileUploads;
    use WireUiActions;

    public $search = '';

  
    public $name;
    public $id;
    public $description;
    
    

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
            'name',
            'description'
        );
    }

    public function edit($id)
    {
        $user = Category::findOrFail($id);

        $this->id = $user->id;
        $this->name = $user->name;
        $this->description = $user->description;
      
        $this->dispatch('edit');
    }

    public function save()
    {
        $rules = [
            'name' => 'required',
            'description' => 'nullable'
        ];


        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'description' => $this->description
        ];

        if($this->id)
        {
            $category = Category::findOrFail($this->id);

           
            $category->update($data);
            $message = "Data Berhasdil DI Update";
        }else {
         

            Category::create($data);

            $message = "Data Berhasil DI Buat";
        }

        $this->dispatch('created');

        $this->resetForm();

        $this->notif($message);


    }

    public function delete($id)
    {
        $category = Category::findOrFail($id);

        $category->delete($id);

        $message = "Data Berhasil DI Hapus";

        $this->notif($message);
    }

    

    public function render()
    {
        return $this->view(
            [
                "categories" => Category::query()->
                where(function ($query){
                $query->where('id', 'like', '%'.$this->search.'%')
                ->orwhere('name', 'like', '%'.$this->search.'%')
                ->orwhere('description', 'like', '%'.$this->search.'%');
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
            <x-slot name="title"><h1>Users</h1></x-slot>

            <div class="overflow-x-auto w-full">
                <table class="w-full text-left">
                    <thead>
                        <tr>
                            <th class="p-3">ID</th>
                            <th class="p-3">Name</th>
                            <th class="p-3">Description</th>
                            <th class="p-3">Action</th>
                            
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($categories as $category)
                        <tr>
                            <td class="p-3">{{ $category->id }}</td>
                            <td class="p-3">{{ $category->name }}</td>
                            <td class="p-3">{{ $category->description }}</td>
                            <td class="p-3">
                                <div class="flex justify-center space-x-2">
                                    <x-button warning icon="pencil" wire:click="edit('{{$category->id}}')" />
                                    <x-button negative icon="trash" x-on:confirm="{
                                    icon: 'warning',
                                    title: 'Yakin?',
                                    description: 'Yakin ingin hapus data ini?',
                                    method: 'delete',
                                    params: '{{$category->id}}'
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
            <h1>Edit Category</h1>
        @else
            <h1>Add Category</h1>
        @endif</x-slot>

        <div class="flex flex-col space-y-2">
         
            <x-input label="Name" wire:model="name" />
            <x-textarea label="Descriptiob" wire:model="description" />
            <x-slot name="footer">
                <div class="flex justify-end space-x-2">
                    <x-button flat label="Close" x-on:click="close" />
                    <x-button :label="$this->id? 'Update':'Save'" wire:click="save" />
                </div>
            </x-slot>
        </div>
    </x-modal-card>
    {{-- No surplus words or unnecessary actions. - Marcus Aurelius --}}
</div>