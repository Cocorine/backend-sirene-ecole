<?php

use App\Http\Controllers\API\PermissionController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EcoleController;
use App\Http\Controllers\Api\SireneController;
use App\Http\Controllers\API\TechnicienController;
use App\Http\Controllers\Api\CalendrierScolaireController; // Add this line
use App\Http\Controllers\Api\UserController;
use App\Models\Ville;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get("/villes", function(Request $request){
    return Ville::all();
});

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

    // Protected routes for Ecole management
    Route::middleware('auth:api')->group(function () {
        Route::get('/', [EcoleController::class, 'index']); // List all schools
        Route::get('me', [EcoleController::class, 'show']); // Get authenticated school details
        Route::put('me', [EcoleController::class, 'update']); // Update authenticated school details
        Route::delete('{id}', [EcoleController::class, 'destroy']); // Delete a school by ID
    });
});

// Sirene routes (Protected - Admin/Technicien)
Route::prefix('sirenes')->middleware('auth:api')->group(function () {
    Route::get('/', [SireneController::class, 'index']);
    Route::get('disponibles', [SireneController::class, 'disponibles']);
    Route::get('numero-serie/{numeroSerie}', [SireneController::class, 'showByNumeroSerie']);
    Route::get('{id}', [SireneController::class, 'show']);
    Route::post('/', [SireneController::class, 'store']); // Admin only
    Route::put('{id}', [SireneController::class, 'update']); // Admin/Technicien
    Route::post('{id}/affecter', [SireneController::class, 'affecter']); // Admin/Technicien
    Route::delete('{id}', [SireneController::class, 'destroy']); // Admin only
});

// Technicien routes (Protected)
Route::prefix('techniciens')->middleware('auth:api')->group(function () {
    Route::get('/', [TechnicienController::class, 'index']);
    Route::post('/', [TechnicienController::class, 'store']);
    Route::get('{id}', [TechnicienController::class, 'show']);
    Route::put('{id}', [TechnicienController::class, 'update']);
    Route::delete('{id}', [TechnicienController::class, 'destroy']);
});

// CalendrierScolaire routes (Protected)
Route::prefix('calendrier-scolaire')->middleware('auth:api')->group(function () {
    Route::get('/', [CalendrierScolaireController::class, 'index']);
    Route::post('/', [CalendrierScolaireController::class, 'store']);
    Route::get('{id}', [CalendrierScolaireController::class, 'show']);
    Route::put('{id}', [CalendrierScolaireController::class, 'update']);
    Route::delete('{id}', [CalendrierScolaireController::class, 'destroy']);
});
