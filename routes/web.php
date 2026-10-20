<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::livewire('/admin/users', 'admin.users')->name('users');
Route::livewire('/admin/categories', 'admin.categories')->name('categories');
Route::livewire('/admin/tools', 'admin.tools')->name('tools');
Route::livewire('/admin/max-loan', 'admin.max_loan')->name('max_loan');


Route::middleware(['auth', 'role:user'])->group(function(){
    Route::livewire('loans', 'users.loan')->name('loan');

});


Route::livewire('/login', 'auth.login')->name('login');