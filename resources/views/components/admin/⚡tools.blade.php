
<?php

use Livewire\Component;
use App\Models\Tool;
use App\Models\Category;
use Livewire\WithFileUploads;
use WireUi\Traits\WireUiActions;
use Illuminate\Support\Facades\Storage;


new class extends Component
{
    use withFileUploads;
    use WireUiActions;

    public $search = '';

    public $brandOptions = [
        'tekiro' => 'Tekiro',
        'makita' => 'Makita',
        'dewalt' => 'DeWalt'
    ];

    public $categories = [];

    public $name;
    public $id;
    public $category;
    public $brand;
    public $photo;
    public $description;
    public $stock;
    public $max_loan;


    public function mount()
    {
        $this->categories = Category::latest()->get(['id', 'name'])->toArray();
    }
    

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
            'category',
            'brand',
            'photo',
            'stock',
            'description',
            'max_loan'
        );
    }

    public function edit($id)
    {
        $tool = Tool::findOrFail($id);

        $this->id = $tool->id;
        $this->name = $tool->name;
        $this->category = $tool->category_id;
        $this->brand = $tool->brand;
        $this->stock = $tool->stock;
        $this->description = $tool->description;
        $this->max_loan = $tool->max_loan;

        $this->dispatch('edit');
    }

    public function save()
    {
        $rules = [
            'photo' => 'nullable|mimes:png,jpg, svg|max:2048',
            'name' => 'required',
            'category' => 'required',
            'brand' => 'required',
            'stock' => 'required',
            'description' => 'required',
            'max_loan' => 'required'
        ];

      
        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'category_id' => $this->category,
            'brand' => $this->brand,
            'stock' => $this->stock,
            'description' => $this->description,
            'max_loan' => $this->max_loan
        ];

        if($this->id)
        {
            $tool = Tool::findOrFail($this->id);

        

            if($this->photo){
                if($tool->photo)
                {
                    Storage::disk('public')->delete('tools/'. $tool->profile);
                }
                $data['photo'] = $this->photo->hashName();
                $this->photo->storeAs('tools', $data['photo'], 'public' );
           
            }
             
            $tool->update($data);
            
            $message = "Data Berhasdil DI Update";
        }else {

            if($this->photo)
            {
                $data['photo'] = $this->photo->hashName();
                $this->photo->storeAs('tools', $data['photo'], 'public' );
            }

            Tool::create($data);

            $message = "Data Berhasil DI Buat";
        }

        $this->dispatch('created');

        $this->resetForm();

        $this->notif($message);


    }
    

    public function delete($id)
    {
        $tool = Tool::findOrFail($id);

        $tool->delete($id);

        $message = "Data Berhasil DI Hapus";

        $this->notif($message);
    }

    

    public function render()
    {
        return $this->view(
            [
                "tools" => Tool::query()->
                where(function ($query){
                $query->where('id', 'like', '%'.$this->search.'%')
                ->orwhere('name', 'like', '%'.$this->search.'%')
                ->orwhere('brand', 'like', '%'.$this->search.'%')
                ->orwhere('stock', 'like', '%'.$this->search.'%');
                })->latest()->paginate(5)
            ]
        )
        ->layout('layouts.app')
        ->title("Tools");
    }
};
?>

<div
x-on:edit.window="$openModal('tool')"
x-on:created.window="$closeModal('tool')">
    <div class="mx-auto max-w-6xl px-3 my-5">
        <div class="flex justify-between my-3">
            <x-button icon="plus" x-on:click="$openModal('tool')" />
            <div class="max-w-2xl">
                <x-input icon="magnifying-glass" placeholder="Search..." wire:model.live="search"></x-input>
            </div>
        </div>

        <x-card>
            <x-slot name="title"><h1>Tools</h1></x-slot>

            <div class="overflow-x-auto w-full">
                <table class="w-full text-left">
                    <thead>
                        <tr>
                            <th class="p-3">ID</th>
                            <th class="p-3">Photo</th>
                            <th class="p-3">Name</th>
                             <th class="p-3">Category</th>
                            <th class="p-3">Brand</th>
                            <th class="p-3">Description</th>
                            <th class="p-3">Stock</th>
                            <th class="p-3">Max Loan</th>
                            <th class="p-3">Action</th>
                            
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($tools as $tool)
                        <tr>
                            <td class="p-3">{{ $tool->id }}</td>
                            <td class="p-3"><img src="{{asset('storage/tools/'. $tool->photo)}}" class="max-w-40 rounded" /></td>
                            <td class="p-3">{{ $tool->name }}</td>
                            <td class="p-3">{{ $tool->category->name }}</td>
                            <td class="p-3">{{ $tool->brand }}</td>
                            <td class="p-3">{{ $tool->description}}</td>
                            <td class="p-3">{{ $tool->stock}}</td>
                            <td class="p-3">{{ $tool->max_loan}}</td>
                            <td class="p-3">
                                <div class="flex justify-center space-x-2">
                                    <x-button warning icon="pencil" wire:click="edit('{{$tool->id}}')" />
                                    <x-button negative icon="trash" x-on:confirm="{
                                    icon: 'warning',
                                    title: 'Yakin?',
                                    description: 'Yakin ingin hapus data ini?',
                                    method: 'delete',
                                    params: '{{$tool->id}}'
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

    <x-modal-card name="tool">
        <x-slot name="title">@if ($this->id)
            <h1>Edit User</h1>
        @else
            <h1>Add User</h1>
        @endif</x-slot>

        <div class="flex flex-col space-y-2">
            <label for="" class="dark:text-gray-400 text-sm font-md">Profile</label>
            <input type="file" wire:model="photo" class="block">

            <x-input label="Name" wire:model="name" />
            <x-native-select
            placeholder="Choose Category"
            label="Category"
            :options="$this->categories"
            option-value="id"
            option-label="name"
            wire:model="category"
            />

            <x-native-select
            placeholder="Choose Brand"
            label="Brand"
            :options="$this->brandOptions"
            wire:model="brand"
            />

            <x-number label="stock" wire:model="stock" />
            <x-number wire:model="max_loan" label="Max Loan" />

            <x-textarea wire:model="description" label="Description" /> 

            <x-slot name="footer">
                <div class="flex justify-end space-x-2">
                    <x-button flat label="Close" x-on:click="close" />
                    <x-button :label="$this->id? 'Update':'Save'" wire:click="save" />
                </div>
            </x-slot>
        </div>
    </x-modal-card>
     {{-- Always remember that you are absolutely unique. Just like everyone else. - Margaret Mead --}}

</div>
