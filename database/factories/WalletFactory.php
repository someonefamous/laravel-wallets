<?php

namespace SomeoneFamous\Wallets\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use SomeoneFamous\Wallets\Models\Wallet;
use SomeoneFamous\Wallets\Tests\User;

class WalletFactory extends Factory
{
    protected $model = Wallet::class;

    public function definition()
    {
        $owner = User::factory()->create();

        return [
            'owner_id' => $owner->id,
            'owner_type' => get_class($owner),
            'currency_id' => 1,
            'overdraft' => 0,
            'name' => $this->faker->name,
        ];
    }
}
