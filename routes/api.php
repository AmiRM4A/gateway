<?php

use App\Http\Controllers\GatewayController;
use Illuminate\Support\Facades\Route;

Route::post('payment', [GatewayController::class, 'transaction'])->name('transaction.create');
Route::post('verify/{unique_id}', [GatewayController::class, 'verify'])->name('transaction.verify');
