<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TrainingProgramController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public router
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// TrainingProgram
Route::get('/training-programs', [TrainingProgramController::class, 'show']);



// ================================================================



// Private router
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/auth', [UserController::class, 'getUser']);

    // User

    // TrainingProgram
    Route::group(['prefix' => '/training-programs'], function () { 
        Route::post('/', [TrainingProgramController::class, 'store']);
    });
});