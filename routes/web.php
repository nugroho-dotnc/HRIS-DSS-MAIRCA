<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::welcome')->name('home');
Route::livewire('/applications', 'pages::candidate.applications')->name('candidate.applications');
Route::livewire('/vacancies', 'pages::candidate.vacancies')->name('candidate.vacancies');
Route::livewire('/vacancies/{id}', 'pages::candidate.vacancies-show')->name('candidate.vacancies.show');
Route::livewire('/vacancies/{id}/apply', 'pages::candidate.vacancies-apply')->name('candidate.vacancies.apply');
Route::livewire('/vacancies/application/success', 'pages::candidate.applications-success')->name('candidate.vacancies.applications-success');

// ─── Admin Routes ────────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::view('/admin/departments', 'pages.admin.departments')->name('admin.departments');
    Route::livewire('/admin/positions', 'pages::admin.positions')->name('admin.positions');
    Route::livewire('/admin/dashboard', 'pages::admin.dashboard')->name('admin.dashboard');
    Route::livewire('/admin/users', 'pages::admin.users')->name('admin.users');
});

// ─── HR Routes ───────────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified', 'role:hr'])->group(function () {
    Route::livewire('/hr', 'pages::hr.dashboard')->name('hr.dashboard');
    Route::livewire('/hr/vacancies', 'pages::hr.vacancies')->name('hr.vacancies');
    Route::livewire('/hr/vacancies/create', 'pages::hr.vacancies.add-form')->name('hr.vacancies.create');
    Route::livewire('/hr/vacancies/{id}', 'pages::hr.vacancies.preview')->name('hr.vacancies.preview');
    Route::livewire('/hr/applications', 'pages::hr.applications')->name('hr.applications');
    Route::livewire('/hr/applications/{id}', 'pages::hr.applications.show')->name('hr.applications.show');
    Route::livewire('/hr/interviews', 'pages::hr.interviews')->name('hr.interviews');
    Route::livewire('/hr/interviews/{sessionId}/dss', 'pages::hr.interviews.dss')->name('hr.interviews.dss');
});

// ─── Employee Routes ──────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified', 'role:employee'])->group(function () {
    Route::livewire('/employee/dashboard', 'pages::employee.dashboard')->name('employee.dashboard');
});

require __DIR__.'/settings.php';
