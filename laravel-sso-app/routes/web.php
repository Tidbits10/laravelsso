<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/login', [AuthController::class, 'showLogin']);
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/', [StudentController::class, 'dashboard'])->name('dashboard');
    Route::post('/requests', [StudentController::class, 'storeRequest'])->name('requests.store');
    Route::post('/appointments', [StudentController::class, 'storeAppointment'])->name('appointments.store');
    Route::post('/password', [StudentController::class, 'changePassword'])->name('password.update');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/export', [AdminController::class, 'exportCsv'])->name('export');
    Route::post('/import-csv', [AdminController::class, 'importCsv'])->name('import');
    Route::post('/requests/{serviceRequest}/approve', [AdminController::class, 'approve'])->name('requests.approve');
    Route::post('/requests/{serviceRequest}/reject', [AdminController::class, 'reject'])->name('requests.reject');
    Route::post('/requests/{serviceRequest}/ready', [AdminController::class, 'markReady'])->name('requests.ready');
    Route::post('/appointments/{appointment}/serve', [AdminController::class, 'serveQueue'])->name('appointments.serve');
    Route::get('/scanner', [VerificationController::class, 'scanner'])->name('scanner');
});

Route::post('/api/verify', [VerificationController::class, 'verify'])->middleware('auth')->name('api.verify');
