<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PharmacovigilanceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (Pharmacovigilance Module)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    // Pharmacovigilance Module
    Route::get('/medications/search', [PharmacovigilanceController::class, 'searchOrdersByLot']);
    Route::get('/medications/export', [PharmacovigilanceController::class, 'exportOrdersByLot']);
    Route::post('/alerts/send', [PharmacovigilanceController::class, 'sendAlerts']);
    Route::get('/alerts', [PharmacovigilanceController::class, 'getAlerts']);
});
