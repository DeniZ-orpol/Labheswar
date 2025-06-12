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

Route::get('/users', [UserController::class, 'index'])->name('users.index')->middleware('auth');
Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show')->middleware('auth');
Route::get('/user/create', [UserController::class, 'create'])->name('users.create')->middleware('auth');
Route::post('/users/store', [UserController::class, 'store'])->name('users.store')->middleware('auth');
Route::get('/users/edit/{user}', [UserController::class, 'edit'])->name('users.edit')->middleware('auth');
Route::put('/users/update/{user}', [UserController::class, 'update'])->name('users.update')->middleware('auth');
Route::post('/users/delete/{id}', [UserController::class, 'destroy'])->name('users.delete')->middleware('auth');
