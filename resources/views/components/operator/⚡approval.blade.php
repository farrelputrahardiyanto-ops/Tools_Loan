<?php

use Livewire\Component;
use App\Models\Loan;
use App\Models\Tool;
use Carbon\Carbon;
use WireUi\Traits\WireUiActions;

new class extends Component
{
    use WireUiActions;

    public $search = '';


    public function approve($id)
    {
        $loan = Loan::findOrFail($id);

        $today = Carbon::today()->format('Y-m-d');
        $due_date = Carbon::today()->addDays(7)->format('Y-m-d');

        $loan->update([
            'loan_date' => $today,
            'approved_by' => auth()->user()->id,
            'due_date' => $due_date,
            'status' => 'approved'
        ]);
    }

    public function reject($id)
    {
        $loan = Loan::findOrFail($id);

        foreach($loan->loan_details as $detail){

            $tool = Tool::findOrFail($detail->tool_id);
            $tool->increment('stock', $detail->qty);
        }

        $loan->update(['status'=> 'rejected']);
    }

    public function lend($id)
    {
        $loan = Loan::findOrFail($id);

        $loan->update(['status' => 'borrowed']);
    }

    public function return($id)
    {
        $loan = Loan::findOrFail($id);

        $dueDate = Carbon::parse($loan->due_date);
        $returnDate = $loan->returned_at
        ? Carbon::parse($loan->returned_at)
        : Carbon::today();

        $lateDays = $returnDate->greaterThan($dueDate)
            ? (int) $dueDate->diffInDays($returnDate)
            : 0;    

        $finePerDay = 10000;

        $fineAmount = $finePerDay*$lateDays;

        $loan->update([
            'status' => 'returned',
            'fine_amount' => $fineAmount
        ]);
    }
    
    public function render()
    {
        $loans = Loan::query()->
            where(function ($query){
                $query->where('id', 'like', '%'.$this->search.'%')
                ->orwhere('user_id', 'like', '%'.$this->search.'%')
                ->orwhere('status', 'like', '%'.$this->search.'%')
                ->orWhereHas('user', function ($q){
                    $q->where('name', 'like', '%'.$this->search.'%');
                });
            })->latest()->get();


        return $this->view([
            "pendingLoans" => $loans->where('status', 'pending'),
            'approvedLoans' => $loans->where('status', 'approved'),
            'returnRequest' => $loans->where('status', 'return_request') 
        ])->layout('layouts.operator')
        ->title('Approval');
    }

};
?>

<div>

    <div class="w-full flex justify-end my-2 px-2">
        <x-input wire:model.live="search" icon="magnifying-glass" placeholder="Search..."  class="max-w-3xl"/>
    </div>

    <div class="grid grid-cols-12 gap-2">
       
        <div class="col-span-12 md:col-span-4 overflow-y-auto min-h-screen ">
             <h1 class="text-3xl font-semibold m-3 dark:text-gray-400">Pending</h1>
            @forelse ($pendingLoans as $pendingLoan)
                <div class="p-3 rounded-lg dark:text-gray-400 dark:bg-gray-800 my-4 ">
                    <h1 class="text-2xl font-semibold">{{ $pendingLoan->user?->name}}</h1>
                    <p class="text-sm my-2">Created At: {{ $pendingLoan->created_at}}</p>

                    <div class="flex">
                        <p class="text-sm">Tools:</p>
                        @foreach ($pendingLoan->loan_details as $detail)
                            <p class="text-sm mx-2">{{$detail->tool->name}} x {{$detail->qty}}</p>
                        @endforeach
                    </div>

                    <div class="flex justify-between mt-3">
                        <x-button negative label="Reject" class="w-full mx-1" x-on:confirm="{
                            icon: 'warning',
                            title: 'Yakin Rejected data ini?',
                            method: 'reject',
                            params: {{$pendingLoan->id}}
                        }" />
                        <x-button label="Approve" class="w-full mx-1" x-on:confirm="{
                                    title: 'Yakin?',
                                    description: 'Yakin ingin Approve data ini?',
                                    method: 'approve',
                                    params: {{$pendingLoan->id}}
                        }" />
                    </div>
                </div>
            @empty
                <x-alert warning title="Data Null" />
            @endforelse
        </div>

         <div class="col-span-12 md:col-span-4 overflow-y-auto min-h-screen ">
             <h1 class="text-3xl font-semibold m-3 dark:text-gray-400">Approved </h1>
            @forelse ($approvedLoans as $approvedLoan)
                <div class="p-3 rounded-lg dark:text-gray-400 dark:bg-gray-800 my-4 mx-3">
                    <h1 class="text-2xl font-semibold">{{ $approvedLoan->user?->name}}</h1>
                    <p class="text-sm my-2">Created At: {{ $approvedLoan->created_at}}</p>
                    <p class="text-sm my-2">Loan Date: {{ $approvedLoan->loan_date}}</p>
                    <p class="text-sm my-2">Due Date: {{ $approvedLoan->due_date}}</p>



                    <div class="flex">
                        <p class="text-sm">Tools:</p>
                        @foreach ($approvedLoan->loan_details as $detail)
                            <p class="text-sm mx-2">{{$detail->tool->name}} x {{$detail->qty}}</p>
                        @endforeach
                    </div>

                    <div class="flex justify-between mt-3">
                        <x-button label="lend" class="w-full mx-1" x-on:confirm="{
                            title: 'Yakin Meminjamkan data ini?',
                            method: 'lend',
                            params: {{$approvedLoan->id}}
                        }"/>
                    </div>
                </div>
            @empty
                <x-alert warning title="Data Null" />
            @endforelse
        </div>

         <div class="col-span-12 md:col-span-4 overflow-y-auto min-h-screen ">
             <h1 class="text-3xl font-semibold m-3 dark:text-gray-400">Return Request </h1>
            @forelse ($returnRequest as $request)
                <div class="p-3 rounded-lg dark:text-gray-400 dark:bg-gray-800 my-4 mx-3">
                    <h1 class="text-2xl font-semibold">{{ $request->user?->name}}</h1>
                    <p class="text-sm my-2">Created At: {{ $request->created_at}}</p>
                    <p class="text-sm my-2">Loan Date: {{ $request->loan_date}}</p>
                    <p class="text-sm my-2">Due Date: {{ $request->due_date}}</p>



                    <div class="flex">
                        <p class="text-sm">Tools:</p>
                        @foreach ($request->loan_details as $detail)
                            <p class="text-sm mx-2">{{$detail->tool->name}} x {{$detail->qty}}</p>
                        @endforeach
                    </div>

                    <div class="flex justify-between mt-3">
                        <x-button label="Approve" class="w-full mx-1" x-on:confirm="{
                            title: 'Yakin Meminjamkan data ini?',
                            method: 'return',
                            params: {{$request->id}}
                        }"/>
                    </div>
                </div>
            @empty
                <x-alert warning title="Data Null" />
            @endforelse
        </div>
        

        

    </div>
    {{-- Be present above all else. - Naval Ravikant --}}
</div>