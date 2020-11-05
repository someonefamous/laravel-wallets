<?php

namespace SomeoneFamous\Wallets\Http\Controllers;

use Illuminate\Http\Response;
use SomeoneFamous\Wallets\Models\Wallet;

class WalletController extends Controller
{
    public function index()
    {
        abort_unless(
            auth()->check(),
            Response::HTTP_FORBIDDEN,
            'Only authenticated users can view their wallets.'
        );

        $wallets = auth()->user()->wallets;

        return view('sf_wallets::wallets.index', ['wallets' => $wallets]);

    }

    public function show()
    {
        abort_unless(
            auth()->check(),
            Response::HTTP_FORBIDDEN,
            'Only authenticated users can view their wallets.'
        );

        $wallet = Wallet::findOrFail(request('wallet'));

        return view('sf_wallets::wallets.show', ['wallet' => $wallet]);
    }
}
