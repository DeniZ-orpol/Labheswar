<?php

use App\Http\Controllers\API\BranchAuthController;
use App\Http\Controllers\API\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/login', [BranchAuthController::class, 'login']);

Route::middleware('branch.auth')->group(function () {
    Route::get('/profile', [BranchAuthController::class, 'profile']);
    Route::post('/logout', [BranchAuthController::class, 'logout']);
});

Route::post('/products/store', [ProductController::class, 'store']);
// Route::get('/products', [ProductController::class, 'show']);
Route::get('/all-products', [ProductController::class, 'showAllProducts']);
Route::get('/all-branch-categories', [ProductController::class, 'showCategoriesFromAllBranches']);



