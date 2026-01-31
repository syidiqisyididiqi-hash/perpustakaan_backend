<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

Route::apiResource('category', CategoryController::class);

Route::apiResource('book', BookController::class);
