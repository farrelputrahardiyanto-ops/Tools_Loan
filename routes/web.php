<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});


Route::middleware(['auth', 'role:admin'])->group(function(){
    Route::livewire('/admin/users', 'admin.users')->name('users');
    Route::livewire('/admin/categories', 'admin.categories')->name('categories');
    Route::livewire('/admin/tools', 'admin.tools')->name('tools');
    Route::livewire('/admin/max-loan', 'admin.max_loan')->name('max_loan');
    Route::livewire('/admin/loans', 'admin.loans')->name('loans');
    Route::livewire('/admin/loan_details', 'admin.loan_details')->name('loan_details');
});


Route::middleware(['auth', 'role:user'])->group(function(){
    Route::livewire('loans', 'users.loan')->name('loan');
    Route::livewire('history', 'users.history')->name('user.history');
});

Route::middleware(['auth', 'role:operator'])->group(function(){
    Route::livewire('/operator/approval', 'operator.approval')->name('approval');
});

Route::livewire('/login', 'auth.login')->name('login');