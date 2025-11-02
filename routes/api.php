<?php

use App\Http\Controllers\API\PermissionController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EcoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('permissions')->group(function () {
    Route::get('/', [PermissionController::class, 'index']);
    Route::get('{id}', [PermissionController::class, 'show']);
    Route::get('slug/{slug}', [PermissionController::class, 'showBySlug']);
    Route::get('role/{roleId}', [PermissionController::class, 'showByRole']);
});

Route::prefix('roles')->group(function () {
    Route::get('/', [RoleController::class, 'index']);
    Route::get('{id}', [RoleController::class, 'show']);
    Route::post('/', [RoleController::class, 'store']);
    Route::put('{id}', [RoleController::class, 'update']);
    Route::delete('{id}', [RoleController::class, 'destroy']);
});

Route::prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::get('{id}', [UserController::class, 'show']);
    Route::post('/', [UserController::class, 'store']);
    Route::put('{id}', [UserController::class, 'update']);
    Route::delete('{id}', [UserController::class, 'destroy']);
});

// Authentication routes (public)
Route::prefix('auth')->group(function () {
    Route::post('request-otp', [AuthController::class, 'requestOtp']);
    Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('login', [AuthController::class, 'login']);
});

// Protected authentication routes
Route::prefix('auth')->middleware('auth:api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
});

// Ecole routes
Route::prefix('ecoles')->group(function () {
    // Public: Inscription
    Route::post('inscription', [EcoleController::class, 'inscrire']);

    // Protected: Ecole account management
    Route::middleware('auth:api')->group(function () {
        Route::get('profil', [EcoleController::class, 'show']);
        Route::put('profil', [EcoleController::class, 'update']);
    });
});
