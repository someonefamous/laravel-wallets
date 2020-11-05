<?php

namespace SomeoneFamous\Wallets\Traits;

use SomeoneFamous\Wallets\Models\Wallet;

trait HasWallets
{
    public function wallets()
    {
        return $this->morphMany(Wallet::class, 'owner');
    }
}
