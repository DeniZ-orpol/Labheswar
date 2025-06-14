<?php

use App\Http\Controllers\API\BranchAuthController;
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