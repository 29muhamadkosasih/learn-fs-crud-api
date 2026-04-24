<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    //user
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    //books
    Route::apiResource('/books', BookController::class);
    //products
    Route::apiResource('/products', ProductController::class);
    //courses
    Route::apiResource('/courses', CourseController::class);
    //users management
    Route::apiResource('/users', UserController::class);
});
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
