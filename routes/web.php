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
use App\Http\Controllers\ManufacturingController;
use App\Http\Controllers\FormulaController;
use App\Http\Controllers\DirectReceiptController;
use App\Http\Controllers\ManyToOneController;
use App\Http\Controllers\StockissueController;
use App\Http\Controllers\StockreceiptController;
use App\Http\Controllers\OneToManyController;
use App\Http\Controllers\PackagingController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockController;
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
    
    // Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    // Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    // Route::post('/products/store/{branch?}', [ProductController::class, 'store'])->name('products.store');
    // Route::get('/products/{id}/show/{branch?}', [ProductController::class, 'show'])->name('products.show');
    // Route::get('/products/{id}/edit/{branch?}', [ProductController::class, 'edit'])->name('products.edit');
    // Route::put('/products/{id}/update/{branch?}', [ProductController::class, 'update'])->name('products.update');
    // Route::delete('/products/{id}/delete/{branch?}', [ProductController::class, 'destroy'])->name('products.destroy');

    Route::post('/product/import', [ProductController::class, 'importProducts'])->name('products.import');
    Route::get('/products/export', [ProductController::class, 'export'])->name('products.export');
    Route::resource('products', ProductController::class);
    Route::post('/products/modalstore', [ProductController::class, 'saveProduct'])->name('products.modalstore');

    // Categories
    // Route::resource('categories', CategoryController::class);
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories/store/{branch?}', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{id}/show/{branch?}', [CategoryController::class, 'show'])->name('categories.show');
    Route::get('/categories/{id}/edit/{branch?}', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{id}/update/{branch?}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{id}/delete/{branch?}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::post('/categories/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');

    
    Route::resource('inventory', InventoryController::class);
    Route::post('/inventory/store', [InventoryController::class, 'inventorystore'])->name('inventory.newstore');

    Route::post('/inventory/quick-update', [InventoryController::class, 'quickUpdate'])->name('inventory.quickUpdate');

    
    Route::get('/manufacturing', [ManufacturingController::class, 'index'])->name('manufacturing.index');
    Route::get('/manufacturing/create', [ManufacturingController::class, 'create'])->name('manufacturing.create');
    Route::post('/manufacturing/store', [ManufacturingController::class, 'store'])->name('manufacturing.store');
    Route::get('/manufacturing/{id}/show/{branch?}', [ManufacturingController::class, 'show'])->name('manufacturing.show');
    Route::get('/manufacturing/{id}/edit/{branch?}', [ManufacturingController::class, 'edit'])->name('manufacturing.edit');
    Route::put('/manufacturing/{id}/update/{branch?}', [ManufacturingController::class, 'update'])->name('manufacturing.update');
    Route::get('/manufacturing/destroy/{id}', [ManufacturingController::class, 'destroy'])->name('manufacturing.destroy');

    Route::get('/formula', [FormulaController::class, 'index'])->name('formula.index');
    Route::get('/formula/create', [FormulaController::class, 'create'])->name('formula.create');
    Route::post('/formula/store', [FormulaController::class, 'store'])->name('formula.store');
    Route::get('/formula/{id}/show/{branch?}', [FormulaController::class, 'show'])->name('formula.show');
    Route::get('/formula/{id}/edit/{branch?}', [FormulaController::class, 'edit'])->name('formula.edit');
    Route::put('/formula/{id}/update/{branch?}', [FormulaController::class, 'update'])->name('formula.update');
    Route::delete('/formula/{id}/delete/{branch?}', [FormulaController::class, 'destroy'])->name('formula.destroy');

    // Packaging
    Route::get('/packaging', [PackagingController::class, 'index'])->name('packaging.index');
    Route::get('/packaging/create', [PackagingController::class, 'create'])->name('packaging.create');
    Route::post('/packaging/store', [PackagingController::class, 'store'])->name('packaging.store');
    Route::get('/packaging/{id}/show/{branch?}', [PackagingController::class, 'show'])->name('packaging.show');
    Route::get('/packaging/{id}/edit/{branch?}', [PackagingController::class, 'edit'])->name('packaging.edit');
    Route::put('/packaging/{id}/update/{branch?}', [PackagingController::class, 'update'])->name('packaging.update');
    Route::delete('/packaging/{id}/delete/{branch?}', [PackagingController::class, 'destroy'])->name('packaging.destroy');

    
    Route::get('/directreceipt', [DirectReceiptController::class, 'index'])->name('directreceipt.index');
    Route::get('/directreceipt/create', [DirectReceiptController::class, 'create'])->name('directreceipt.create');
    Route::post('/directreceipt/store', [DirectReceiptController::class, 'store'])->name('directreceipt.store');
    Route::get('/directreceipt/{id}/show/{branch?}', [DirectReceiptController::class, 'show'])->name('directreceipt.show');
    Route::get('/directreceipt/{id}/edit/{branch?}', [DirectReceiptController::class, 'edit'])->name('directreceipt.edit');
    Route::put('/directreceipt/{id}/update/{branch?}', [DirectReceiptController::class, 'update'])->name('directreceipt.update');
    Route::delete('/directreceipt/{id}/delete/{branch?}', [DirectReceiptController::class, 'destroy'])->name('directreceipt.destroy');

    // Stock Issue
    Route::get('/stockissue', [StockissueController::class, 'index'])->name('stockissue.index');
    Route::get('/stockissue/create', [StockissueController::class, 'create'])->name('stockissue.create');
    Route::post('/stockissue/store', [StockissueController::class, 'store'])->name('stockissue.store');
    Route::get('/stockissue/{id}/show/{branch?}', [StockissueController::class, 'show'])->name('stockissue.show');
    Route::get('/stockissue/{id}/edit/{branch?}', [StockissueController::class, 'edit'])->name('stockissue.edit');
    Route::put('/stockissue/{id}/update/{branch?}', [StockissueController::class, 'update'])->name('stockissue.update');
    Route::delete('/stockissue/{id}/delete/{branch?}', [StockissueController::class, 'destroy'])->name('stockissue.destroy');
    Route::get('/stockissue/pending', [StockissueController::class, 'pending'])->name('stockissue.pending');
    Route::get('/stockissue/pending/ledger/{ledgerId}', [StockissueController::class, 'pendingLedgerDetails'])->name('stockissue.pendingLedgerDetails');
    Route::get('/stockissue/search/{branch}', [StockissueController::class, 'search'])->name('stockissue.search');

    Route::get('/stockreceipt', [StockreceiptController::class, 'index'])->name('stockreceipt.index');
    Route::get('/stockreceipt/create', [StockreceiptController::class, 'create'])->name('stockreceipt.create');
    Route::post('/stockreceipt/store', [StockreceiptController::class, 'store'])->name('stockreceipt.store');
    Route::get('/stockreceipt/{id}/show/{branch?}', [StockreceiptController::class, 'show'])->name('stockreceipt.show');
    Route::get('/stockreceipt/{id}/edit/{branch?}', [StockreceiptController::class, 'edit'])->name('stockreceipt.edit');
    Route::put('/stockreceipt/{id}/update/{branch?}', [StockreceiptController::class, 'update'])->name('stockreceipt.update');
    Route::delete('/stockreceipt/{id}/delete/{branch?}', [StockreceiptController::class, 'destroy'])->name('stockreceipt.destroy');

    // Purchase
    Route::get('/purchase', [PurchaseController::class, 'index'])->name('purchase.index');
    Route::get('/purchase/create', [PurchaseController::class, 'create'])->name('purchase.create');
    Route::post('/purchase/create', [PurchaseController::class, 'store'])->name('purchase.store');
    Route::get('/purchase/{id}/edit', [PurchaseController::class, 'edit'])->name('purchase.edit');
    Route::put('/purchase/{id}/update', [PurchaseController::class, 'update'])->name('purchase.update');
    Route::delete('/purchase/{id}/delete', [PurchaseController::class, 'destroy'])->name('purchase.destroy');
    Route::get('/purchase/history', [PurchaseController::class, 'getPurchaseHistory'])->name('purchase.history');

    // Purchase party
    Route::get('/purchase/party', [PurchasePartyController::class, 'index'])->name('purchase.party.index');
    Route::get('/purchase/party/create', [PurchasePartyController::class, 'create'])->name('purchase.party.create');
    Route::post('/purchase/party/store', [PurchasePartyController::class, 'store'])->name('purchase.party.store');
    Route::get('/purchase/party/{id}/edit', [PurchasePartyController::class, 'edit'])->name('purchase.party.edit');
    Route::put('/purchase/party/{id}/update', [PurchasePartyController::class, 'update'])->name('purchase.party.update');
    Route::delete('/purchase/party/{id}/delete', [PurchasePartyController::class, 'destroy'])->name('purchase.party.destroy');
    Route::get('/purchase/party/{id}/show', [PurchasePartyController::class, 'show'])->name('purchase.party.show');

    Route::resource('app/orders', AppOrderController::class);

    // Search routes for search dropdown
    Route::get('/companies/search', [ProductController::class, 'searchCompany'])->name('companies.search');
    Route::get('/packagings/search', [ProductController::class, 'searchPackaging'])->name('packagings.search');
    Route::get('/categories/search', [ProductController::class, 'searchCategory'])->name('categories.search');
    Route::get('/hsn-code/search', [ProductController::class, 'searchHsnCode'])->name('hsn.search');
    Route::get('/products-search', [ProductController::class, 'searchProduct'])->name('products.search');
    Route::get('/formula-search', [FormulaController::class, 'searchFormula'])->name('formula.search');
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
    // Route::get('/ledgers', [LedgerController::class, 'getLedgersByType'])->name('ledgers');
    Route::resource('ledger', LedgerController::class);
    Route::resource('bank', BankDetailsController::class);
    Route::resource('profit-loose', ProfitAndLooseController::class);
    Route::resource('stock-in-hand', StockInHandController::class);

    // One to Many routes
    Route::resource('one-to-many', OneToManyController::class);
    Route::resource('many-to-one', ManyToOneController::class);
    Route::resource('stock', StockController::class);
    Route::get('/stock/{id}/pdf', [StockController::class, 'exportRecordPdf'])->name('stock.record_pdf');
});

Route::get('/data-correction', [InventoryController::class, 'dataCorrection']);

Route::get('/test-redirect', function () {
    return redirect()->route('dashboard');
});
