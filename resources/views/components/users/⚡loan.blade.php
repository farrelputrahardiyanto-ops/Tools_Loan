<?php

use Livewire\Component;
use App\Models\Tool;
use App\Models\MaxLoan;
use WireUi\Traits\WireUiActions;
use Carbon\Carbon;
use App\Models\Loan;

new class extends Component
{

    use WireUiActions;  

    public $search = '';

    public $cart = [];


    public $cart_number = 0;

    public $today;

    public $due_date;

    public $user_id;

    public $user_loan;

    public function mount()
    {
        $this->today = Carbon::today()->format('Y-m-d');
        $this->due_date = Carbon::today()->addDays(7)->format('Y-m-d');

        $this->user_id = auth()->user()->id;
        $this->user_loan = Loan::where('user_id', $this->user_id)->whereIn('status', ['pending', 'approved'])->first();
    }

    public function addToCart($toolId)
    {
        $tool = Tool::findOrfail($toolId);

        $max_loan = MaxLoan::findOrFail(1);

        // Mengecek apakah Tool dengan id tsb sudah ada di cart
         if(isset($this->cart[$toolId]))
          {

            // Pengecekan agar jumlah tool yang dipinjam tidak melebihi jumlah maksimal dan  Pengecakan agar tool tidak minus
            if($tool->max_loan == $this->cart[$toolId]['qty'] || $tool->stock == $this->cart[$toolId]['qty']){
                 $this->notification()->send([
                    'icon' => 'warning',
                    'title' => 'Maksimal peminjaman',
                    'description' => 'Maksmal Peminjaman '
                ]); 
            }
           else {
                $this->cart[$toolId]['qty']++;
            }
        }else {
            if($this->cart_number == $max_loan->max_loan){
                $this->notification()->send([
                    'icon' => 'warning',
                    'title' => 'Maksimal peminjaman',
                    'description' => 'Maksmal Peminjaman '
                ]); 
            }else{
            $this->cart[$toolId] = [
                'id' => $tool->id,
                'name' => $tool->name,
                'qty' => 1
            ];
                $this->cart_number += 1;
            }
        }

    }

    public function deleteFromCart($id)
    {
        unset($this->cart[$id]);

        $this->cart_number -= 1 ;
    }

    public function decreaseQty($toolId)
    {
        if($this->cart[$toolId]['qty'] > 1){
            $this->cart[$toolId]['qty'] -= 1;
        }
    }

    public function invoice()
    {
        $this->dispatch('open');
    }

    public function save()
    {
        $this->validate([
            'user_id' => 'required',
            'due_date'  => 'required',
            'cart.*.id' => 'required',
            'cart.*.qty' => 'required|numeric|min:1'
        ]);

        DB::transaction(function() {
            $loan = Loan::create([
                'user_id' => $this->user_id,
                'loan_date' => $this->today,
                'due_date' => $this->due_date,
                'status' => 'pending'
            ]);

        $details = collect($this->cart)->map(function($item){
            return [
                'tool_id' => $item['id'],
                'qty' => $item['qty']
            ];
        })->toArray();

        $loan->loan_details()->createMany($details);

        });

        $this->user_loan = Loan::where('user_id', $this->user_id)->whereIn('status', ['pending', 'approved'])->first();


        $this->resetAll();

        $this->dispatch('close');
    }

    public function resetAll()
    {
        $this->reset([
            'cart_number'
        ]);

        $this->cart = [];
    }



    public function render()
    {
        return $this->view([
            "tools" =>  Tool::query()->
            where(function ($query){
                $query->where('name', 'like', '%'.$this->search.'%')
                ->orwhere('description', 'like', '%'.$this->search.'%')
                ->orwhere('brand', 'like', '%'.$this->search.'%')
                ->orwhere('stock', 'like', '%'.$this->search.'%');
            })->latest()->get()
        ])
        ->layout('layouts.app')
        ->title('Cashier');
    }
};
?>


