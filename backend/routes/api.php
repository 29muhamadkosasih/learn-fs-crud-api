<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\DashboardAnalyticsController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\RolePermissionController;
use App\Http\Controllers\Api\UserController;

Route::middleware(['auth:sanctum'])->group(function () {
    // user
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // books
    Route::middleware('permission:books.view')->group(function () {
        Route::get('/books', [BookController::class, 'index']);
        Route::get('/books/{id}', [BookController::class, 'show']);
    });
    Route::middleware('permission:books.create')->post('/books', [BookController::class, 'store']);
    Route::middleware('permission:books.edit')->put('/books/{id}', [BookController::class, 'update']);
    Route::middleware('permission:books.delete')->delete('/books/{id}', [BookController::class, 'destroy']);

    // products
    Route::middleware('permission:products.view')->group(function () {
        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/products/{id}', [ProductController::class, 'show']);
    });
    Route::middleware('permission:products.create')->post('/products', [ProductController::class, 'store']);
    Route::middleware('permission:products.edit')->put('/products/{id}', [ProductController::class, 'update']);
    Route::middleware('permission:products.delete')->delete('/products/{id}', [ProductController::class, 'destroy']);

    // courses
    Route::middleware('permission:courses.view')->group(function () {
        Route::get('/courses', [CourseController::class, 'index']);
        Route::get('/courses/{id}', [CourseController::class, 'show']);
    });
    Route::middleware('permission:courses.create')->post('/courses', [CourseController::class, 'store']);
    Route::middleware('permission:courses.edit')->put('/courses/{id}', [CourseController::class, 'update']);
    Route::middleware('permission:courses.delete')->delete('/courses/{id}', [CourseController::class, 'destroy']);

    // users management
    Route::middleware('permission:users.view')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{id}', [UserController::class, 'show']);
    });
    Route::middleware('permission:users.create')->post('/users', [UserController::class, 'store']);
    Route::middleware('permission:users.edit')->put('/users/{id}', [UserController::class, 'update']);
    Route::middleware('permission:users.delete')->delete('/users/{id}', [UserController::class, 'destroy']);

    // dashboard analytics
    Route::get('/dashboard/analytics', [DashboardAnalyticsController::class, 'index']);
});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // permissions management
    Route::get('/permissions', [PermissionController::class, 'index']);
    Route::post('/permissions', [PermissionController::class, 'store']);
    Route::get('/permissions/{id}', [PermissionController::class, 'show']);
    Route::put('/permissions/{id}', [PermissionController::class, 'update']);
    Route::delete('/permissions/{id}', [PermissionController::class, 'destroy']);

    // role permissions management
    Route::get('/role-permissions', [RolePermissionController::class, 'index']);
    Route::post('/role-permissions', [RolePermissionController::class, 'store']);
    Route::put('/role-permissions/{id}', [RolePermissionController::class, 'update']);
    Route::delete('/role-permissions/{id}', [RolePermissionController::class, 'destroy']);
});

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
