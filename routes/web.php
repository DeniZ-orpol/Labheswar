<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BranchController;
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

// Route::get('/user', [UserController::class, 'index'])->name('users.index')->middleware('auth');
// Route::get('/user/{id}', [UserController::class, 'show'])->name('customers.show')->middleware('auth');
// Route::get('/party/create', [UserController::class, 'create'])->name('customers.create')->middleware('auth');
// Route::post('/user/store', [UserController::class, 'store'])->name('customers.store')->middleware('auth');
// Route::get('/user/edit/{customer}', [UserController::class, 'edit'])->name('customers.edit')->middleware('auth');
// Route::post('/user/update/{customer}', [UserController::class, 'update'])->name('customers.update')->middleware('auth');

Route::group(['middleware' => 'auth'], function () {
    Route::get('/branch', [BranchController::class, 'index'])->name('branch.index');
    Route::get('/branch/create', [BranchController::class, 'create'])->name('branch.create');
    Route::post('/branch/store', [BranchController::class, 'store'])->name('branch.store');
    Route::get('/branch/edit/{branch}', [BranchController::class, 'edit'])->name('branch.edit');
    Route::post('/branch/update/{branch}', [BranchController::class, 'update'])->name('branch.update');
    Route::get('/branch/{branch}', [BranchController::class, 'show'])->name('branch.show');
    Route::delete('/branch/{branch}', [BranchController::class, 'destroy'])->name('branch.destroy');
});
Route::get('/users', [UserController::class, 'index'])->name('users.index')->middleware('auth');
Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show')->middleware('auth');
Route::get('/user/create', [UserController::class, 'create'])->name('users.create')->middleware('auth');
Route::post('/users/store', [UserController::class, 'store'])->name('users.store')->middleware('auth');
Route::get('/users/edit/{user}', [UserController::class, 'edit'])->name('users.edit')->middleware('auth');
Route::put('/users/update/{user}', [UserController::class, 'update'])->name('users.update')->middleware('auth');
Route::post('/users/delete/{id}', [UserController::class, 'destroy'])->name('users.delete')->middleware('auth');
