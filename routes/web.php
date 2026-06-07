<?php

use App\Mail\ApplicationHiredMail;
use App\Mail\ApplicationReceivedMail;
use App\Mail\ApplicationRejectedMail;
use App\Mail\InterviewInvitationMail;
use Illuminate\Support\Facades\Route;

// ─── Email Preview Routes (local only) ───────────────────────────────────────
if (app()->environment('local')) {
    Route::prefix('_email-preview')->group(function () {

        Route::get('/interview-invitation', fn () => new InterviewInvitationMail(
            candidate_name:     'Budi Santoso',
            vacancy_title:      'Software Engineer',
            interview_date:     'Senin, 16 Juni 2026',
            interview_time:     '10.00 – 11.00 WIB',
            interview_location: 'Google Meet (link dikirim terpisah)',
            interviewer_name:   'Siti Rahmawati, S.Psi.',
            notes:              'Siapkan portofolio dan CV terbaru Anda.',
            portal_url:         config('app.url').'/applications',
        ))->name('email-preview.interview-invitation');

        Route::get('/application-hired', fn () => new ApplicationHiredMail(
            candidate_name:  'Budi Santoso',
            vacancy_title:   'Software Engineer',
            position_name:   'Junior Software Engineer',
            department_name: 'Technology & Product',
            start_date:      'Senin, 23 Juni 2026',
            next_steps:      'Tim HR akan mengirimkan dokumen onboarding ke email Anda dalam 3 hari kerja.',
            portal_url:      config('app.url').'/applications',
        ))->name('email-preview.application-hired');

        Route::get('/application-rejected', fn () => new ApplicationRejectedMail(
            candidate_name:   'Budi Santoso',
            vacancy_title:    'Software Engineer',
            position_name:    'Junior Software Engineer',
            rejection_reason: '',
            vacancies_url:    config('app.url').'/vacancies',
        ))->name('email-preview.application-rejected');

        Route::get('/application-received', fn () => new ApplicationReceivedMail(
            candidate_name:   'Budi Santoso',
            vacancy_title:    'Software Engineer',
            application_code: 'APP-2026-00123',
            applied_at:       '7 Juni 2026, 20.00 WIB',
            portal_url:       config('app.url').'/applications',
        ))->name('email-preview.application-received');

    });
}

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
    Route::livewire('/hr/dss', 'pages::hr.dss')->name('hr.dss');
    Route::livewire('/hr/dss/{vacancyId}/result', 'pages::hr.dss.result')->name('hr.dss.result');
});

// ─── Employee Routes ──────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified', 'role:employee'])->group(function () {
    Route::livewire('/employee/dashboard', 'pages::employee.dashboard')->name('employee.dashboard');
});

require __DIR__.'/settings.php';
