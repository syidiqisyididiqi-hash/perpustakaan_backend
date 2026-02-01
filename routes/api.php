<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FineController;
use App\Http\Controllers\LoanController;
use Illuminate\Support\Facades\Route;

Route::apiResource('category', CategoryController::class);

Route::apiResource('book', BookController::class);

Route::apiResource('fine', FineController::class);

Route::apiResource('loan', LoanController::class);
