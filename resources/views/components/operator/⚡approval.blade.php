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

         foreach($loan->loan_details as $detail){

            $tool = Tool::findOrFail($detail->tool_id);
            $tool->increment('stock', $detail->qty);
        }


        $loan->update([
            'status' => 'returned',
            'returned_at' => Carbon::today(),
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
    
{{-- Search --}}
<div class="my-4 px-3">
    <div class="flex justify-end">
        <x-input
            wire:model.live="search"
            icon="magnifying-glass"
            placeholder="Search loans..."
            class="w-full sm:max-w-sm"
        />
    </div>
</div>

{{-- Loan Management --}}
<div class="grid grid-cols-1 gap-5 px-3 pb-6 md:grid-cols-2 xl:grid-cols-3">

    {{-- Pending --}}
    <div class="min-w-0">
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                Pending
            </h1>

            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                {{ $pendingLoans->count() }}
            </span>
        </div>

        <div class="flex flex-col gap-3">
            @forelse ($pendingLoans as $pendingLoan)
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">

                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="truncate font-semibold text-gray-900 dark:text-gray-100">
                                {{ $pendingLoan->user?->name }}
                            </h2>

                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Created {{ $pendingLoan->created_at }}
                            </p>
                        </div>

                        <span class="shrink-0 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                            Pending
                        </span>
                    </div>

                    <div class="my-4 border-t border-gray-100 dark:border-gray-700"></div>

                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        Requested Tools
                    </p>

                    <div class="flex flex-col gap-2">
                        @foreach ($pendingLoan->loan_details as $detail)
                            <div class="flex items-start justify-between gap-3 text-sm">
                                <span class="text-gray-700 dark:text-gray-300">
                                    {{ $detail->tool->name }}
                                </span>
                                <span class="shrink-0 rounded-md bg-gray-100 px-2 py-0.5 font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                    x {{ $detail->qty }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 flex gap-2 border-t border-gray-100 pt-3 dark:border-gray-700">
                        <x-button
                            negative
                            label="Reject"
                            class="w-full"
                            x-on:confirm="{
                                icon: 'warning',
                                title: 'Yakin Reject data ini?',
                                method: 'reject',
                                params: {{ $pendingLoan->id }}
                            }"
                        />

                        <x-button
                            label="Approve"
                            class="w-full"
                            x-on:confirm="{
                                title: 'Yakin?',
                                description: 'Yakin ingin Approve data ini?',
                                method: 'approve',
                                params: {{ $pendingLoan->id }}
                            }"
                        />
                    </div>
                </div>
            @empty
                <x-alert warning title="No pending loans found" />
            @endforelse
        </div>
    </div>

    {{-- Approved --}}
    <div class="min-w-0">
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                Approved
            </h1>

            <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                {{ $approvedLoans->count() }}
            </span>
        </div>

        <div class="flex flex-col gap-3">
            @forelse ($approvedLoans as $approvedLoan)
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">

                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="truncate font-semibold text-gray-900 dark:text-gray-100">
                                {{ $approvedLoan->user?->name }}
                            </h2>

                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Created {{ $approvedLoan->created_at }}
                            </p>
                        </div>

                        <span class="shrink-0 rounded-full bg-blue-100 px-2.5 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                            Approved
                        </span>
                    </div>

                    <div class="my-4 border-t border-gray-100 dark:border-gray-700"></div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-700/50">
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Loan Date
                            </p>
                            <p class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-200">
                                {{ $approvedLoan->loan_date }}
                            </p>
                        </div>

                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-700/50">
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Due Date
                            </p>
                            <p class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-200">
                                {{ $approvedLoan->due_date }}
                            </p>
                        </div>
                    </div>

                    <p class="mb-2 mt-4 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        Requested Tools
                    </p>

                    <div class="flex flex-col gap-2">
                        @foreach ($approvedLoan->loan_details as $detail)
                            <div class="flex items-start justify-between gap-3 text-sm">
                                <span class="text-gray-700 dark:text-gray-300">
                                    {{ $detail->tool->name }}
                                </span>
                                <span class="shrink-0 rounded-md bg-gray-100 px-2 py-0.5 font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                    x {{ $detail->qty }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 border-t border-gray-100 pt-3 dark:border-gray-700">
                        <x-button
                            label="Lend Tools"
                            icon="hand-raised"
                            class="w-full"
                            x-on:confirm="{
                                title: 'Yakin Meminjamkan data ini?',
                                method: 'lend',
                                params: {{ $approvedLoan->id }}
                            }"
                        />
                    </div>
                </div>
            @empty
                <x-alert warning title="No approved loans found" />
            @endforelse
        </div>
    </div>

    {{-- Return Request --}}
    <div class="min-w-0">
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                Return Requests
            </h1>

            <span class="rounded-full bg-purple-100 px-3 py-1 text-xs font-semibold text-purple-700 dark:bg-purple-900/40 dark:text-purple-300">
                {{ $returnRequest->count() }}
            </span>
        </div>

        <div class="flex flex-col gap-3">
            @forelse ($returnRequest as $request)
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">

                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="truncate font-semibold text-gray-900 dark:text-gray-100">
                                {{ $request->user?->name }}
                            </h2>

                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Created {{ $request->created_at }}
                            </p>
                        </div>

                        <span class="shrink-0 rounded-full bg-purple-100 px-2.5 py-1 text-xs font-medium text-purple-700 dark:bg-purple-900/40 dark:text-purple-300">
                            Return
                        </span>
                    </div>

                    <div class="my-4 border-t border-gray-100 dark:border-gray-700"></div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-700/50">
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Loan Date
                            </p>
                            <p class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-200">
                                {{ $request->loan_date }}
                            </p>
                        </div>

                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-700/50">
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Due Date
                            </p>
                            <p class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-200">
                                {{ $request->due_date }}
                            </p>
                        </div>
                    </div>

                    <p class="mb-2 mt-4 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        Returned Tools
                    </p>

                    <div class="flex flex-col gap-2">
                        @foreach ($request->loan_details as $detail)
                            <div class="flex items-start justify-between gap-3 text-sm">
                                <span class="text-gray-700 dark:text-gray-300">
                                    {{ $detail->tool->name }}
                                </span>
                                <span class="shrink-0 rounded-md bg-gray-100 px-2 py-0.5 font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                    x {{ $detail->qty }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 border-t border-gray-100 pt-3 dark:border-gray-700">
                        <x-button
                            label="Confirm Return"
                            icon="check"
                            class="w-full"
                            x-on:confirm="{
                                title: 'Yakin Meminjamkan data ini?',
                                method: 'return',
                                params: {{ $request->id }}
                            }"
                        />
                    </div>
                </div>
            @empty
                <x-alert warning title="No return requests found" />
            @endforelse
        </div>
    </div>

</div>

    {{-- Be present above all else. - Naval Ravikant --}}
</div>