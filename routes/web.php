<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

Route::middleware(['auth', 'verified', 'role:admin'])->group( function(){
        Route::view('/admin/departments','pages.admin.departments')->name('admin.departments');
        Route::livewire('/admin/positions', 'pages::admin.positions')->name('admin.positions');
        Route::livewire('/admin/dashboard', 'pages::admin.dashboard')->name('admin.dashboard');
        Route::livewire('/admin/users', 'pages::admin.users')->name('admin.users');
    });

require __DIR__.'/settings.php';
