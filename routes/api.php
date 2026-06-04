<?php

use App\Http\Controllers\Api\Admin\DepartmentController;
use App\Http\Controllers\Api\Admin\PositionController;
use App\Http\Controllers\Api\Admin\RecruitmentCriteriaController;
use App\Http\Controllers\Api\Admin\ReportController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Candidate\ApplicationController as CandidateApplicationController;
use App\Http\Controllers\Api\Candidate\VacancyController as CandidateVacancyController;
use App\Http\Controllers\Api\Public\DepartmentController as PublicDepartmentController;
use App\Http\Controllers\Api\Public\PositionController as PublicPositionController;
use App\Http\Controllers\Api\Employee\ProfileController;
use App\Http\Controllers\Api\HR\ApplicationController as HRApplicationController;
use App\Http\Controllers\Api\HR\DecisionController;
use App\Http\Controllers\Api\HR\DashBoardController as HRDashboardController;
use App\Http\Controllers\Api\HR\EmployeeController as HREmployeeController;
use App\Http\Controllers\Api\HR\InterviewController;
use App\Http\Controllers\Api\HR\MAIRCAController;
use App\Http\Controllers\Api\HR\VacancyController as HRVacancyController;
use App\Http\Controllers\Api\Supervisor\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — HRIS MAIRCA
|--------------------------------------------------------------------------
|
| Prefix: /api
| Auth: Laravel Sanctum (Bearer Token)
| Role Guard: RoleMiddleware (role:admin | hr | supervisor | employee | candidate)
|
| Role Hierarchy:
|   public        → Tanpa autentikasi
|   auth:sanctum  → Semua role yang sudah login
|   + role:admin  → Hanya Admin
|   + role:hr     → Hanya HR
|   + role:supervisor → Hanya Supervisor
|   + role:employee   → Hanya Employee
|   + role:candidate  → Hanya Candidate
|
*/

// ═══════════════════════════════════════════════════════════════════════════
// 🔓 PUBLIC ROUTES — Tanpa autentikasi
// ═══════════════════════════════════════════════════════════════════════════

// Authentication
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('api.auth.login');
    Route::post('/register', [AuthController::class, 'registerCandidate'])->name('api.auth.register');
});

// Public Vacancy (Candidate browsing tanpa login — sesuai SRS)
Route::prefix('vacancies')->group(function () {
    Route::get('/', [CandidateVacancyController::class, 'index'])->name('api.vacancies.index');
    Route::get('/{id}', [CandidateVacancyController::class, 'show'])->name('api.vacancies.show');
});

// Public Departments & Positions (tanpa autentikasi)
Route::prefix('departments')->group(function () {
    Route::get('/', [PublicDepartmentController::class, 'index'])->name('api.departments.index');
    Route::get('/{id}', [PublicDepartmentController::class, 'show'])->name('api.departments.show');
});

Route::prefix('positions')->group(function () {
    Route::get('/', [PublicPositionController::class, 'index'])->name('api.positions.index');
    Route::get('/{id}', [PublicPositionController::class, 'show'])->name('api.positions.show');
});

// Apply & Track — PUBLIC (sesuai SRS: candidate tidak wajib login untuk apply)
Route::post('/applications/generate-code', [CandidateApplicationController::class, 'generateCode'])->name('api.candidate.generate-code');
Route::post('/apply', [CandidateApplicationController::class, 'apply'])->name('api.candidate.apply');
Route::get('/track/{applicationCode}', [CandidateApplicationController::class, 'track'])->name('api.candidate.track');

// ═══════════════════════════════════════════════════════════════════════════
// 🔐 AUTHENTICATED ROUTES — Semua role harus login (Sanctum)
// ═══════════════════════════════════════════════════════════════════════════

