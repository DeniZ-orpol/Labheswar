<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('login/login');
// });

Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('login', [AuthController::class, 'login']);
Route::get('logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', [DashboardController::class, 'index'])->name('dashboard')->middleware('auth');

Route::get('/user', [UserController::class, 'index'])->name('users.index')->middleware('auth');
Route::get('/user/{id}', [UserController::class, 'show'])->name('customers.show')->middleware('auth');
Route::get('/party/create', [UserController::class, 'create'])->name('customers.create')->middleware('auth');
Route::post('/user/store', [UserController::class, 'store'])->name('customers.store')->middleware('auth');
Route::get('/user/edit/{customer}', [UserController::class, 'edit'])->name('customers.edit')->middleware('auth');
Route::post('/user/update/{customer}', [UserController::class, 'update'])->name('customers.update')->middleware('auth');
