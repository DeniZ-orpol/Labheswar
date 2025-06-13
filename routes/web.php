<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Request;

// Route::get('/', function () {
//     return view('login/login');
// });

Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('login', [AuthController::class, 'login']);
Route::get('logout', [AuthController::class, 'logout'])->name('logout');


Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetOtp'])->name('password.email');
Route::get('/verify-otp', [ForgotPasswordController::class, 'showOtpForm'])->name('password.otp');
Route::post('/verify-otp', [ForgotPasswordController::class, 'verifyOtp'])->name('password.verifyOtp');
Route::get('/reset-password', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('password.update');


Route::group(['middleware' => 'custom.auth','check.remember'], function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::get('/branch', [BranchController::class, 'index'])->name('branch.index');
    // Route::get('/branch/create', [BranchController::class, 'create'])->name('branch.create');
    // Route::post('/branch/store', [BranchController::class, 'store'])->name('branch.store');
    Route::get('/branch/edit/{branch}', [BranchController::class, 'edit'])->name('branch.edit');
    Route::post('/branch/update/{branch}', [BranchController::class, 'update'])->name('branch.update');
    Route::get('/branch/{branch}', [BranchController::class, 'show'])->name('branch.show');
    // Route::post('/branch/delete/{branch}', [BranchController::class, 'destroy'])->name('branch.delete');

    Route::resource('users', UserController::class)->except(['show', 'edit', 'update', 'destroy']);
    Route::get('/users/{branchId}/show/{id}', [UserController::class, 'show'])->name('users.show');
    Route::get('/users/{branchId}/edit/{id}', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{branchId}/update/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{branchId}/delete/{id}', [UserController::class, 'destroy'])->name('users.destroy');

    Route::resource('roles', RoleController::class);
});


Route::get('/test-redirect', function () {
    return redirect()->route('dashboard');
});
