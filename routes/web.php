<?php

use Illuminate\Support\Facades\Route;
use SomeoneFamous\Wallets\Http\Controllers\WalletController;

Route::get('/wallets', [WalletController::class, 'index'])->name('wallets.index');
Route::get('/wallets/{wallet}', [WalletController::class, 'show'])->name('wallets.show');
