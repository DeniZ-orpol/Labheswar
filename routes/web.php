<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Request;

// Route::get('/', function () {
//     return view('login/login');
// });

Route::get('/migrate', function () {
    Artisan::call('migrate');
    dd('migrated!');
});

Route::get('/linkstorage', function () {
    Artisan::call('storage:link');
    dd('storage:link!');
});

Route::get('/clear-cache', function () {
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('config:cache');
    Artisan::call('view:clear');
    return "Cache cleared successfully";
});

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

    Route::resource('products', ProductController::class);

    // Route::get('/products', [ProductController::class, 'index'])->name('product.index');
    // Route::get('/products/create', [ProductController::class, 'create'])->name('product.create');
    // Route::post('/products/create', [ProductController::class, 'store'])->name('product.store');
});


Route::get('/test-redirect', function () {
    return redirect()->route('dashboard');
});
