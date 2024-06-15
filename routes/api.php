<?php

use App\Http\Controllers\GatewayController;
use Illuminate\Support\Facades\Route;

Route::post('payment', [GatewayController::class, 'transaction'])->name('transaction.create');
Route::post('verify', [GatewayController::class, 'verify'])->name('transaction.verify');
Route::post('inquiry', [GatewayController::class, 'inquiry'])->name('transaction.inquiry');
