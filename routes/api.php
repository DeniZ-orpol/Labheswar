<?php

use App\Http\Controllers\API\BranchAuthController;
use App\Http\Controllers\API\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/login', [BranchAuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [BranchAuthController::class, 'profile']);
    Route::post('/logout', [BranchAuthController::class, 'logout']);

    // Product APIs
    Route::get('/all-products', [ProductController::class, 'showAllProducts']);
    Route::post('/products/store', [ProductController::class, 'store']);
    Route::get('/categories', [ProductController::class, 'getCategories']);
    Route::get('/companies', [ProductController::class, 'getCompanies']);
    Route::get('/hsn-code', [ProductController::class, 'getHsnCode']);
    Route::post('/search-product', [ProductController::class, 'searchProduct']);

});

// Route::post('/products/store', [ProductController::class, 'store']);
// Route::get('/products', [ProductController::class, 'show']);
// Route::get('/all-products', [ProductController::class, 'showAllProducts']);
// Route::get('/all-branch-categories', [ProductController::class, 'showCategoriesFromAllBranches']);
// Route::get('/product-by-barcode/{barcode}', [ProductController::class, 'findProductByBarcode']);



