<?php

use App\Http\Controllers\API\HealthCheckController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {

});

// Route::get('/health-check', [HealthCheckController::class, 'index']);
