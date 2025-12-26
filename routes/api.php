<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\TransferController;

Route::get('/health', fn() => response()->json(['status' => 'ok']));

Route::prefix('wallets')->group(function () {
    Route::post('/', [WalletController::class, 'store']);
    Route::get('/', [WalletController::class, 'index']);
    Route::get('/{id}', [WalletController::class, 'show']);
    Route::get('/{id}/balance', [WalletController::class, 'balance']);
    Route::get('/{id}/transactions', [WalletController::class, 'transactions']);

    Route::post('/{id}/deposit', [WalletController::class, 'deposit']);
    Route::post('/{id}/withdraw', [WalletController::class, 'withdraw']);
});

Route::post('/transfers', [TransferController::class, 'store']);
