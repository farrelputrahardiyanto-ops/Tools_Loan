<?php

use Livewire\Component;
use App\Models\User;

new class extends Component
{
    

    public function render()
    {
        return $this->view(
            [
                "users" => User::latest()->paginate(5)
            ]
        )
        ->layout('layouts.app')
        ->title("Users");
    }
};
?>

<div>
    <div class="mx-auto max-w-6xl px-3 my-5">
        <div class="flex justify-between my-3">
            <x-button icon="plus" />
            <div class="max-w-2xl">
                <x-input icon="magnifying-glass" placeholder="Search..."></x-input>
            </div>
        </div>

        <x-card>
            <x-slot name="title"><h1>Users</h1></x-slot>

            <div class="overflow-x-auto w-full">
                <table class="w-full text-left">
                    <thead>
                        <tr>
                            <th class="p-3">ID</th>
                            <th class="p-3">Profile</th>
                            <th class="p-3">Name</th>
                            <th class="p-3">Role</th>
                            <th class="p-3">Email</th>
                            <th class="p-3">Action</th>
                            
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->role }}</td>
                            <td>{{ $user->email }}</td>
                            
                        </tr>
                            
                        @empty
                            <x-alert warning title="Null" />
                        @endforelse
                    
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
    {{-- No surplus words or unnecessary actions. - Marcus Aurelius --}}
</div>