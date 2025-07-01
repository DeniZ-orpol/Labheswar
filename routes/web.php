<?php

use App\Http\Controllers\AppOrderController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BankDetailsController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CompanyContoller;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\HsnController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfitAndLooseController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchasePartyController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StockInHandController;
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

    // Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    // Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    // Route::post('/products/store/{branch?}', [ProductController::class, 'store'])->name('products.store');
    // Route::get('/products/{id}/show/{branch?}', [ProductController::class, 'show'])->name('products.show');
    // Route::get('/products/{id}/edit/{branch?}', [ProductController::class, 'edit'])->name('products.edit');
    // Route::put('/products/{id}/update/{branch?}', [ProductController::class, 'update'])->name('products.update');
    // Route::delete('/products/{id}/delete/{branch?}', [ProductController::class, 'destroy'])->name('products.destroy');

    Route::post('/product/import', [ProductController::class, 'importProducts'])->name('products.import');

    // Categories
    // Route::resource('categories', CategoryController::class);
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories/store/{branch?}', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{id}/show/{branch?}', [CategoryController::class, 'show'])->name('categories.show');
    Route::get('/categories/{id}/edit/{branch?}', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{id}/update/{branch?}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{id}/delete/{branch?}', [CategoryController::class, 'destroy'])->name('categories.destroy');

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
    Route::get('/purchase/party/{id}/show', [PurchasePartyController::class, 'show'])->name('purchase.party.show');
    Route::get('/purchase/history', [PurchaseController::class, 'getPurchaseHistory'])->name('purchase.history');

    Route::resource('app/orders', AppOrderController::class);

    // Search routes for search dropdown
    Route::get('/companies/search', [ProductController::class, 'searchCompany'])->name('companies.search');
    Route::get('/categories/search', [ProductController::class, 'searchCategory'])->name('categories.search');
    Route::get('/hsn-code/search', [ProductController::class, 'searchHsnCode'])->name('hsn.search');
    Route::get('/products-search', [ProductController::class, 'searchProduct'])->name('products.search');
    Route::get('/purchase/party/search', [PurchasePartyController::class, 'partySearch'])->name('purchase.party.search');
    Route::post('/company/search', [CompanyContoller::class, 'search'])->name('company.search');

    Route::resource('hsn_codes', HsnController::class);
    Route::resource('company', CompanyContoller::class);

    // Modal data store routes
    Route::post('/categories/modalstore', [CategoryController::class, 'modalStore'])->name('categories.modalstore');
    Route::post('/company/modalstore', [CompanyContoller::class, 'modalStore'])->name('company.modalstore');
    Route::post('/hsn_codes/modalstore', [HsnController::class, 'modalStore'])->name('hsn_codes.modalstore');
    Route::post('/purchase/party/modalstore', [PurchasePartyController::class, 'modalStore'])->name('purchase.party.modalstore');

    // Ledger routes
    Route::get('/ledgers', [LedgerController::class, 'getLedgersByType'])->name('ledgers');
    Route::resource('ledger', LedgerController::class);
    Route::resource('bank', BankDetailsController::class);
    Route::resource('profit-loose', ProfitAndLooseController::class);
    Route::resource('stock-in-hand', StockInHandController::class);
});


Route::get('/test-redirect', function () {
    return redirect()->route('dashboard');
});
