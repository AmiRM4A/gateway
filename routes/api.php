<?php

use App\Http\Controllers\GatewayController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::post('payment', [TransactionController::class, 'create'])->name('transaction.create');
Route::post('verify/{unique_id}', [TransactionController::class, 'verify'])->name('transaction.verify');
Route::apiResource('gateway', GatewayController::class);