<div 
x-on:open.window="$openModal('invoice')"
x-on:close.window="$closeModal('invoice')" 
x-on:print.window="window.print()"
>
    <div class="grid grid-cols-12  pb-15 md:pb-0">
        <div class="col-span-12 md:col-span-10 pb-80">

            <div class="flex w-full my-2 px-3 py-5 justify-center md:justify-end ">
                <div class=" w-full md:max-w-xl  ">
                    <x-input icon="magnifying-glass" wire:model.live="search" placeholder="Search..."  />
                </div>
            </div>

            <div class="grid md:grid-cols-6 grid-cols-2 gap-4  px-3 my-2 h-auto  ">
                @if ($this->user_loan)
                     <x-alert warning title="You Can't Borrowed Tools" class="col-span-full" />
                @endif
                @forelse ($tools as $tool)
                    <div class="flex flex-col shadow dark:bg-gray-800 rounded-md bg-stone-200">
                        <img src="{{asset('storage/tools/'. $tool->photo)}}" class="w-full h-full object-cover rounded-md" alt="">
                        <h1 class="text-md text-center mb-1 mt-2 font-semibold dark:text-gray-300">{{$tool->name}}</h1>
                       
                            <div class="flex justify-between px-2 mt-3 mb-2 ">
                                
                                <p class="text-sm text-gray-400">Stock: {{$tool->stock}}</p>

                                <p class="text-sm text-gray-400">Max Loan: {{$tool->max_loan}}</p>
                                
                            </div>
                            <div class="w-full  p-2 mt-auto ">
                                <x-button icon="plus" class="w-full" :disabled="$tool->stock == 0 or $this->user_loan"  wire:click="addToCart('{{$tool->id}}')"/>
                            </div>
                    </div>
                @empty

                <x-alert warning title="Tool Not Found" class="col-span-full" />

                @endforelse

                
            </div>
        </div>

        


        <div class="md:col-span-2 min-h-screen dark:bg-gray-900 bg-stone-200 md:block lg:block hidden overflow-y-auto dark:text-gray-400">
             <div class="w-full mt-6  flex justify-center ">
                <x-avatar sm size="w-20 h-20" :src="asset('storage/users/'. auth()->user()->profile )"/>
             </div>
             <h1 class="my-1 text-3xl text-center"></h1>

             <h2 class="text-lg text-center my-5">Halo, {{auth()->user()->name}}  </h2>
             @forelse ($cart as $item)
             <div class="flex justify-between dark:bg-gray-900 border-2 border-gray-600 rounded-xl p-2  mx-3 mb-2 ">
                 <div class="flex flex-col">
                    <h3 class="text-md">{{ $item['name']}}</h3>

                 </div>

                 <div class="flex space-x-1 py-1">
                    <x-button sm flat icon="plus" wire:click="addToCart('{{$item['id']}}')" />
                    <h1 class="twxt-lg my-auto">{{$item['qty']}}</h1>
                    <x-button sm flat icon="minus" wire:click="decreaseQty('{{$item['id']}}')" />
                      <div class="absout -mx-4  -my-4">
                    <x-mini-button rounded secondary icon="x-mark" class="w-0.5 h-0.5"   wire:click="deleteFromCart('{{$item['id']}}')"/>
                 </div>
                 </div>
                
             </div>

             
             @empty
                <div class="w-full p-3">
                     <x-alert info title="Cart Null" />
                </div>
             @endforelse

             @if ($this->cart != Null)
                 <div class="w-full flex flex-col space-y-2 justify-center px-3 h-auto">
                   
                    <h1 class="text-center text-lg">Cart Number: {{$this->cart_number}} </h1>
                    <x-button label="Checkout"  wire:click="invoice" class="mx-auto my-3" />
                 </div>
             @endif
        </div>
    </div>

    <div class="fixed bottom-0 z-10 rounded-t-xl dark:bg-gray-900 w-full md:hidden">
         <div class="w-full mt-6  flex justify-center space-x-2 ">
            <div class="">
                 <x-avatar s :src="asset('storage/users/')"/>
            </div>
               
                <h1 class="my-1 text-3xl text-center my-auto"></h1>
             </div>
             

             <h2 class="text-lg text-center mt-1">Cart   </h2>
             <div class="max-h-30 overflow-y-auto">
                @forelse ($cart as $item)
                <div class="flex justify-between dark:bg-gray-900 border-2 border-gray-600 rounded-xl p-2  mx-3 mb-2">
                    <div class="flex flex-col">
                        <h3 class="text-md">{{ $item['name']}}</h3>

                        <p class="text-sm text-gray-500">Rp.</p>
                     </div>

                    <div class="flex space-x-1 py-1">
                        <x-button sm flat icon="plus" wire:click="addToCart('{{$item['id']}}')" />
                        <h1 class="twxt-lg my-auto">{{$item['qty']}}</h1>
                        <x-button sm flat icon="minus" wire:click="decreaseQty('{{$item['id']}}')" />
                        <div class="absout -mx-4  -my-4">
                            <x-mini-button rounded secondary icon="x-mark" class="w-0.5 h-0.5"   wire:click="deleteFromCart('{{$item['id']}}')"/>
                        </div>
                 </div>
                
             </div>

             
             @empty
                <div class="w-full p-3">
                     <x-alert info title="Cart Null" />
                </div>
             @endforelse

             
             
             </div>
             <div class="flex flex-col my-2 shadow-[35px_35px_35px_35px_rgba(0,0,0,0.25)] z-10">
                @if ($this->cart != Null)
                 <div class="w-full flex flex-col spacey-2 justify-center px-3 h-auto">
                   
                        
                    
                 </div>
             @endif
                <h1 class="text-center text-lg">Total: Rp</h1>
                    <x-button label="Checkout"  wire:click="invoice" class="mx-auto my-3" />
             </div>
    </div>

    <x-modal-card name="invoice" persistent >
        
        <x-slot name="title" class="w-full">
        <div class="flex w-full justify-end">
            <x-mini-button rounded  icon="x-mark" x-on:click="close" />
        </div>
    </x-slot>
    <section id="invoice">
    <div class="flex justify-center flex-col my-3">
        <h1 class="text-xl font-bold text-center">Power Tools Loan</h1>
        <h1 class="text-xl font-bold text-center">Jl.Rongawi-Suki 001/002</h1>
        <h1 class="text-xl font-bold text-center">Kec. Anti Suki Kota Ngawi 6969</h1>
    </div>
        <div class="overflow-y-auto w-full px-4  py-2 mt-8">
            <div class="flex flex-col border-dashed py-2 border-b border-t ">
                <h1 class="text-sm"><span class="inline-block w-20">Date</span>:      {{ $this->today }}</h1>
                <h1 class="text-sm"><span class="inline-block w-20">Due Date</span>:   {{ $this->due_date}}</h1>
                <h1 class="text-sm"><span class="inline-block w-20">Customer</span>:  {{auth()->user()->name}}</h1>
            </div>
            <div class="border-dashed border-b py-2">

            
            @forelse ($cart as $item)
                <div class="flex flex-col  my-4">
                    <div class="">
                        <div class="flex justify-between w-full">
                                <h1 class="text-sm font-semibold">{{ $item['name']}}</h1>
                                <h1 class="text-sm">{{ $item['qty']}}</h1>
                        </div>
                    </div>
                </div>
            @empty
                
            @endforelse
            </div>

            
        </div>
    </section>
        <x-slot name="footer">
            <div class="flex space-x-2 my-2 print:hidden">                
                    <x-button label="Borrow " wire:click="save"  class="w-full" />
            </div>
        </x-slot>    
    </x-modal-card>
    {{-- The only way to do great work is to love what you do. - Steve Jobs --}}
</div>