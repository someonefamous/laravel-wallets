<?php

namespace SomeoneFamous\Wallets\Database\Factories;

use SomeoneFamous\Wallets\Models\Currency;
use SomeoneFamous\Wallets\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition()
    {
        return [
            'wallet_id' => 1,
            'counter_wallet_id' => 1,
            'amount' => $this->faker->randomFloat(Currency::MAX_DECIMALS, -10000000, 10000000),
            'description' => $this->faker->name,
            'status' => array_rand([Transaction::STATUS_CLEARED, Transaction::STATUS_PENDING])
        ];
    }
}
