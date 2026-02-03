<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DocumentController;
use App\Models\Department;
use App\Models\DocumentCategory;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public Routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index']);

    // Master Data
    Route::get('/departments', function () {
        return Department::all();
    });

    Route::get('/roles', function () {
        return \Spatie\Permission\Models\Role::all();
    });

    Route::apiResource('categories', \App\Http\Controllers\CategoryController::class);

    // Admin User Management
    Route::apiResource('users', \App\Http\Controllers\UserController::class);

    // Activity Logs
    Route::get('/activity-logs', [\App\Http\Controllers\ActivityLogController::class, 'index']);

    // Document Operations
    Route::get('/documents', [DocumentController::class, 'index']);
    Route::post('/documents', [DocumentController::class, 'store']);
    Route::get('/documents/{document}', [DocumentController::class, 'show']);
    Route::match(['put', 'patch'], '/documents/{document}', [DocumentController::class, 'update']);
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy']);
    Route::get('/documents/{document}/download', [DocumentController::class, 'download']);
});