Route::middleware('auth:sanctum')->group(function () {

    // Auth actions (perlu token)
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
        Route::get('/me', [AuthController::class, 'me'])->name('api.auth.me');
    });

    // ──────────────────────────────────────────────────────────────────────
    // 👑 ADMIN ROUTES
    // ──────────────────────────────────────────────────────────────────────
    Route::middleware('role:admin')->prefix('admin')->name('api.admin.')->group(function () {

        // User Management
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{id}/role', [UserController::class, 'updateRole'])->name('users.role');
        Route::patch('/users/{id}/status', [UserController::class, 'updateStatus'])->name('users.status');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

        // Department Management
        Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
        Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store');
        Route::get('/departments/{id}', [DepartmentController::class, 'show'])->name('departments.show');
        Route::put('/departments/{id}', [DepartmentController::class, 'update'])->name('departments.update');
        Route::delete('/departments/{id}', [DepartmentController::class, 'destroy'])->name('departments.destroy');

        // Position Management
        Route::get('/positions', [PositionController::class, 'index'])->name('positions.index');
        Route::post('/positions', [PositionController::class, 'store'])->name('positions.store');
        Route::get('/positions/{id}', [PositionController::class, 'show'])->name('positions.show');
        Route::put('/positions/{id}', [PositionController::class, 'update'])->name('positions.update');
        Route::delete('/positions/{id}', [PositionController::class, 'destroy'])->name('positions.destroy');
        Route::get('/positions/{id}/criteria', [RecruitmentCriteriaController::class, 'getByPosition'])->name('positions.criteria');

        // DSS MAIRCA Criteria Management
        Route::get('/criteria', [RecruitmentCriteriaController::class, 'index'])->name('criteria.index');
        Route::post('/criteria', [RecruitmentCriteriaController::class, 'store'])->name('criteria.store');
        Route::get('/criteria/{id}', [RecruitmentCriteriaController::class, 'show'])->name('criteria.show');
        Route::put('/criteria/{id}', [RecruitmentCriteriaController::class, 'update'])->name('criteria.update');
        Route::delete('/criteria/{id}', [RecruitmentCriteriaController::class, 'destroy'])->name('criteria.destroy');

        // Likert Scale (nested under criteria)
        Route::get('/criteria/{id}/likert', [RecruitmentCriteriaController::class, 'likertIndex'])->name('criteria.likert.index');
        Route::post('/criteria/{id}/likert', [RecruitmentCriteriaController::class, 'likertStore'])->name('criteria.likert.store');
        Route::delete('/criteria/{id}/likert/{scaleId}', [RecruitmentCriteriaController::class, 'likertDestroy'])->name('criteria.likert.destroy');

        // Reports (read-only)
        Route::get('/reports/recruitment', [ReportController::class, 'recruitment'])->name('reports.recruitment');
    });

    // ──────────────────────────────────────────────────────────────────────
    // 👩‍💼 HR ROUTES
    // ──────────────────────────────────────────────────────────────────────
    Route::middleware('role:hr')->prefix('hr')->name('api.hr.')->group(function () {

        // Vacancy Management
        Route::get('/vacancies', [HRVacancyController::class, 'index'])->name('vacancies.index');
        Route::post('/vacancies', [HRVacancyController::class, 'store'])->name('vacancies.store');
        Route::get('/vacancies/{id}', [HRVacancyController::class, 'show'])->name('vacancies.show');
        Route::put('/vacancies/{id}', [HRVacancyController::class, 'update'])->name('vacancies.update');
        Route::patch('/vacancies/{id}/close', [HRVacancyController::class, 'close'])->name('vacancies.close');
        Route::delete('/vacancies/{id}', [HRVacancyController::class, 'destroy'])->name('vacancies.destroy');

        // Application Management (Review Lamaran)
        Route::get('/applications', [HRApplicationController::class, 'index'])->name('applications.index');
        Route::get('/applications/{id}', [HRApplicationController::class, 'show'])->name('applications.show');
        Route::patch('/applications/{id}/screening', [HRApplicationController::class, 'moveToScreening'])->name('applications.screening');
        Route::patch('/applications/{id}/reject', [HRApplicationController::class, 'reject'])->name('applications.reject');

        // Interview Management
        Route::get('/interviews', [InterviewController::class, 'index'])->name('interviews.index');
        Route::post('/interviews', [InterviewController::class, 'store'])->name('interviews.store');
        Route::get('/interviews/{id}', [InterviewController::class, 'show'])->name('interviews.show');
        Route::put('/interviews/{id}', [InterviewController::class, 'update'])->name('interviews.update');
        Route::get('/interviews/{id}/scores', [InterviewController::class, 'getScores'])->name('interviews.scores.index');
        Route::post('/interviews/{id}/scores', [InterviewController::class, 'storeScores'])->name('interviews.scores.store');

        // DSS MAIRCA — Kalkulasi & Ranking
        Route::post('/mairca/calculate/{vacancyId}', [MAIRCAController::class, 'calculate'])->name('mairca.calculate');
        Route::get('/mairca/ranking/{vacancyId}', [MAIRCAController::class, 'ranking'])->name('mairca.ranking');

        // Keputusan Final & Onboarding
        Route::post('/decisions/{applicationId}', [DecisionController::class, 'decide'])->name('decisions.decide');
        Route::post('/onboarding/{applicationId}', [DecisionController::class, 'onboarding'])->name('decisions.onboarding');

        // Employee Management (HR kelola data karyawan)
        Route::get('/employees', [HREmployeeController::class, 'index'])->name('employees.index');
        Route::get('/employees/{id}', [HREmployeeController::class, 'show'])->name('employees.show');
        Route::put('/employees/{id}', [HREmployeeController::class, 'update'])->name('employees.update');

        // DASHBOARD
        Route::get('/dashboard', [HRDashboardController::class, 'index'])->name('dashboard');
    });

    // ──────────────────────────────────────────────────────────────────────
    // 🧑‍💼 SUPERVISOR ROUTES
    // ──────────────────────────────────────────────────────────────────────
    Route::middleware('role:supervisor')->prefix('supervisor')->name('api.supervisor.')->group(function () {
        Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');
    });

    // ──────────────────────────────────────────────────────────────────────
    // 👤 EMPLOYEE ROUTES
    // ──────────────────────────────────────────────────────────────────────
    Route::middleware('role:employee')->prefix('employee')->name('api.employee.')->group(function () {
        Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::get('/employment', [ProfileController::class, 'employment'])->name('employment');
    });

    // ──────────────────────────────────────────────────────────────────────
    // 🙋 CANDIDATE ROUTES (auth — yang sudah register/login)
    // ──────────────────────────────────────────────────────────────────────
    Route::middleware('role:candidate')->prefix('candidate')->name('api.candidate.')->group(function () {
        Route::get('/applications', [CandidateApplicationController::class, 'myApplications'])->name('applications');
    });

});
