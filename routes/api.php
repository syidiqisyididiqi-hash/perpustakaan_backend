<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ApiAuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\FineController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\LoanDetailController;
use App\Http\Controllers\UserController;

Route::prefix('auth')->group(function () {
    Route::post('register', [ApiAuthController::class, 'register']);
    Route::post('login', [ApiAuthController::class, 'login']);
    Route::post('logout', [ApiAuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::get('health', function () {
    return response()->json([
        'status' => true,
        'message' => 'API up'
    ], 200);
});

Route::middleware('auth:sanctum')->group(function () {

    Route::apiResource('users', UserController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('books', BookController::class);
    Route::apiResource('fines', FineController::class);
    Route::apiResource('loans', LoanController::class);
    Route::apiResource('loan-details', LoanDetailController::class);
});
