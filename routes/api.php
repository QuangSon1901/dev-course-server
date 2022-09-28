<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TrainingProgramController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public router

// Auth
Route::group(['prefix' => '/auth'], function() {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// TrainingProgram
Route::group(['prefix' => '/training-programs'], function () { 
    Route::get('/', [TrainingProgramController::class, 'show']);
});


// ================================================================



// Private router
Route::group(['middleware' => ['auth:sanctum']], function () {

    // Auth
    Route::group(['prefix' => '/auth'], function() {
        Route::get('/', [UserController::class, 'getUser']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
    
    // User

    // TrainingProgram
    Route::group(['prefix' => '/training-programs'], function () { 
        Route::post('/', [TrainingProgramController::class, 'store']);
    });
});