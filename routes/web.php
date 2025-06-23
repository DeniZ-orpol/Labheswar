<?php

use App\Http\Controllers\AppOrderController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchasePartyController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Artisan;
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


Route::group(['middleware' => 'auth', 'check.remember'], function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/branch', [BranchController::class, 'index'])->name('branch.index');
    // Route::get('/branch/create', [BranchController::class, 'create'])->name('branch.create');
    // Route::post('/branch/store', [BranchController::class, 'store'])->name('branch.store');
    Route::get('/branch/edit/{branch}', [BranchController::class, 'edit'])->name('branch.edit');
    Route::post('/branch/update/{branch}', [BranchController::class, 'update'])->name('branch.update');
    Route::get('/branch/{branch}', [BranchController::class, 'show'])->name('branch.show');
    // Route::post('/branch/delete/{branch}', [BranchController::class, 'destroy'])->name('branch.delete');

    Route::resource('users', UserController::class);

    Route::resource('roles', RoleController::class);

    // Products
    Route::resource('products', ProductController::class);
    Route::post('/product/import', [ProductController::class, 'importProducts'])->name('products.import');

    Route::resource('categories', CategoryController::class);

    Route::resource('inventory', InventoryController::class);

    // Purchase
    Route::get('/purchase', [PurchaseController::class, 'index'])->name('purchase.index');
    Route::get('/purchase/create', [PurchaseController::class, 'create'])->name('purchase.create');
    Route::post('/purchase/create', [PurchaseController::class, 'store'])->name('purchase.store');
    Route::get('/purchase/{id}/edit', [PurchaseController::class, 'edit'])->name('purchase.edit');
    Route::put('/purchase/{id}/update', [PurchaseController::class, 'update'])->name('purchase.update');
    Route::delete('/purchase/{id}/delete', [PurchaseController::class, 'destroy'])->name('purchase.destroy');

    // Purchase party
    Route::get('/purchase/party', [PurchasePartyController::class, 'index'])->name('purchase.party.index');
    Route::get('/purchase/party/create', [PurchasePartyController::class, 'create'])->name('purchase.party.create');
    Route::post('/purchase/party/store', [PurchasePartyController::class, 'store'])->name('purchase.party.store');
    Route::get('/purchase/party/{id}/edit', [PurchasePartyController::class, 'edit'])->name('purchase.party.edit');
    Route::put('/purchase/party/{id}/update', [PurchasePartyController::class, 'update'])->name('purchase.party.update');
    Route::delete('/purchase/party/{id}/delete', [PurchasePartyController::class, 'destroy'])->name('purchase.party.destroy');

    Route::resource('app/orders', AppOrderController::class);
});


Route::get('/test-redirect', function () {
    return redirect()->route('dashboard');
});
