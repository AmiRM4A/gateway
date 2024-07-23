<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\GatewayController;
use App\Http\Controllers\Api\v1\TransactionController;

Route::apiResource('transaction', TransactionController::class);
Route::post('transaction/verify/{transaction}', [TransactionController::class, 'verify'])->name('transaction.verify');

Route::apiResource('gateway', GatewayController::class);
