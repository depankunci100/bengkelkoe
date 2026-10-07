<?php

use App\Http\Controllers\Api\PartApiController;
use App\Http\Controllers\Api\WorkOrderApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/work-orders', [WorkOrderApiController::class, 'index']);
    Route::get('/work-orders/{id}', [WorkOrderApiController::class, 'show']);
    Route::post('/work-orders/{id}/status', [WorkOrderApiController::class, 'updateStatus']);

    Route::get('/parts', [PartApiController::class, 'index']);
});
