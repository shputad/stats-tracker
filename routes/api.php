<?php

use App\Http\Controllers\Api\StatsController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/stats/network-profiles', [StatsController::class, 'getNetworkProfileStats']);
    Route::post('/stats/topup', [StatsController::class, 'addTopup']);
    Route::get('/stats/overall', [StatsController::class, 'getOverallStats']);
});