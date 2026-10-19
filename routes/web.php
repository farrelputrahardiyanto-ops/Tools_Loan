<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::livewire('/users', 'admin.users')->name('users');
Route::livewire('/categories', 'admin.categories')->name('categories');
Route::livewire('/tools', 'admin.tools')->name('tools');