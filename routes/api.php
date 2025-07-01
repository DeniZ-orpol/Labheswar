<?php

use App\Http\Controllers\API\AppCartOrderController;
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
    Route::get('/product-by-barcode/{barcode}', [ProductController::class, 'findProductByBarcode']);
    Route::get('/user/popular-product', [ProductController::class, 'getUserPopularProducts']); // Get list of popular products of auth user

    // Cart API
    Route::post('/add-to-cart', [AppCartOrderController::class, 'addProductToCart']);
    Route::post('/get-cart-items', [AppCartOrderController::class, 'getCartItems']);
    Route::post('/create-order-receipt', action: [AppCartOrderController::class, 'createCartOrderReceipt']);
    Route::get('/get-cart-list', [AppCartOrderController::class, 'getCartList']);
    Route::post('/cart/add', [AppCartOrderController::class, 'addToCart']);
    Route::put('/cart/update-quantity', [AppCartOrderController::class, 'updateQuantity']);
    Route::post('/cart/remove-product', [AppCartOrderController::class, 'removeProductFromCart']);

    // Order Receipts API
    Route::get('/order-bills-list', [AppCartOrderController::class, 'getOrderBills']);
    Route::get('/order-bill/{id}', [AppCartOrderController::class, 'orderBill']);

    Route::post('/cart/assign', [AppCartOrderController::class, 'assignCartToUser']);
    Route::get('/cart/open', [AppCartOrderController::class, 'getAssignedCartId']);
});

// Route::post('/products/store', [ProductController::class, 'store']);
// Route::get('/products', [ProductController::class, 'show']);
// Route::get('/all-products', [ProductController::class, 'showAllProducts']);
// Route::get('/all-branch-categories', [ProductController::class, 'showCategoriesFromAllBranches']);
// Route::get('/product-by-barcode/{barcode}', [ProductController::class, 'findProductByBarcode']);
