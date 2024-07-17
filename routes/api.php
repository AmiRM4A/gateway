<?php

use App\Http\Controllers\GatewayController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function () {
    Route::apiResource('transaction', TransactionController::class);
    Route::post('transaction/verify/{transaction}', [TransactionController::class, 'verify'])->name('transaction.verify');

    Route::apiResource('gateway', GatewayController::class);
});
